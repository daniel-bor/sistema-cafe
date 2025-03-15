<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedidaPeso extends Model
{
    use SoftDeletes;
    protected $table = 'medidas_peso';
    protected $fillable = ['nombre', 'simbolo'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
