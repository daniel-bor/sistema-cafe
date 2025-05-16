<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Pesaje;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\EstadoPesaje;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PesajeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PesajeResource\RelationManagers;

class PesajeResource extends Resource
{
    protected static ?string $model = Pesaje::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $modelLabel = 'Pesaje';
    protected static ?string $pluralModelLabel = 'Pesajes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cantidad_total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tolerancia')
                    ->required()
                    ->numeric()
                    ->default(5.00),
                Forms\Components\TextInput::make('precio_unitario')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('fecha_inicio'),
                Forms\Components\DateTimePicker::make('fecha_cierre'),
                Forms\Components\TextInput::make('estado')
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('cuenta_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('agricultor_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('medida_peso_id')
                    ->required()
                    ->numeric(),
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
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make(),
                    // Action para enviar la solicitud de pesaje
                    Tables\Actions\Action::make('Enviar solicitud')
                        ->color('success')
                        ->action(function (Pesaje $record) {
                            $record->estado = EstadoPesaje::PENDIENTE;
                            $record->save();
                            Notification::make()
                                ->title('Solicitud enviada')
                                ->body('La solicitud de pesaje ha sido enviada.')
                                ->success()
                                ->send();
                        })
                        ->icon('heroicon-o-paper-airplane')
                        ->requiresConfirmation()
                        ->visible(fn($record) => $record->cantidad_total == $record->total_parcialidades && $record->estado == EstadoPesaje::NUEVO),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePesajes::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
