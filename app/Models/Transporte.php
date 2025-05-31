<?php

namespace App\Models;

use App\Enums\EstadoGenericoEnum;
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

    // cast estado como enum
    protected $casts = [
        'estado' => EstadoGenericoEnum::class,
    ];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
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

    public function aprobar()
    {
        $this->estado = EstadoGenericoEnum::APROBADO;
        $this->save();
    }

    public function rechazar()
    {
        $this->estado = EstadoGenericoEnum::RECHAZADO;
        $this->save();
    }

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

            // Crear con estado pendiente utilizando el enum
            $transporte->estado = \App\Enums\EstadoGenericoEnum::PENDIENTE->value;

            // Assign agricultor_id from authenticated user
            if (auth()->check()) {
                $user = auth()->user();
                Log::debug("[Transporte creating boot] User authenticated: ID {$user->id}");
                if ($user->agricultor) { // Esto dispara la consulta a 'agricultores'
                    $transporte->agricultor_id = $user->agricultor->id;
                    Log::debug("[Transporte creating boot] agricultor_id set to: {$user->agricultor->id}");
                } else {
                    Log::warning("[Transporte creating boot] User ID {$user->id} does not have an 'agricultor' related model.");
                }
            } else {
                Log::warning("[Transporte creating boot] No authenticated user found.");
            }
        });
    }
}
