<?php

namespace App\Filament\Agricultor\Resources;

use App\Filament\Agricultor\Resources\TransportistaResource\Pages;
use App\Filament\Agricultor\Resources\TransportistaResource\RelationManagers;
use App\Models\Transportista;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransportistaResource extends Resource
{
    protected static ?string $model = Transportista::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cui')
                    ->required(),
                Forms\Components\TextInput::make('nombre_completo')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->required(),
                Forms\Components\TextInput::make('tipo_licencia')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_vencimiento_licencia')
                    ->required(),
                Forms\Components\TextInput::make('agricultor_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('estado_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('disponible')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cui')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_licencia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_vencimiento_licencia')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agricultor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('disponible')
                    ->boolean(),
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTransportistas::route('/'),
            // 'create' => Pages\CreateTransportista::route('/create'),
            // 'edit' => Pages\EditTransportista::route('/{record}/edit'),
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
