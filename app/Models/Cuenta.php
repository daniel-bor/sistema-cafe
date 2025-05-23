<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $fillable = ['no_cuenta', 'estado', 'agricultor_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function pesajes()
    {
        return $this->hasMany(Pesaje::class);
    }
}
