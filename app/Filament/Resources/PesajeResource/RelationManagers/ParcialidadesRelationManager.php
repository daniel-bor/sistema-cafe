<?php

namespace App\Filament\Resources\PesajeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ParcialidadesRelationManager extends RelationManager
{
    protected static string $relationship = 'parcialidades';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('peso')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('peso')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->color(fn($state) => $state->getColor())
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transporte.placa')
                    ->label('Transporte')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transportista.nombre_completo')
                    ->label('Transportista')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('transportista.foto')
                    ->label('Foto')
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('peso')
                    ->label('Peso')
                    ->sortable()
                    ->summarize([
                        Sum::make()->label('Total'),
                    ]),
                Tables\Columns\TextColumn::make('pesaje.medidaPeso.nombre')
                    ->label('Transporte'),
                Tables\Columns\TextColumn::make('fecha_recepcion')
                    ->dateTime('d/m/Y H:i')
                    ->label('Fecha Recepción'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver detalle')
                    ->modalHeading('Detalle de Parcialidad')
                    ->modalWidth('6xl')
                    ->infolist(fn(Infolist $infolist): Infolist => $this->parcialidadInfolist($infolist)),
            ])
            ->bulkActions([]);
    }

    public function parcialidadInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Información básica de la parcialidad
                Infolists\Components\Section::make('Información de la Parcialidad')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('pesaje.cuenta.no_cuenta')
                                    ->label('No. de Cuenta')
                                    ->badge()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('id')
                                    ->label('ID Parcialidad')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\ViewEntry::make('codigo_qr')
                                    ->label('Código Escaneable')
                                    ->view('filament.components.qr-code')
                                    ->state(fn($record) => route('parcialidad.qr', ['id' => $record->id]))
                                    ->extraAttributes(['size' => 120]),
                            ]),
                    ])
                    ->collapsible()
                    ->icon('heroicon-o-information-circle'),

                // Información del transporte y transportista
                Infolists\Components\Section::make('Información de Transporte')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('id')
                                    ->label('No. de Parcialidad')
                                    ->weight('bold')
                                    ->size('lg'),
                                Infolists\Components\TextEntry::make('transporte.placa')
                                    ->label('Placa')
                                    ->badge()
                                    ->color('success'),
                                Infolists\Components\TextEntry::make('transporte.marca')
                                    ->label('Transporte')
                                    ->formatStateUsing(fn($record) =>
                                        $record->transporte
                                            ? $record->transporte->marca . ' - ' . $record->transporte->color
                                            : 'Sin asignar'
                                    )
                                    ->placeholder('Sin asignar'),
                                Infolists\Components\TextEntry::make('estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn($state) => $state->getColor()),
                                Infolists\Components\TextEntry::make('peso')
                                    ->label('Peso')
                                    ->formatStateUsing(fn($record) =>
                                        $record->peso . ' ' . ($record->pesaje->medidaPeso->nombre ?? 'kg')
                                    )
                                    ->weight('bold')
                                    ->color('success'),
                                Infolists\Components\TextEntry::make('peso_bascula')
                                    ->label('Peso en Báscula')
                                    ->formatStateUsing(fn($record) =>
                                        $record->peso_bascula
                                            ? $record->peso_bascula . ' ' . ($record->pesaje->medidaPeso->nombre ?? 'kg')
                                            : 'No pesado'
                                    )
                                    ->badge()
                                    ->color(fn($record) => $record->peso_bascula ? 'success' : 'warning'),
                                Infolists\Components\TextEntry::make('pesaje.observaciones')
                                    ->label('Observaciones del Pesaje')
                                    ->placeholder('Sin observaciones')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible()
                    ->icon('heroicon-o-truck'),

                Infolists\Components\Section::make('Información del Transportista')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\ImageEntry::make('transportista.foto')
                                    ->label('Foto')
                                    ->circular()
                                    ->size(100)
                                    ->defaultImageUrl(url('/images/default-avatar.png')),
                                Infolists\Components\TextEntry::make('transportista.cui')
                                    ->label('CUI')
                                    ->copyable()
                                    ->icon('heroicon-o-identification'),
                                Infolists\Components\TextEntry::make('transportista.nombre_completo')
                                    ->label('Nombre Completo')
                                    ->weight('bold')
                                    ->size('lg'),
                                Infolists\Components\TextEntry::make('transportista.estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn($state) => $state->getColor()),
                                Infolists\Components\TextEntry::make('transportista.telefono')
                                    ->label('Teléfono')
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('transportista.tipo_licencia_label')
                                    ->label('Tipo de Licencia')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('fecha_envio')
                                    ->label('Fecha de Envío')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('No enviado')
                                    ->icon('heroicon-o-truck'),
                                Infolists\Components\TextEntry::make('fecha_recepcion')
                                    ->label('Fecha de Recepción')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('No recibido')
                                    ->icon('heroicon-o-check-circle'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Creación')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-calendar'),
                            ]),
                    ])
                    ->collapsible()
                    ->icon('heroicon-o-user'),
            ]);
    }
}
