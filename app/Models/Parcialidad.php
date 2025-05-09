<?php

namespace App\Models;

use App\Enums\EstadoParcialidad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parcialidad extends Model
{
    use SoftDeletes;
    protected $table = 'parcialidades';
    protected $fillable = ['pesaje_id', 'transporte_id', 'transportista_id', 'peso', 'tipo_medida', 'fecha_recepcion', 'estado', 'codigo_qr'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'estado' => EstadoParcialidad::class,
        'fecha_recepcion' => 'datetime'
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
}
