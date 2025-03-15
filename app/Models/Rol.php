<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rol extends Model
{
    use SoftDeletes;
    protected $table = 'rols';
    protected $fillable = ['nombre'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
