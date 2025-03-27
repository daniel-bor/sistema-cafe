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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('medida_peso_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('peso_total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('estado_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('solicitud_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('cuenta_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('fecha_creacion')
                    ->required(),
                Forms\Components\DateTimePicker::make('fecha_inicio'),
                Forms\Components\DateTimePicker::make('fecha_cierre'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medida_peso_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('peso_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('solicitud_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cuenta_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_creacion')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_cierre')
                    ->dateTime()
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
            'create' => Pages\CreatePesaje::route('/create'),
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
