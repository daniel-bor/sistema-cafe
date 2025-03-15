<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesaje extends Model
{
    protected $fillable = ['medida_peso_id', 'peso_total', 'estado_id', 'solicitud_id', 'cuenta_id', 'fecha_creacion', 'fecha_inicio', 'fecha_cierre'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function medidaPeso()
    {
        return $this->belongsTo(MedidaPeso::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function solicitud()
    {
        return $this->belongsTo(SolicitudPesaje::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }
}
