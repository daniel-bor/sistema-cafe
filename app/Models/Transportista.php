<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transportista extends Model
{
    protected $fillable = ['cui', 'nombre_completo', 'fecha_nacimiento', 'tipo_licencia', 'fecha_vencimiento_licencia', 'agricultor_id', 'estado_id', 'disponible'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}
