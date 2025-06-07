<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agricultor extends Model
{
    use SoftDeletes;
    protected $table = 'agricultores';
    protected $fillable = ['nit', 'nombre', 'apellido', 'observaciones', 'user_id', 'telefono', 'direccion'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transportes()
    {
        return $this->hasMany(Transporte::class);
    }

    public function transportistas()
    {
        return $this->hasMany(Transportista::class);
    }

    public function pesajes()
    {
        return $this->hasMany(Pesaje::class);
    }

    public function cuentas()
    {
        return $this->hasMany(Cuenta::class);
    }

    // Funciones auxiliares

    public function getNombreCompletoAttribute()
    {
        return $this->attributes['nombre'] . ' ' . $this->attributes['apellido'];
    }
}
