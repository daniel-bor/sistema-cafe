<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AgricultorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('agricultor')
            ->path('')
            ->colors([
                'primary' => Color::Green,
            ])
            ->login()
            ->discoverResources(in: app_path('Filament/Agricultor/Resources'), for: 'App\\Filament\\Agricultor\\Resources')
            ->discoverPages(in: app_path('Filament/Agricultor/Pages'), for: 'App\\Filament\\Agricultor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Agricultor/Widgets'), for: 'App\\Filament\\Agricultor\\Widgets')
            ->widgets([])
            ->authMiddleware([
                Authenticate::class,
                'filament.panel.access:agricultor',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetPostgresSchema::class,
            ])
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->profile(isSimple: false)
            ->unsavedChangesAlerts();
    }
}
