<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacoras';
    protected $fillable = ['entidad', 'valor_anterior', 'valor_nuevo', 'accion', 'ip', 'usuario_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
