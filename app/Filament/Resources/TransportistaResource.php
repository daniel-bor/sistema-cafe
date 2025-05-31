<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransportistaResource\Pages;
use App\Filament\Resources\TransportistaResource\RelationManagers;
use App\Models\Transportista;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransportistaResource extends Resource
{
    protected static ?string $model = Transportista::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Agricultores';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'nombre_completo';

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
                Tables\Columns\TextColumn::make('cui')
                    ->label('DPI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_licencia_label')
                    ->label('Tipo de Licencia'),
                Tables\Columns\TextColumn::make('fecha_vencimiento_licencia')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agricultor.nombreCompleto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn($state) => $state->getColor())
                    ->sortable(),
                Tables\Columns\IconColumn::make('disponible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Desactivado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // group
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('aprobar')
                        ->label('Aprobar')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn(Transportista $record) => $record->aprobar()),
                    Tables\Actions\Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn(Transportista $record) => $record->rechazar()),
                    Tables\Actions\ViewAction::make(),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(['sm' => 3])
                    ->schema([
                        Infolists\Components\ImageEntry::make('foto')
                            ->label('Foto')
                            ->disk('public')
                            ->circular(),
                        Infolists\Components\TextEntry::make('nombre_completo')
                            ->label('Nombre Completo'),
                        Infolists\Components\TextEntry::make('cui')
                            ->label('CUI'),
                        Infolists\Components\TextEntry::make('fecha_nacimiento')
                            ->label('Nacimiento'),
                        Infolists\Components\TextEntry::make('tipo_licencia_label')
                            ->label('Tipo de Licencia'),
                        Infolists\Components\TextEntry::make('fecha_vencimiento_licencia')
                            ->label('Vencimiento de Licencia'),
                        Infolists\Components\TextEntry::make('agricultor.nombreCompleto')
                            ->label('Agricultor'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransportistas::route('/'),
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
