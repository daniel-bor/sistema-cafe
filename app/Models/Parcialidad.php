<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcialidad extends Model
{
    protected $table = 'parcialidades';
    protected $fillable = ['pesaje_id', 'transporte_id', 'transportista_id', 'peso', 'tipo_medida', 'fecha_recepcion', 'estado_id', 'codigo_qr'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

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

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}
