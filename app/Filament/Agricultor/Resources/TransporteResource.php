<?php

namespace App\Filament\Agricultor\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Transporte;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Agricultor\Resources\TransporteResource\Pages;
use App\Filament\Agricultor\Resources\TransporteResource\RelationManagers;

class TransporteResource extends Resource
{
    protected static ?string $model = Transporte::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        try {
            $testCount = DB::table('transportes')->count(); // Eloquent/Query Builder
            Log::debug("[Test Query] Conteo de transportes (Query Builder): " . $testCount);

            $testRaw = DB::select('SELECT COUNT(*) FROM transportes');
            Log::debug("[Test Query] Conteo de transportes (Raw): " . $testRaw[0]->count);
        } catch (\Exception $e) {
            Log::error("[Test Query] FALLO al consultar transportes: " . $e->getMessage());
        }

        return $form
            ->schema([
                Forms\Components\TextInput::make('placa')
                    ->maxLength(7)
                    ->minLength(3)
                    ->required(),
                Forms\Components\TextInput::make('marca')
                    ->maxLength(50)
                    ->minLength(3)
                    ->required(),
                // Forms\Components\TextInput::make('modelo')
                //     ->maxLength(50)
                //     ->minLength(3)
                //     ->required(),
                Forms\Components\Select::make('color')
                    ->options([
                        'Blanco' => 'Blanco',
                        'Negro' => 'Negro',
                        'Rojo' => 'Rojo',
                        'Azul' => 'Azul',
                        'Gris' => 'Gris',
                        'Verde' => 'Verde',
                    ])
                    ->native(false)
                    ->required(),
                Forms\Components\Toggle::make('disponible')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('placa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('marca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('color')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado.nombre')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('disponible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime('Y-m-d')
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
            'index' => Pages\ListTransportes::route('/'),
            // 'create' => Pages\CreateTransporte::route('/create'),
            // 'edit' => Pages\EditTransporte::route('/{record}/edit'),
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
