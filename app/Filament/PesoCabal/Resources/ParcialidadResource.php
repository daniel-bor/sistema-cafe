<?php

namespace App\Filament\PesoCabal\Resources;

use App\Enums\EstadoParcialidad;
use App\Filament\PesoCabal\Resources\ParcialidadResource\Pages;
use App\Filament\PesoCabal\Resources\ParcialidadResource\RelationManagers;
use App\Models\Parcialidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParcialidadResource extends Resource
{
    protected static ?string $model = Parcialidad::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Parcialidades';
    protected static ?string $pluralLabel = 'Parcialidades';
    protected static ?string $label = 'Parcialidad';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('peso')
                    ->minValue(1)
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('transporte_id')
                    ->relationship(
                        'transporte',
                        'placa',
                        fn(Builder $query) => $query->where('disponible', true)
                    )
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('transportista_id')
                    ->relationship('transportista', 'nombre_completo')
                    ->native(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pesaje.cuenta.no_cuenta')
                    ->label('Cuenta')
                    ->sortable(),
                Tables\Columns\TextColumn::make('peso')
                    ->label('Peso Indicado')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pesaje.medidaPeso.nombre')
                    ->label('Unidad de Medida')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_envio')
                    ->dateTime('d/m/Y H:i')
                    ->label('Fecha de Envio'),
                Tables\Columns\TextColumn::make('transporte.placa')
                    ->label('Transporte')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transportista.nombre_completo')
                    ->label('Transportista')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('transportista.foto')
                    ->label('Foto')
                    ->circular()
                    ->size(70),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->color(fn($state) => $state->getColor())
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultGroup(Group::make('pesaje.cuenta.no_cuenta')
                ->collapsible())
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Aceptar / rechazar
                    Tables\Actions\Action::make('aceptar')
                        ->label('Aceptar')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Parcialidad $record) {
                            $record->update([
                                'estado' => EstadoParcialidad::RECIBIDO,
                            ]);
                        }),
                    Tables\Actions\Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function (Parcialidad $record) {
                            $record->update([
                                'estado' => EstadoParcialidad::RECHAZADO,
                            ]);
                        }),
                    Tables\Actions\Action::make('pesar')
                        ->label('Pesar')
                        ->icon('heroicon-o-scale')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('peso_bascula')
                                ->default(fn($record) => $record?->peso_bascula)
                                ->label('Peso en Bascula')
                                ->minValue(1)
                                ->maxValue(1000000)
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (Parcialidad $record, array $data) {
                            $record->update([
                                'peso' => $data['peso_bascula'],
                                'estado' => EstadoParcialidad::PESADO,
                            ]);
                        }),
                    Tables\Actions\Action::make('finalizar')
                        ->label('Finalizar')
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->action(function (Parcialidad $record) {
                            $record->update([
                                'estado' => EstadoParcialidad::FINALIZADO,
                            ]);
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParcialidads::route('/'),
            // 'create' => Pages\CreateParcialidad::route('/create'),
            'view' => Pages\ViewParcialidad::route('/{record}'),
            // 'edit' => Pages\EditParcialidad::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('estado', EstadoParcialidad::ENVIADO)
            ->orWhere('estado', EstadoParcialidad::RECIBIDO)
            ->orWhere('estado', EstadoParcialidad::PESADO);
    }
}
