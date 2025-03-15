<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boleta extends Model
{
    protected $table = 'boletas';
    protected $fillable = ['parcialidad_id', 'usuario_id', 'concepto', 'monto', 'numero_documento', 'referencia', 'observaciones'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function parcialidad()
    {
        return $this->belongsTo(Parcialidad::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
