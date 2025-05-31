<?php

use Illuminate\Support\Facades\Route;
use App\Models\Parcialidad;

// Route::get('/', function () {
//     return view('welcome');
// });

// Ruta para mostrar el detalle de parcialidad al escanear QR
Route::get('/parcialidad/{id}/qr', function ($id) {
    $parcialidad = Parcialidad::with([
        'pesaje.cuenta',
        'pesaje.medidaPeso',
        'transportista',
        'transporte'
    ])->findOrFail($id);

    return view('parcialidad.detalle', compact('parcialidad'));
})->name('parcialidad.qr');
