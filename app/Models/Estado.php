<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estado extends Model
{
    use SoftDeletes;
    protected $table = 'estados';
    protected $fillable = ['nombre', 'contexto'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

}
