<?php

namespace App\Services;

use App\Models\Cuenta;
use App\Models\Pesaje;
use Illuminate\Database\Eloquent\Model;

class CuentaService
{
    /**
     * Crea una nueva cuenta o recupera una existente para un agricultor.
     *
     * @param int $agricultorId El ID del agricultor
     * @param int $pesajeId El ID del pesaje que origina la solicitud (opcional)
     * @return Cuenta La cuenta creada o recuperada
     */
    public function obtenerOCrearCuentaParaAgricultor(int $agricultorId, ?int $pesajeId = null): Cuenta
    {
        // Buscar si ya existe una cuenta para este agricultor
        $cuenta = Cuenta::where('agricultor_id', $agricultorId)->first();

        // Si no existe, crear una nueva cuenta
        if (!$cuenta) {
            $cuenta = $this->crearCuenta($agricultorId, $pesajeId);
        }

        return $cuenta;
    }

    /**
     * Crea una nueva cuenta para un agricultor.
     *
     * @param int $agricultorId El ID del agricultor
     * @param int|null $solicitudId El ID del pesaje que origina la solicitud (opcional)
     * @return Cuenta La cuenta creada
     */
    public function crearCuenta(int $agricultorId, ?int $solicitudId = null): Cuenta
    {
        // Generar un número de cuenta único
        $noCuenta = $this->generarNumeroCuenta($agricultorId);

        // Estado por defecto para una cuenta nueva (1 = Activa normalmente)
        $estadoId = 1;

        return Cuenta::create([
            'no_cuenta' => $noCuenta,
            'agricultor_id' => $agricultorId,
            'estado_id' => $estadoId,
            'solicitud_id' => $solicitudId,
        ]);
    }

    /**
     * Asigna una cuenta a un pesaje.
     *
     * @param Pesaje $pesaje El pesaje al que se asignará la cuenta
     * @return Pesaje El pesaje actualizado
     */
    public function asignarCuentaAPesaje(Pesaje $pesaje): Pesaje
    {
        // Verificar que el pesaje tenga un agricultor asignado
        if (!$pesaje->agricultor_id) {
            throw new \InvalidArgumentException('El pesaje no tiene un agricultor asignado');
        }

        // Obtener o crear cuenta para el agricultor
        $cuenta = $this->obtenerOCrearCuentaParaAgricultor($pesaje->agricultor_id, $pesaje->id);

        // Asignar la cuenta al pesaje
        $pesaje->cuenta_id = $cuenta->id;
        $pesaje->save();

        return $pesaje;
    }

    /**
     * Genera un número de cuenta único para un agricultor.
     *
     * @param int $agricultorId El ID del agricultor
     * @return string El número de cuenta generado
     */
    private function generarNumeroCuenta(int $agricultorId): string
    {
        // Prefijo para la cuenta
        $prefijo = 'CTA';

        // Año actual
        $anio = date('Y');

        // Generar un número secuencial basado en la cantidad de cuentas existentes + 1
        $secuencial = Cuenta::count() + 1;

        // Formatear el secuencial como un número de 6 dígitos
        $secuencial = str_pad($secuencial, 6, '0', STR_PAD_LEFT);

        // Combinar todo para formar un número de cuenta único
        return "{$prefijo}-{$anio}-{$agricultorId}-{$secuencial}";
    }
}
