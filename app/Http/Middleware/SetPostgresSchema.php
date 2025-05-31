<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Para depuración
use Symfony\Component\HttpFoundation\Response;

class SetPostgresSchema
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next)
    // {
    //     if (Auth::check() && DB::connection()->getDriverName() === 'pgsql') {
    //         $user = Auth::user(); // Asumiendo que el usuario tiene una relación 'rol' con 'nombre'
    //         $panelId = null;
    //         try {
    //             if (filament()->hasTenancy() && filament()->getTenant()) {
    //                 // Lógica si usas multi-tenancy
    //             } elseif (filament()->getCurrentPanel()) {
    //                 $panelId = filament()->getCurrentPanel()->getId();
    //             }
    //         } catch (\Throwable $e) {
    //             // Panel no disponible
    //         }

    //         $baseSearchPath = ['shared', 'public']; // Los esquemas 'shared' y 'public' siempre son necesarios.
    //         $contextSchemas = [];

    //         // Determinar el esquema contextual basado en el rol del usuario o el panel
    //         // Asumimos que tienes una forma de obtener el nombre del rol: $user->rol->nombre o $user->getRoleNames()->first() con Spatie
    //         $userRoleName = $user->rol->nombre ?? null; // Ajusta esto a cómo obtienes el nombre del rol

    //         if ($panelId === 'agricultor' || $userRoleName === 'Agricultor') {
    //             $contextSchemas = ['agricultor_context', 'beneficio_context', 'peso_cabal_context'];
    //         } elseif ($panelId === 'beneficio' || $userRoleName === 'Beneficio') {
    //             $contextSchemas = ['beneficio_context', 'agricultor_context', 'peso_cabal_context'];
    //         } elseif ($panelId === 'pesocabal' || $userRoleName === 'PesoCabal') {
    //             $contextSchemas = ['peso_cabal_context', 'beneficio_context', 'agricultor_context'];
    //         }
    //         // Añade más lógica para otros roles si es necesario

    //         // Unir esquemas contextuales con el base, asegurando que 'shared' y 'public' estén
    //         // El orden importa: los esquemas se buscan en el orden listado.
    //         // Ponemos los contextuales primero, luego shared y public.
    //         $finalSchemas = array_unique(array_merge($contextSchemas, $baseSearchPath));
    //         $searchPathSql = implode(',', $finalSchemas);

    //         if (!empty($searchPathSql)) {
    //             DB::statement("SET search_path TO {$searchPathSql}");
    //             Log::debug("[SetPostgresSchema] User {$user->id} ({$userRoleName}), Panel ({$panelId}). search_path set to: {$searchPathSql}");
    //         }
    //     } elseif (DB::connection()->getDriverName() === 'pgsql') {
    //         // Para usuarios no autenticados o situaciones donde Auth::check() es false pero hay conexión
    //         // Usar el search_path por defecto de la configuración
    //         $defaultSearchPath = config('database.connections.' . DB::getDefaultConnection() . '.search_path', 'shared, public');
    //         DB::statement("SET search_path TO {$defaultSearchPath}");
    //         Log::debug("[SetPostgresSchema] Unauthenticated/Fallback. search_path set to: {$defaultSearchPath}");
    //     }

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next)
    {
        Log::debug("[SetPostgresSchema] Middleware invoked for URI: " . $request->getRequestUri());

        if (DB::connection()->getDriverName() !== 'pgsql') {
            Log::debug("[SetPostgresSchema] Not a pgsql connection. Skipping.");
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $panelId = null;
            $userRoleName = $user->rol->nombre ?? 'ROLE_NOT_FOUND'; // Ajusta si 'rol' o 'nombre' es diferente

            Log::debug("[SetPostgresSchema] User Authenticated: ID {$user->id}, Role: {$userRoleName}");

            try {
                if (filament()->getCurrentPanel()) {
                    $panelId = filament()->getCurrentPanel()->getId();
                    Log::debug("[SetPostgresSchema] Current Filament Panel ID: {$panelId}");
                } else {
                    Log::debug("[SetPostgresSchema] No current Filament panel detected by filament()->getCurrentPanel()");
                }
            } catch (\Throwable $e) {
                Log::debug("[SetPostgresSchema] Exception getting panel: " . $e->getMessage());
            }

            $baseSearchPath = ['shared'];
            $contextSchemas = [];

            if ($panelId === 'agricultor' || $userRoleName === 'Agricultor') {
                Log::debug("[SetPostgresSchema] Agricultor context matched.");
                // Para un agricultor, necesita ver su propio contexto y potencialmente los otros para FKs
                $contextSchemas = ['agricultor_context', 'beneficio_context', 'peso_cabal_context'];
            } elseif ($panelId === 'beneficio' || $userRoleName === 'Beneficio') {
                Log::debug("[SetPostgresSchema] Beneficio context matched.");
                $contextSchemas = ['beneficio_context', 'agricultor_context', 'peso_cabal_context'];
            } elseif ($panelId === 'pesocabal' || $userRoleName === 'PesoCabal') {
                Log::debug("[SetPostgresSchema] PesoCabal context matched.");
                $contextSchemas = ['peso_cabal_context', 'beneficio_context', 'agricultor_context'];
            } else {
                Log::debug("[SetPostgresSchema] No specific role/panel context matched. Panel: {$panelId}, Role: {$userRoleName}");
            }

            $finalSchemas = array_unique(array_merge($contextSchemas, $baseSearchPath));
            $searchPathSql = implode(',', $finalSchemas);

            if (!empty($searchPathSql)) {
                DB::statement("SET search_path TO {$searchPathSql}");
                $currentSearchPath = DB::selectOne('SHOW search_path')->search_path;
                Log::info("[SetPostgresSchema] SUCCESS: search_path for User {$user->id} set to: {$currentSearchPath}");
            } else {
                Log::warning("[SetPostgresSchema] Calculated search_path was empty. User: {$user->id}");
            }
        } else {
            Log::debug("[SetPostgresSchema] User not authenticated or Auth::check() is false.");
            // Fallback al search_path de la configuración
            $defaultSearchPath = config('database.connections.' . DB::getDefaultConnection() . '.search_path', 'shared, public');
            DB::statement("SET search_path TO {$defaultSearchPath}");
            Log::info("[SetPostgresSchema] Fallback: search_path set to default: {$defaultSearchPath}");
        }

        return $next($request);
    }
}
