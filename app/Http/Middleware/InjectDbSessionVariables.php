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
        $ip     = $request->ip();
        $userId = Auth::id() ?? 0;

        // NOTA: aquí interpolamos directamente y ponemos comillas en la IP
        DB::unprepared("SET \"app.client_ip\" = '{$ip}'");
        DB::unprepared("SET \"app.current_user\" = '{$userId}'");


        return $next($request);
    }
}
