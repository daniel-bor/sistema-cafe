<?php

namespace App\Filament\Agricultor\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transportista;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Infolists;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Agricultor\Resources\TransportistaResource\Pages;
use App\Filament\Agricultor\Resources\TransportistaResource\RelationManagers;
use Illuminate\Database\Eloquent\Collection;

class TransportistaResource extends Resource
{
    protected static ?string $model = Transportista::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cui')
                    ->label('CUI')
                    ->mask('9999999999999')
                    ->required(),
                Forms\Components\TextInput::make('nombre_completo')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->closeOnDateSelection()
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()->subYears(18))
                    // ->native(false)
                    ->required(),
                Forms\Components\Select::make('tipo_licencia')
                    ->options(\App\Enums\TipoLicencia::labels())
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('fecha_vencimiento_licencia')
                    ->closeOnDateSelection()
                    ->displayFormat('d/m/Y')
                    ->minDate(now()->addDays(1))
                    // ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono')
                    ->mask('9999-9999')
                    ->required(),
                Forms\Components\Select::make('agricultor_id')
                    ->relationship('agricultor', 'nombre')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nombre_completo)
                    ->native(false)
                    ->required()
                    ->visible(fn() => auth()->user()->rol_id === 2),
                Forms\Components\Toggle::make('disponible')
                    ->required()
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false),
                Forms\Components\FileUpload::make('foto')
                    ->image()
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('transportistas')
                    ->avatar()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->imageEditorMode(2)
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1'),
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
                Tables\Columns\TextColumn::make('estado.nombre')
                    ->numeric()
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('disable')
                        ->label('Desactivar')
                        ->action(function (Collection $records) {
                            $inactivoId = \App\Models\Estado::where('nombre', 'INACTIVO')->first()?->id ?? 2;
                            $records->each->update(['estado_id' => $inactivoId]);
                        })
                        ->requiresConfirmation()
                        ->color('danger')
                        ->icon('heroicon-o-x-mark'),
                ]),
            ]);
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
