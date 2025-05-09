<?php

namespace App\Filament\Agricultor\Resources;

use App\Filament\Agricultor\Resources\PesajeResource\Pages;
use App\Filament\Agricultor\Resources\PesajeResource\RelationManagers;
use App\Models\Pesaje;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\TextInput::make('precio_unitario')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100000)
                    ->step(0.01)
                    ->prefix('Q.'),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cantidad_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('medidaPeso.nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tolerancia')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    // Solo usuarios con rol_id 2 pueden ver esta columna
                    ->visible(fn($record) => auth()->user()->rol_id === 2),
                Tables\Columns\TextColumn::make('precio_unitario')
                    ->money('GTQ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_parcialidades')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListPesajes::route('/'),
            // 'create' => Pages\CreatePesaje::route('/create'),
            // 'view' => Pages\ViewPesaje::route('/{record}'),
            // 'edit' => Pages\EditPesaje::route('/{record}/edit'),
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
