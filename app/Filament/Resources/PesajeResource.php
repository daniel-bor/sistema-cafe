<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Pesaje;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\EstadoPesaje;
use App\Services\CuentaService;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\PesajeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PesajeResource\RelationManagers;
use App\Filament\Resources\PesajeResource\RelationManagers\ParcialidadesRelationManager;

class PesajeResource extends Resource
{
    protected static ?string $model = Pesaje::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cantidad_total')
                    ->minValue(1)
                    ->required()
                    ->numeric()
                    ->maxValue(100000),
                Forms\Components\Select::make('medida_peso_id')
                    ->relationship('medidaPeso', 'nombre')
                    ->native(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Mostrando el ID
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),
                // Numero de cuenta
                Tables\Columns\TextColumn::make('cuenta.no_cuenta')
                    ->sortable()
                    ->label('No. Cuenta'),
                // Agricultor
                Tables\Columns\TextColumn::make('agricultor.nombreCompleto')
                    ->label('Agricultor'),
                Tables\Columns\TextColumn::make('cantidad_total')
                    ->numeric('2', '.', ',')
                    ->sortable(),
                Tables\Columns\TextColumn::make('medidaPeso.nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tolerancia')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    // Solo usuarios con rol_id 2 pueden ver esta columna
                    ->visible(fn($record) => auth()->user()->rol_id === 2),
                // Tables\Columns\TextColumn::make('precio_unitario')
                //     ->money('GTQ')
                //     ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_parcialidades')
                    ->label('Parcialidades')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_entregas')
                    ->label('Entregas')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('fecha_inicio')
                //     ->dateTime()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('fecha_cierre')
                //     ->dateTime()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn($state) => $state->getColor())
                    ->sortable(),
                // Observaciones
                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Observaciones'),
                Tables\Columns\TextColumn::make('cuenta.no_cuenta')
                    ->sortable()
                    ->visible(fn($record) => $record),
                Tables\Columns\TextColumn::make('agricultor.nombre')
                    ->sortable()
                    ->visible(fn($record) => auth()->user()->rol_id === 2),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                // Filtros para mostrar los pesajes por estado
                // Por defecto se muestran pesajes con estado PENDIENTE
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        EstadoPesaje::ACEPTADO->value => EstadoPesaje::ACEPTADO->getLabel(),
                        EstadoPesaje::PENDIENTE->value => EstadoPesaje::PENDIENTE->getLabel(),
                        EstadoPesaje::RECHAZADO->value => EstadoPesaje::RECHAZADO->getLabel(),
                        EstadoPesaje::PESAJE_INICIADO->value => EstadoPesaje::PESAJE_INICIADO->getLabel(),
                        EstadoPesaje::PESAJE_FINALIZADO->value => EstadoPesaje::PESAJE_FINALIZADO->getLabel(),
                    ])
                    ->default(EstadoPesaje::PENDIENTE->value),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Actions para aceptar o rechazar la solicitud
                    Tables\Actions\Action::make('Aceptar')
                        ->label('Aceptar Solicitud')
                        ->color('success')
                        ->action(function (Pesaje $record) {
                            DB::transaction(function () use ($record) {
                                try {
                                    // Usar el servicio de cuentas para gestionar la cuenta del agricultor
                                    $cuentaService = app(CuentaService::class);

                                    // Asignar una cuenta al pesaje (crea una nueva si no existe)
                                    $cuentaService->asignarCuentaAPesaje($record);

                                    // Actualizar el estado del pesaje a ACEPTADO
                                    $record->estado = EstadoPesaje::ACEPTADO;
                                    $record->save();

                                    Notification::make()
                                        ->title('Solicitud aceptada')
                                        ->body('La solicitud de pesaje se actualizó correctamente y se ha gestionado la cuenta.')
                                        ->success()
                                        ->send();
                                } catch (\Exception $e) {
                                    // Capturar errores y mostrar una notificación
                                    Notification::make()
                                        ->title('Error al procesar la solicitud')
                                        ->body('No se pudo completar la operación: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            });
                        })
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->visible(fn($record) => $record->cantidad_total == $record->total_parcialidades && $record->estado == EstadoPesaje::PENDIENTE),
                    Tables\Actions\Action::make('Rechazar')
                        ->label('Rechazar Solicitud')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('observaciones')
                                ->label('Observaciones')
                                ->placeholder('Escriba las observaciones para el agricultor')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->action(function (Pesaje $record, array $data) {
                            $record->estado = EstadoPesaje::RECHAZADO;
                            $record->observaciones = $data['observaciones'] ?? null;
                            $record->save();
                            Notification::make()
                                ->title('Solicitud rechazada')
                                ->body('La solicitud de pesaje se actualizó correctamente.')
                                ->success()
                                ->send();
                        })
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->visible(fn($record) => $record->cantidad_total == $record->total_parcialidades && $record->estado == EstadoPesaje::PENDIENTE),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ParcialidadesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPesajes::route('/'),
            'view' => Pages\ViewPesaje::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('estado', '!=', EstadoPesaje::NUEVO);
    }
}
