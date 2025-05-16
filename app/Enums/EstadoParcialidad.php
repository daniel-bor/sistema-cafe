<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum EstadoParcialidad: int implements HasLabel
{
    case PENDIENTE = 1;
    case ENVIADO = 2;
    case RECIBIDO = 3;
    case RECHAZADO = 4;
    case PESADO = 5; // Ultimo estado hasta ahora
    case FINALIZADO = 6;

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
