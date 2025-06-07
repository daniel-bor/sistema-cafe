<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InjectDbSessionVariables
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener IP y usuario
        $ip       = $request->ip();
        $userId   = Auth::id() ?? 0;

        // Inyectar en la sesión de Postgres
        // Nota: usamos binds para evitar SQL injection
        DB::statement('SET app.client_ip = ?', [$ip]);
        DB::statement('SET app.current_user = ?', [$userId]);

        return $next($request);
    }
}
