<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PesajeResource\Pages;
use App\Filament\Resources\PesajeResource\RelationManagers;
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
                Tables\Columns\TextColumn::make('cantidad_total')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tolerancia')
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_unitario')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_cierre')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cuenta_id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('agricultor_id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('medida_peso_id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
