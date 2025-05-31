<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FilamentPanelAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $allowedPanel): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('filament.agricultor.auth.login');
        }

        // Los administradores pueden acceder a todos los paneles
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar acceso según el panel y rol
        $hasAccess = match ($allowedPanel) {
            'agricultor' => $user->canAccessAgricultorPanel(),
            'beneficio' => $user->canAccessBeneficioPanel(),
            'pesoCabal' => $user->canAccessPesoCabalPanel(),
            default => false,
        };

        if (!$hasAccess) {
            // Redirigir al panel correcto según el rol del usuario
            return redirect($user->getDefaultPanelUrl());
        }

        return $next($request);
    }
}
