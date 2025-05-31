<?php

namespace App\Models;

use App\Enums\EstadoParcialidad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parcialidad extends Model
{
    use SoftDeletes;
    protected $table = 'parcialidades';
    protected $fillable = ['pesaje_id', 'transporte_id', 'transportista_id', 'peso', 'peso_bascula', 'fecha_recepcion', 'fecha_envio','estado', 'codigo_qr', 'observaciones'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'estado' => EstadoParcialidad::class,
        'fecha_recepcion' => 'datetime',
        'fecha_envio' => 'datetime'
    ];

    public function pesaje()
    {
        return $this->belongsTo(Pesaje::class);
    }

    public function transporte()
    {
        return $this->belongsTo(Transporte::class);
    }

    public function transportista()
    {
        return $this->belongsTo(Transportista::class);
    }

    // Inicializar el estado como PENDIENTE al crear un registro
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($parcialidad) {
            // Asignar el estado PENDIENTE al crear un nuevo registro
            $parcialidad->estado = EstadoParcialidad::PENDIENTE;
        });
    }
}
