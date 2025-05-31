<?php

namespace App\Models;

use App\Enums\TipoLicencia;
use App\Enums\EstadoGenericoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transportista extends Model
{
    use SoftDeletes;
    protected $fillable = ['cui', 'nombre_completo', 'fecha_nacimiento', 'tipo_licencia', 'fecha_vencimiento_licencia', 'agricultor_id', 'estado_id', 'disponible', 'foto', 'telefono'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'tipo_licencia' => TipoLicencia::class,
        'disponible' => 'boolean',
        'estado' => EstadoGenericoEnum::class
    ];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function getTipoLicenciaLabelAttribute()
    {
        return $this->tipo_licencia->label();
    }

    public function aprobar()
    {
        $this->estado = EstadoGenericoEnum::APROBADO;
        $this->save();
    }

    public function rechazar()
    {
        $this->estado = EstadoGenericoEnum::RECHAZADO;
        $this->save();
    }

    // al crear un registro establecer el estado como activo
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($transportista) {
            // Crear con estado pendiente utilizando el enum
            $transportista->estado = \App\Enums\EstadoGenericoEnum::PENDIENTE->value;

            // Assign agricultor_id from authenticated user
            if (auth()->check() && auth()->user()->agricultor->id) {
                $transportista->agricultor_id = auth()->user()->agricultor->id;
            }
        });
    }
}
