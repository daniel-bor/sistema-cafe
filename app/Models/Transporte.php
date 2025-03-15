<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transporte extends Model
{
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
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($transporte) {
            // Cache the active state ID to avoid repeated queries
            $transporte->estado_id = \Cache::remember('estado_activo_id', 86400, function () {
                return Estado::where('nombre', 'ACTIVO')->first()->id;
            });

            // Assign agricultor_id from authenticated user
            if (auth()->check() && auth()->user()->agricultor) {
                $transporte->agricultor_id = auth()->user()->agricultor->id;
            }
        });
    }
}
