<?php

namespace App\Providers;

use App\Models\Pesaje;
use App\Observers\PesajeObserver;
use App\Policies\FilamentResourcePolicy;
use Illuminate\Support\Facades\Gate;
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

        // Registrar políticas de Gates para Filament
        $this->registerFilamentGates();
    }

    /**
     * Registrar Gates para controlar acceso a recursos de Filament
     */
    private function registerFilamentGates(): void
    {
        // Gates para panel Agricultor
        Gate::define('viewAny-agricultor-resource', [FilamentResourcePolicy::class, 'viewAnyAgricultorResource']);
        Gate::define('create-agricultor-resource', [FilamentResourcePolicy::class, 'createAgricultorResource']);
        Gate::define('update-agricultor-resource', [FilamentResourcePolicy::class, 'updateAgricultorResource']);
        Gate::define('delete-agricultor-resource', [FilamentResourcePolicy::class, 'deleteAgricultorResource']);

        // Gates para panel Beneficio
        Gate::define('viewAny-beneficio-resource', [FilamentResourcePolicy::class, 'viewAnyBeneficioResource']);
        Gate::define('create-beneficio-resource', [FilamentResourcePolicy::class, 'createBeneficioResource']);
        Gate::define('update-beneficio-resource', [FilamentResourcePolicy::class, 'updateBeneficioResource']);
        Gate::define('delete-beneficio-resource', [FilamentResourcePolicy::class, 'deleteBeneficioResource']);

        // Gates para panel Peso Cabal
        Gate::define('viewAny-pesocabal-resource', [FilamentResourcePolicy::class, 'viewAnyPesoCabalResource']);
        Gate::define('create-pesocabal-resource', [FilamentResourcePolicy::class, 'createPesoCabalResource']);
        Gate::define('update-pesocabal-resource', [FilamentResourcePolicy::class, 'updatePesoCabalResource']);
        Gate::define('delete-pesocabal-resource', [FilamentResourcePolicy::class, 'deletePesoCabalResource']);
    }
}
