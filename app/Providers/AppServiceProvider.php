<?php

namespace App\Providers;

use App\Models\Pesaje;
use App\Observers\PesajeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar el servicio de cuentas como singleton en el contenedor
        $this->app->singleton(\App\Services\CuentaService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pesaje::observe(PesajeObserver::class);
    }
}
