<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JWTAuthController;
use App\Http\Controllers\TransporteController;
use App\Http\Controllers\TransportistaController;
use App\Http\Controllers\PesajeController;
use App\Http\Controllers\ParcialidadController;
use App\Http\Middleware\JwtMiddleware;

Route::post('register', [JWTAuthController::class, 'register']);
Route::post('login', [JWTAuthController::class, 'login']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('user', [JWTAuthController::class, 'getUser']);
    Route::post('logout', [JWTAuthController::class, 'logout']);

    // Rutas para el recurso Transporte
    Route::apiResource('transportes', TransporteController::class);

    // Rutas adicionales para acciones específicas de transportes
    Route::post('transportes/{id}/aprobar', [TransporteController::class, 'aprobar']);
    Route::post('transportes/{id}/rechazar', [TransporteController::class, 'rechazar']);
    Route::get('transportes-disponibles', [TransporteController::class, 'disponibles']);

    // Rutas para el recurso Transportista
    Route::apiResource('transportistas', TransportistaController::class);

    // Rutas adicionales para acciones específicas de transportistas
    Route::post('transportistas/{id}/aprobar', [TransportistaController::class, 'aprobar']);
    Route::post('transportistas/{id}/rechazar', [TransportistaController::class, 'rechazar']);
    Route::post('transportistas/{id}/cambiar-disponibilidad', [TransportistaController::class, 'cambiarDisponibilidad']);
    Route::get('transportistas-disponibles', [TransportistaController::class, 'disponibles']);

    // Rutas para el recurso Pesaje
    Route::apiResource('pesajes', PesajeController::class);

    // Rutas adicionales para acciones específicas de pesajes
    Route::post('pesajes/{id}/enviar-solicitud', [PesajeController::class, 'enviarSolicitud']);
    Route::post('pesajes/{id}/aceptar-solicitud', [PesajeController::class, 'aceptarSolicitud']);
    Route::post('pesajes/{id}/rechazar-solicitud', [PesajeController::class, 'rechazarSolicitud']);
    Route::post('pesajes/{id}/cerrar-cuenta', [PesajeController::class, 'cerrarCuenta']);

    // Rutas para el recurso Parcialidad
    Route::apiResource('parcialidades', ParcialidadController::class);

    // Rutas adicionales para acciones específicas de parcialidades
    Route::post('parcialidades/{id}/enviar', [ParcialidadController::class, 'enviar']);
    Route::post('parcialidades/{id}/recibir', [ParcialidadController::class, 'recibir']);
    Route::post('parcialidades/{id}/pesar', [ParcialidadController::class, 'pesar']);
    Route::post('parcialidades/{id}/finalizar', [ParcialidadController::class, 'finalizar']);

    // Rutas para obtener catálogos
    Route::get('transportistas/catalogos/tipos-licencia', [TransportistaController::class, 'tiposLicencia']);
    Route::get('transportistas/catalogos/estados', [TransportistaController::class, 'estados']);
    Route::get('pesajes/catalogos/general', [PesajeController::class, 'catalogos']);
    Route::get('parcialidades/catalogos/general', [ParcialidadController::class, 'catalogos']);
});
