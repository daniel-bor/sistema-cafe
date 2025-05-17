<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EstadoCuentaEnum: int implements HasLabel
{
    case CUENTA_CREADA = 0;
    case CUENTA_ABIERTA = 1;
    case PESAJE_INICIADO = 2;
    case PESAJE_FINALIZADO = 3;
    case CUENTA_CERRADA = 4; // Ultimo estado hasta ahora
    case CUENTA_CONFIRMADA = 5;

    /**
     * Obtener todas las opciones como array para selects y forms
     *
     * @return array
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::CUENTA_CREADA => 'Creada',
            self::CUENTA_ABIERTA => 'Abierta',
            self::PESAJE_INICIADO => 'Pesaje Iniciado',
            self::PESAJE_FINALIZADO => 'Pesaje Finalizado',
            self::CUENTA_CERRADA => 'Cerrada',
            self::CUENTA_CONFIRMADA => 'Confirmada',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::CUENTA_CREADA => 'gray',
            self::CUENTA_ABIERTA => 'blue',
            self::PESAJE_INICIADO => 'yellow',
            self::PESAJE_FINALIZADO => 'green',
            self::CUENTA_CERRADA => 'red',
            self::CUENTA_CONFIRMADA => 'purple',
        };
    }
}
