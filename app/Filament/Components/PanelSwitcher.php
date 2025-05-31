<?php

namespace App\Filament\Components;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Auth;

class PanelSwitcher
{
    /**
     * Obtener los elementos de navegación para cambiar entre paneles
     */
    public static function getNavigationItems(): array
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return [];
        }

        return [
            NavigationGroup::make('Cambiar Panel')
                ->items([
                    NavigationItem::make('Panel Agricultor')
                        ->url('/')
                        ->icon('heroicon-o-users')
                        ->visible($user->canAccessAgricultorPanel()),

                    NavigationItem::make('Panel Beneficio')
                        ->url('/beneficio')
                        ->icon('heroicon-o-building-office')
                        ->visible($user->canAccessBeneficioPanel()),

                    NavigationItem::make('Panel Peso Cabal')
                        ->url('/peso-cabal')
                        ->icon('heroicon-o-scale')
                        ->visible($user->canAccessPesoCabalPanel()),
                ])
                ->collapsible()
        ];
    }
}
