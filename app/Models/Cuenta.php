<?php

namespace App\Models;

use App\Enums\EstadoCuentaEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cuenta extends Model
{
    protected $fillable = ['no_cuenta', 'estado', 'agricultor_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    // soft delete
    use SoftDeletes;

    protected $casts = [
        'estado' => EstadoCuentaEnum::class,
    ];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function pesajes()
    {
        return $this->hasMany(Pesaje::class);
    }

    // Inicializar el estado de la cuenta con boot implementando su enum estado CREADO
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cuenta) {
            $cuenta->estado = EstadoCuentaEnum::CUENTA_CREADA;
        });
    }
}
