<?php

namespace App\Models;

use App\Enums\EstadoPesaje;
use App\Enums\EstadoParcialidad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pesaje extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cantidad_total',
        'tolerancia',
        'precio_unitario',
        'fecha_inicio',
        'fecha_cierre',
        'estado',
        'cuenta_id',
        'agricultor_id',
        'medida_peso_id',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'estado' => EstadoPesaje::class,
    ];

    public function medidaPeso()
    {
        return $this->belongsTo(MedidaPeso::class);
    }

    public function solicitud()
    {
        return $this->belongsTo(Pesaje::class)->where('estado_id', 1);
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function parcialidades()
    {
        return $this->hasMany(Parcialidad::class);
    }

    // Funciones auxiliares
    public function getCantidadParcialidadesAttribute()
    {
        return $this->parcialidades()
            ->where('estado', '!=', EstadoParcialidad::RECHAZADO)
            ->count();
    }

    public function getCantidadEntregasAttribute()
    {
        return $this->parcialidades()
            ->where('estado', EstadoParcialidad::PESADO)
            ->count();
    }

    public function getTotalParcialidadesAttribute()
    {
        return $this->parcialidades()
            ->where('estado', '!=', EstadoParcialidad::RECHAZADO)
            ->sum('peso');
    }

    public function getFechaUltimoEnvioAttribute()
    {
        return $this->parcialidades()
            ->where('estado', EstadoParcialidad::PESADO)
            ->orderBy('fecha_envio', 'desc')
            ->first()?->fecha_envio ?? null;
    }

    public function getNoCuentaAttribute()
    {
        return $this->cuenta?->no_cuenta ?? 'No asignada';
    }

    // Obtener el porcentaje de diferencia entre el peso total y la suma de las parcialidades recibidas
    public function getPorcentajeDiferenciaAttribute()
    {
        $pesoTotal = $this->cantidad_total;
        $pesoParcialidades = $this->parcialidades()->sum('peso_bascula');

        if ($pesoTotal == 0) {
            return 0; // Evitar división por cero
        }

        return (($pesoTotal - $pesoParcialidades) / $pesoTotal) * 100;
    }


    // Al crear un registro establecer el estado como nuevo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pesaje) {
            // Asignar el estado como pendiente
            $pesaje->estado = EstadoPesaje::NUEVO;
        });
    }
}
