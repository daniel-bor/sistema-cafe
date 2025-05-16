<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EstadoPesaje: int implements HasLabel
{
    case NUEVO = 0;
    case PENDIENTE = 1;
    case ACEPTADO = 2;
    case RECHAZADO = 3;
    case PROCESO = 4;
    case FINALIZADO = 5;

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
            self::PROCESO => 'En Proceso',
            self::FINALIZADO => 'Finalizado',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::NUEVO => 'info',
            self::PENDIENTE => 'warning',
            self::ACEPTADO => 'success',
            self::RECHAZADO => 'danger',
            self::PROCESO => 'primary',
            self::FINALIZADO => 'secondary',
        };
    }
}
