<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RedirectUserToCorrectPanel
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Si el usuario está en un panel que no corresponde a su rol,
        // la redirección se manejará por el middleware FilamentPanelAccess
        // Este listener es principalmente para referencia y futuras extensiones
    }
}
