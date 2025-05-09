<?php

namespace App\Enums;

enum EstadoParcialidad: int
{
    case PENDIENTE = 1;
    case ENVIADO = 2;
    case RECIBIDO = 3;
    case RECHAZADO = 4;
    case PESADO = 5;
    case FINALIZADO = 6;

    /**
     * Obtener todas las opciones como array para selects y forms
     *
     * @return array
     */
    public static function opciones(): array
    {
        return [
            self::PENDIENTE->value => 'Pendiente',
            self::ENVIADO->value => 'Enviado',
            self::RECIBIDO->value => 'Recibido',
            self::RECHAZADO->value => 'Rechazado',
            self::PESADO->value => 'Pesado',
            self::FINALIZADO->value => 'Finalizado',
        ];
    }
}
