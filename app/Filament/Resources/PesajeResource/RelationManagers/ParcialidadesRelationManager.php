<?php

namespace App\Filament\Resources\PesajeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
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
                Tables\Columns\TextColumn::make('fecha_recepcion')
                    ->dateTime('d/m/Y H:i')
                    ->label('Recibido'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
