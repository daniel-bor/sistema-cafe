<?php

namespace App\Filament\PesoCabal\Resources;

use App\Enums\EstadoParcialidad;
use App\Filament\PesoCabal\Resources\ParcialidadResource\Pages;
use App\Filament\PesoCabal\Resources\ParcialidadResource\RelationManagers;
use App\Models\Parcialidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class ParcialidadResource extends Resource
{
    protected static ?string $model = Parcialidad::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Parcialidades';
    protected static ?string $pluralLabel = 'Parcialidades';
    protected static ?string $label = 'Parcialidad';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('peso')
                //     ->minValue(1)
                //     ->numeric()
                //     ->required(),
                // Forms\Components\Select::make('transporte_id')
                //     ->relationship(
                //         'transporte',
                //         'placa',
                //         fn(Builder $query) => $query->where('disponible', true)
                //     )
                //     ->native(false)
                //     ->required(),
                // Forms\Components\Select::make('transportista_id')
                //     ->relationship('transportista', 'nombre_completo')
                //     ->native(false)
                //     ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información de la Parcialidad')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID')
                            ->badge(),
                        Infolists\Components\TextEntry::make('peso')
                            ->label('Peso Esperado')
                            ->suffix(fn($record) => ' ' . $record->pesaje->medidaPeso->nombre),
                        Infolists\Components\TextEntry::make('peso_bascula')
                            ->label('Peso en Báscula')
                            ->suffix(fn($record) => ' ' . $record->pesaje->medidaPeso->nombre)
                            ->placeholder('No pesado'),
                        Infolists\Components\TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn($state) => $state->getColor()),
                        Infolists\Components\TextEntry::make('fecha_envio')
                            ->label('Fecha de Envío')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('No enviado'),
                        Infolists\Components\TextEntry::make('fecha_recepcion')
                            ->label('Fecha de Recepción')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('No recibido'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Información del Pesaje')
                    ->schema([
                        Infolists\Components\TextEntry::make('pesaje.id')
                            ->label('ID del Pesaje')
                            ->badge(),
                        Infolists\Components\TextEntry::make('pesaje.cuenta.no_cuenta')
                            ->label('Número de Cuenta')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('pesaje.cantidad_total')
                            ->label('Cantidad Total')
                            ->numeric(2, '.', ',')
                            ->suffix(fn($record) => ' ' . $record->pesaje->medidaPeso->nombre),
                        Infolists\Components\TextEntry::make('pesaje.medidaPeso.nombre')
                            ->label('Unidad de Medida')
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('pesaje.estado')
                            ->label('Estado del Pesaje')
                            ->badge()
                            ->color(fn($state) => $state->getColor()),
                        Infolists\Components\TextEntry::make('pesaje.observaciones')
                            ->label('Observaciones')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Información del Transporte')
                    ->schema([
                        Infolists\Components\TextEntry::make('transporte.placa')
                            ->label('Placa del Vehículo')
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('transporte.marca')
                            ->label('Marca'),
                        // Infolists\Components\TextEntry::make('transporte.modelo')
                        //     ->label('Modelo'),
                        // Infolists\Components\TextEntry::make('transporte.capacidad_carga')
                        //     ->label('Capacidad de Carga')
                        //     ->numeric(2, '.', ',')
                        //     ->suffix(' kg'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Información del Transportista')
                    ->schema([
                        Infolists\Components\ImageEntry::make('transportista.foto')
                            ->label('Foto')
                            ->circular()
                            ->size(100)
                            ->defaultImageUrl(url('/images/default-avatar.png')),
                        Infolists\Components\TextEntry::make('transportista.nombre_completo')
                            ->label('Nombre Completo')
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('transportista.cui')
                            ->label('Cédula')
                            ->badge(),
                        Infolists\Components\TextEntry::make('transportista.telefono')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone'),
                        // Infolists\Components\TextEntry::make('transportista.email')
                        //     ->label('Email')
                        //     ->icon('heroicon-o-envelope')
                        //     ->copyable(),
                        Infolists\Components\TextEntry::make('transportista.tipo_licencia_label')
                            ->label('Tipo Licencia')
                            ->badge()
                            ->color('info'),
                        // Infolists\Components\IconEntry::make('transportista.disponible')
                        //     ->label('Disponible')
                        //     ->boolean()
                        //     ->trueIcon('heroicon-o-check-circle')
                        //     ->falseIcon('heroicon-o-x-circle')
                        //     ->trueColor('success')
                        //     ->falseColor('danger'),
                    ])
                    ->columns(2),
            ]);
    }

    // Crea un infolist con los datos de la parcialidad, pesaje, tranporte y transportista asociados
    // Infolist:


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pesaje.cuenta.no_cuenta')
                    ->label('Cuenta')
                    ->sortable(),
                Tables\Columns\TextColumn::make('peso')
                    ->label('Peso Indicado')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pesaje.medidaPeso.nombre')
                    ->label('Unidad de Medida')
                    ->sortable(),
                Tables\Columns\TextColumn::make('peso_bascula')
                    ->label('Peso en Báscula')
                    ->sortable()
                    ->placeholder('No pesado')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('fecha_envio')
                    ->dateTime('d/m/Y H:i')
                    ->label('Fecha de Envio'),
                Tables\Columns\TextColumn::make('transporte.placa')
                    ->label('Transporte')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transportista.nombre_completo')
                    ->label('Transportista')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('transportista.foto')
                    ->label('Foto')
                    ->circular()
                    ->size(70),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->color(fn($state) => $state->getColor())
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultGroup(Group::make('pesaje.cuenta.no_cuenta')
                ->collapsible())
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Aceptar / rechazar
                    Tables\Actions\Action::make('aceptar')
                        ->label('Aceptar')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Parcialidad $record) {
                            $record->update([
                                'estado' => EstadoParcialidad::RECIBIDO,
                                'fecha_recepcion' => now(),
                            ]);
                            // Verificar si es la primera parcialidad con pesaje
                            $hasOtherWeighedParcialidades = $record->pesaje->parcialidades
                                ->where('id', '!=', $record->id)
                                ->filter(fn($parcialidad) => $parcialidad->peso_bascula !== null)
                                ->count() > 0;

                            // Si ninguna otra parcialidad ha sido pesada, actualizar estado del pesaje
                            if (!$hasOtherWeighedParcialidades) {
                                $record->pesaje->cuenta->update(['estado' => \App\Enums\EstadoCuentaEnum::CUENTA_ABIERTA]);
                                $record->pesaje->update([
                                    'estado' => \App\Enums\EstadoPesaje::CUENTA_ABIERTA,
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn($record) => $record->estado == EstadoParcialidad::ENVIADO),
                    Tables\Actions\Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function (Parcialidad $record) {
                            $record->update([
                                'estado' => EstadoParcialidad::RECHAZADO,
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(fn($record) => $record->estado == EstadoParcialidad::ENVIADO),
                    Tables\Actions\Action::make('pesar')
                        ->label('Pesar')
                        ->icon('heroicon-o-scale')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('peso_bascula')
                                ->default(fn($record) => $record?->peso_bascula)
                                ->label('Peso en Bascula')
                                ->minValue(1)
                                ->maxValue(1000000)
                                ->numeric()
                                ->required(),
                            // Observaciones opcionales
                            Forms\Components\Textarea::make('observaciones')
                                ->label('Observaciones')
                                ->placeholder('Ingrese observaciones si es necesario')
                                ->maxLength(255),
                        ])
                        ->action(function (Parcialidad $record, array $data) {
                            $record->update([
                                'peso_bascula' => $data['peso_bascula'],
                                'observaciones' => $data['observaciones'] ?? null,
                                'estado' => EstadoParcialidad::PESADO,
                            ]);

                            // Actualizar el estado del pesaje relacionado
                            $record->pesaje->update([
                                'estado' => \App\Enums\EstadoPesaje::PESAJE_INICIADO,
                            ]);
                            $record->pesaje->cuenta->update(['estado' => \App\Enums\EstadoCuentaEnum::PESAJE_INICIADO]);

                            // Verificar si todas las parcialidades de este pesaje específico han sido pesadas
                            $totalParcialidadesPesaje = $record->pesaje->parcialidades()
                                ->where('estado', '!=', EstadoParcialidad::RECHAZADO)
                                ->count();

                            $parcialidadesPesadas = $record->pesaje->parcialidades()
                                ->where('estado', EstadoParcialidad::PESADO)
                                ->count();

                            // Si todas las parcialidades de este pesaje han sido pesadas,
                            // actualizar el estado del pesaje a PESAJE_FINALIZADO
                            if ($totalParcialidadesPesaje === $parcialidadesPesadas && $totalParcialidadesPesaje > 0) {
                                $record->pesaje->update([
                                    'estado' => \App\Enums\EstadoPesaje::PESAJE_FINALIZADO,
                                ]);

                                // Verificar si todos los pesajes de la cuenta han sido finalizados
                                $allPesajesFinalizados = !$record->pesaje->cuenta->pesajes()
                                    ->where('estado', '!=', \App\Enums\EstadoPesaje::PESAJE_FINALIZADO)
                                    ->exists();

                                // Si todos los pesajes de la cuenta están finalizados, cerrar la cuenta
                                if ($allPesajesFinalizados) {
                                    $record->pesaje->cuenta->update(['estado' => \App\Enums\EstadoCuentaEnum::CUENTA_CERRADA]);
                                }
                            }
                        })
                        ->visible(fn($record) => $record->estado == EstadoParcialidad::RECIBIDO),
                    Tables\Actions\Action::make('finalizar')
                        ->label('Finalizar')
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->action(function (Parcialidad $record) {
                            $record->update([
                                'estado' => EstadoParcialidad::FINALIZADO,
                            ]);
                            // Actualizar estado del transporte y transportista a disponible
                            $record->transporte->update(['disponible' => true]);
                            $record->transportista->update(['disponible' => true]);

                            // Validar si es la ultima parcialidad del pesaje y validar que porcentaje_diferencia sea < a la tolerancia del pesaje
                            // Si todo es correcto, actualizar el estado del pesaje y la cuenta a CUENTA_CONFIRMADA

                            // Obtener el pesaje relacionado
                            $pesaje = $record->pesaje;

                            // Verificar si todas las parcialidades del pesaje están finalizadas
                            $totalParcialidades = $pesaje->parcialidades()
                                ->where('estado', '!=', EstadoParcialidad::RECHAZADO)
                                ->count();

                            $parcialidadesFinalizadas = $pesaje->parcialidades()
                                ->where('estado', EstadoParcialidad::FINALIZADO)
                                ->count();

                            // Si es la última parcialidad en finalizar del pesaje
                            if ($totalParcialidades === $parcialidadesFinalizadas && $totalParcialidades > 0) {
                                // Calcular el porcentaje de diferencia
                                $porcentajeDiferencia = abs($pesaje->porcentaje_diferencia);
                                $tolerancia = $pesaje->tolerancia ?? 0;

                                // Verificar si la diferencia está dentro de la tolerancia
                                if ($porcentajeDiferencia <= $tolerancia) {
                                    // Actualizar estado del pesaje a PESAJE_FINALIZADO
                                    $pesaje->update([
                                        'estado' => \App\Enums\EstadoPesaje::PESAJE_FINALIZADO,
                                    ]);

                                    // Verificar si todos los pesajes de la cuenta han sido finalizados
                                    $allPesajesFinalizados = !$pesaje->cuenta->pesajes()
                                        ->where('estado', '!=', \App\Enums\EstadoPesaje::PESAJE_FINALIZADO)
                                        ->exists();

                                    // Si todos los pesajes de la cuenta están finalizados, confirmar la cuenta
                                    if ($allPesajesFinalizados && $pesaje->cuenta) {
                                        $pesaje->cuenta->update([
                                            'estado' => \App\Enums\EstadoCuentaEnum::CUENTA_CONFIRMADA
                                        ]);

                                        // Notificar que la cuenta ha sido confirmada
                                        \Filament\Notifications\Notification::make()
                                            ->title('Cuenta confirmada')
                                            ->body("Todos los pesajes de la cuenta {$pesaje->cuenta->no_cuenta} han sido completados exitosamente.")
                                            ->success()
                                            ->send();
                                    }

                                    // Notificar éxito del pesaje individual
                                    \Filament\Notifications\Notification::make()
                                        ->title('Pesaje completado exitosamente')
                                        ->body("El pesaje se ha finalizado. Diferencia: {$porcentajeDiferencia}% (Tolerancia: {$tolerancia}%)")
                                        ->success()
                                        ->send();
                                } else {
                                    // Notificar que excede la tolerancia
                                    \Filament\Notifications\Notification::make()
                                        ->title('Tolerancia excedida')
                                        ->body("La diferencia de peso ({$porcentajeDiferencia}%) excede la tolerancia permitida ({$tolerancia}%)")
                                        ->warning()
                                        ->send();
                                }
                            }
                        })
                        ->visible(fn($record) => $record->estado == EstadoParcialidad::PESADO),
                    Tables\Actions\Action::make('generar_boleta')
                        ->label('Generar Boleta')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->action(function (Parcialidad $record) {
                            // Cargar todas las relaciones necesarias
                            $record->load([
                                'pesaje.cuenta',
                                'pesaje.medidaPeso',
                                'transporte',
                                'transportista'
                            ]);

                            try {
                                $pdf = Pdf::loadView('boleta.boleta-pdf', ['parcialidad' => $record])
                                    ->setPaper('a4', 'portrait');

                                $filename = 'boleta_parcialidad_' . $record->id . '_' . date('Y-m-d_H-i-s') . '.pdf';

                                return response()->streamDownload(function () use ($pdf) {
                                    echo $pdf->output();
                                }, $filename, [
                                    'Content-Type' => 'application/pdf',
                                ]);
                            } catch (\Exception $e) {
                                \Log::error('Error generando boleta PDF: ' . $e->getMessage());

                                // Mostrar notificación de error al usuario
                                \Filament\Notifications\Notification::make()
                                    ->title('Error al generar la boleta')
                                    ->body('Hubo un problema al generar el PDF. Intente nuevamente.')
                                    ->danger()
                                    ->send();

                                return false;
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Generar Boleta PDF')
                        ->modalDescription('¿Está seguro de que desea generar la boleta PDF para esta parcialidad?')
                        ->modalSubmitActionLabel('Generar Boleta')
                        ->visible(fn($record) => $record->estado == EstadoParcialidad::PESADO),
                ])
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
            'index' => Pages\ListParcialidads::route('/'),
            // 'create' => Pages\CreateParcialidad::route('/create'),
            'view' => Pages\ViewParcialidad::route('/{record}'),
            // 'edit' => Pages\EditParcialidad::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('estado', EstadoParcialidad::ENVIADO)
            ->orWhere('estado', EstadoParcialidad::RECIBIDO)
            ->orWhere('estado', EstadoParcialidad::PESADO);
    }
}
