<?php

namespace App\Filament\Agricultor\Resources\PesajeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\EstadoPesaje;
use App\Models\Parcialidad;
use App\Enums\EstadoParcialidad;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Count;
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
                    ->minValue(1)
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('transporte_id')
                    ->relationship(
                        'transporte',
                        'placa',
                        fn(Builder $query) => $query->where('disponible', true)
                    )
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('transportista_id')
                    ->relationship('transportista', 'nombre_completo')
                    ->native(false)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                // Estado
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->color(fn($state) => $state->getColor())
                    ->badge()
                    ->sortable(),
                // Placa de transporte
                Tables\Columns\TextColumn::make('transporte.placa')
                    ->label('Transporte')
                    ->sortable(),
                // Nombre del transportista
                Tables\Columns\TextColumn::make('transportista.nombre_completo')
                    ->label('Transportista')
                    ->sortable(),
                // Imagen tipo avatar del transportista
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
                // Tables\Columns\TextColumn::make('tipo_medida')->label('Medida'),
                Tables\Columns\TextColumn::make('fecha_recepcion')
                    ->dateTime('d/m/Y H:i')
                    ->label('Recibido'),
                // Tables\Columns\TextColumn::make('codigo_qr')->label('QR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->createAnother(false)
                    // ->beforeFormValidated(), //Validar que la suma de pesos de parcialidades no sea mayor al peso total del pesaje
                    ->mutateFormDataUsing(function (array $data, CreateAction $action): array {
                        // Convertir el peso total a float
                        $pesoNuevo = (float) $data['peso'];
                        $pesaje = $this->ownerRecord;
                        $pesoTotal = (float) $pesaje->cantidad_total;
                        $pesoParcialidades = $pesaje->parcialidades()->sum('peso');

                        if ($pesoParcialidades + $pesoNuevo > $pesoTotal) {
                            Notification::make()
                                ->title('Error')
                                ->body('El peso total de las parcialidades no puede ser mayor al peso total del pesaje.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn($record) => $record->estado == EstadoParcialidad::PENDIENTE || $record->estado == EstadoParcialidad::RECHAZADO),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn($record) => $record->estado == EstadoParcialidad::PENDIENTE || $record->estado == EstadoParcialidad::RECHAZADO),
                    // Tables\Actions\Action::make('verQR')
                    //     ->label('Ver QR')
                    //     ->url(fn(Model $record) => route('filament.resources.pesajes.pesaje.qr', $record->id))
                    //     ->icon('heroicon-o-qrcode')
                    //     ->openUrlInNewTab()
                    //     ->color('success'),
                    Tables\Actions\Action::make('enviar')
                        ->label('Realizar envío')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->action(function (Parcialidad $record) {
                            // Validar que el transporte y el transportista estén disponibles
                            if (!$record->transporte->disponible || !$record->transportista->disponible) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('El transporte o el transportista no están disponibles.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $record->estado = EstadoParcialidad::ENVIADO;
                            $record->fecha_envio = now();
                            $record->save();

                            // Actualizar estado de transporte y transportista a no disponible
                            $record->transporte->disponible = false;
                            $record->transporte->save();
                            $record->transportista->disponible = false;
                            $record->transportista->save();

                            Notification::make()
                                ->title('Éxito')
                                ->body('Parcialidad enviada correctamente.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn($record) => ($record->pesaje->estado == EstadoPesaje::ACEPTADO || $record->pesaje->estado == EstadoPesaje::PESAJE_INICIADO) && $record->estado == EstadoParcialidad::PENDIENTE),
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
