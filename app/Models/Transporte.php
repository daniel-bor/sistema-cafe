<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transporte extends Model
{
    protected $table = 'transportes';
    use SoftDeletes;
    protected $fillable = ['placa', 'marca', 'color', 'estado_id', 'disponible', 'agricultor_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    // al crear un registro establecer el estado como activo
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($transporte) {
    //         // Cache the active state ID to avoid repeated queries
    //         $transporte->estado_id = \Cache::remember('estado_activo_id', 86400, function () {
    //             return Estado::where('nombre', 'ACTIVO')->first()->id;
    //         });

    //         // Assign agricultor_id from authenticated user
    //         if (auth()->check() && auth()->user()->agricultor) {
    //             $transporte->agricultor_id = auth()->user()->agricultor->id;
    //         }
    //     });
    // }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transporte) {
            // Log para depurar el search_path
            if (DB::connection()->getDriverName() === 'pgsql') {
                try {
                    $currentSearchPath = DB::selectOne('SHOW search_path')->search_path;
                    Log::debug("[Transporte creating boot] Current search_path: " . $currentSearchPath);
                } catch (\Exception $e) {
                    Log::error("[Transporte creating boot] Error fetching search_path: " . $e->getMessage());
                }
            }

            // Cache the active state ID to avoid repeated queries
            // Asegúrate que el modelo Estado y su tabla 'estados' estén accesibles (deberían estar en 'shared')
            $transporte->estado_id = \Cache::remember('estado_activo_id', 86400, function () {
                // Si 'estados' está en 'shared' y 'shared' está en el search_path, esto debería funcionar.
                return \App\Models\Estado::where('nombre', 'ACTIVO')->where('contexto', 'TRANSPORTE')->firstOrFail()->id; // Sé más específico con el contexto del estado
            });

            // Assign agricultor_id from authenticated user
            if (auth()->check()) {
                $user = auth()->user();
                Log::debug("[Transporte creating boot] User authenticated: ID {$user->id}");
                if ($user->agricultor) { // Esto dispara la consulta a 'agricultores'
                    $transporte->agricultor_id = $user->agricultor->id;
                    Log::debug("[Transporte creating boot] agricultor_id set to: {$user->agricultor->id}");
                } else {
                    Log::warning("[Transporte creating boot] User ID {$user->id} does not have an 'agricultor' related model.");
                    // Considera qué hacer aquí: ¿lanzar una excepción, no crear el transporte, etc.?
                }
            } else {
                Log::warning("[Transporte creating boot] No authenticated user found.");
                // Considera el caso: ¿Debería un transporte crearse sin un usuario autenticado?
            }
        });
    }
}
