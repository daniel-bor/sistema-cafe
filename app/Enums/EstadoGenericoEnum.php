<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EstadoGenericoEnum: int implements HasLabel
{
    case NUEVO = 0;
    case PENDIENTE = 1;
    case APROBADO = 2;
    case RECHAZADO = 3;

    /**
     * Obtener todas las opciones como array para selects y forms
     *
     * @return string
     */

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NUEVO => 'Nuevo',
            self::PENDIENTE => 'Pendiente',
            self::APROBADO => 'Aprobado',
            self::RECHAZADO => 'Rechazado',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::NUEVO => 'info',
            self::PENDIENTE => 'warning',
            self::APROBADO => 'success',
            self::RECHAZADO => 'danger',
        };
    }
}
