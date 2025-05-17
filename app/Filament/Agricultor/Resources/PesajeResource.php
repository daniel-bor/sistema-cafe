<?php

namespace App\Filament\Agricultor\Resources;

use App\Enums\EstadoPesaje;
use Filament\Forms;
use Filament\Tables;
use App\Models\Pesaje;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Agricultor\Resources\PesajeResource\Pages;
use App\Filament\Agricultor\Resources\PesajeResource\RelationManagers;
use App\Filament\Agricultor\Resources\PesajeResource\RelationManagers\ParcialidadesRelationManager;
use App\Models\Estado;

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
                    ->minValue(1)
                    ->required()
                    ->numeric()
                    ->maxValue(100000),
                Forms\Components\Select::make('medida_peso_id')
                    ->relationship('medidaPeso', 'nombre')
                    ->native(false)
                    ->required(),
                // Forms\Components\TextInput::make('tolerancia')
                //     ->prefixIcon('heroicon-o-percent-badge')
                //     ->required()
                //     ->numeric()
                //     ->minValue(1)
                //     ->maxValue(10),
            ]);
        // ->columns(3);
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
                    ->label('Cuenta'),
                Tables\Columns\TextColumn::make('cantidad_total')
                    ->numeric('2', '.', ',')
                    ->sortable(),
                Tables\Columns\TextColumn::make('medidaPeso.nombre')
                    ->sortable(),
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
            'create' => Pages\CreatePesaje::route('/create'),
            // 'view' => Pages\ViewPesaje::route('/{record}'),
            'edit' => Pages\EditPesaje::route('/{record}/edit'),
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
