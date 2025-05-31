<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EstadoPesaje: int implements HasLabel
{
    case NUEVO = 0;
    case PENDIENTE = 1;
    case ACEPTADO = 2;
    case RECHAZADO = 3;
    case PESAJE_INICIADO = 4;
    case PESAJE_FINALIZADO = 5;
    case CUENTA_ABIERTA = 6; // Estado adicional para indicar que la cuenta está abierta
    case CUENTA_CERRADA = 7; // Estado adicional para indicar que la cuenta está cerrada

    /**
     * Obtener todas las opciones como array para selects y forms
     *
     * @return array
     */

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NUEVO => 'Nuevo',
            self::PENDIENTE => 'Pendiente',
            self::ACEPTADO => 'Aceptado',
            self::RECHAZADO => 'Rechazado',
            self::PESAJE_INICIADO => 'Pesaje Iniciado',
            self::PESAJE_FINALIZADO => 'Pesaje Finalizado',
            self::CUENTA_ABIERTA => 'Cuenta Abierta',
            self::CUENTA_CERRADA => 'Cuenta Cerrada',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::NUEVO => 'info',
            self::PENDIENTE => 'warning',
            self::ACEPTADO => 'success',
            self::RECHAZADO => 'danger',
            self::PESAJE_INICIADO => 'primary',
            self::PESAJE_FINALIZADO => 'secondary',
            self::CUENTA_ABIERTA => 'blue',
            self::CUENTA_CERRADA => 'success',
        };
    }
}
