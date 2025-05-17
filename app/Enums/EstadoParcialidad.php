<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum EstadoParcialidad: int implements HasLabel
{
    case PENDIENTE = 0;
    case ENVIADO = 1;
    case RECIBIDO = 2;
    case RECHAZADO = 3;
    case PESADO = 4; // Ultimo estado hasta ahora
    case FINALIZADO = 5;

    /**
     * Obtener todas las opciones como array para selects y forms
     *
     * @return array
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::ENVIADO => 'Enviado',
            self::RECIBIDO => 'Recibido',
            self::RECHAZADO => 'Rechazado',
            self::PESADO => 'Pesado',
            self::FINALIZADO => 'Finalizado',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::ENVIADO => 'info',
            self::RECIBIDO => 'success',
            self::RECHAZADO => 'danger',
            self::PESADO => 'primary',
            self::FINALIZADO => 'secondary',
        };
    }
}
