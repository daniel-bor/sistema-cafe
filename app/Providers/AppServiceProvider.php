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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pesaje::observe(PesajeObserver::class);
    }
}
