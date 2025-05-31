<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransporteResource\Pages;
use App\Filament\Resources\TransporteResource\RelationManagers;
use App\Models\Transporte;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransporteResource extends Resource
{
    protected static ?string $model = Transporte::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Agricultores';
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'placa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn($state) => $state->getColor())
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
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\Action::make('aprobar')
                        ->label('Aprobar')
                        ->icon('heroicon-o-check')
                        ->action(fn(Transporte $record) => $record->aprobar())
                        ->color('success')
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn(Transporte $record) => $record->rechazar())
                        ->color('danger')
                        ->requiresConfirmation(),
                    Tables\Actions\ViewAction::make(),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransportes::route('/'),
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
