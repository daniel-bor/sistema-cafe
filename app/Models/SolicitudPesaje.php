<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudPesaje extends Model
{
    protected $fillable = ['cantidad_total', 'medida_peso_id', 'tolerancia', 'precio_unitario', 'cantidad_parcialidades', 'estado_id', 'agricultor_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function medidaPeso()
    {
        return $this->belongsTo(MedidaPeso::class);
    }
}
