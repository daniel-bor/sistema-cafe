<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgricultorResource\Pages;
use App\Filament\Resources\AgricultorResource\RelationManagers;
use App\Models\Agricultor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgricultorResource extends Resource
{
    protected static ?string $model = Agricultor::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Agricultores';

    protected static ?string $pluralLabel = 'Agricultores';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    [
                        Forms\Components\TextInput::make('nit')
                            ->maxLength(15)
                            ->minLength(5)
                            ->required(),
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre(s)')
                            ->required(),
                        Forms\Components\TextInput::make('apellido')
                            ->label('Apellido(s)')
                            ->required(),
                        Forms\Components\TextInput::make('telefono')
                            ->tel()
                            ->required()
                            ->maxLength(15),
                        Forms\Components\TextInput::make('direccion')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->required(),
                        Forms\Components\Textarea::make('observaciones')
                            ->columnSpanFull(),
                    ]
                )
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombreCompleto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('direccion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->label('Correo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y')
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
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListAgricultors::route('/'),
            // 'create' => Pages\CreateAgricultor::route('/create'),
            // 'edit' => Pages\EditAgricultor::route('/{record}/edit'),
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
