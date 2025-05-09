<?php

namespace App\Observers;

use App\Models\Pesaje;
use Illuminate\Support\Facades\Auth;

class PesajeObserver
{
    /**
     * Handle the Pesaje "created" event.
     */
    public function creating(Pesaje $pesaje): void
    {
        // Si el agricultor_id ya fue asignado manualmente, lo respetamos
        if ($pesaje->agricultor_id) {
            return;
        }

        // Obtenemos el usuario autenticado
        $user = Auth::user();

        if ($user && $user->agricultor) {
            // Si el usuario tiene un agricultor asociado, asignamos su ID
            $pesaje->agricultor_id = $user->agricultor->id;
        } else {
            // Si no tiene agricultor asociado, asignamos null
            $pesaje->agricultor_id = null;
        }
    }

    /**
     * Handle the Pesaje "updated" event.
     */
    public function updated(Pesaje $pesaje): void
    {
        //
    }

    /**
     * Handle the Pesaje "deleted" event.
     */
    public function deleted(Pesaje $pesaje): void
    {
        //
    }

    /**
     * Handle the Pesaje "restored" event.
     */
    public function restored(Pesaje $pesaje): void
    {
        //
    }

    /**
     * Handle the Pesaje "force deleted" event.
     */
    public function forceDeleted(Pesaje $pesaje): void
    {
        //
    }
}
