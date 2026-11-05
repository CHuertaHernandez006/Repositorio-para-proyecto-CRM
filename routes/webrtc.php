<?php

use App\Http\Controllers\TelefonoWebRtcController;
use Illuminate\Support\Facades\Route;

// Añadir al final de routes/web.php: require __DIR__ . '/webrtc.php';
Route::middleware(['auth', 'rol:3'])
    ->prefix('telefono')
    ->name('telefono.')
    ->group(function () {
        Route::get('/', [TelefonoWebRtcController::class, 'index'])->name('index');
        Route::post('/validar-destino', [TelefonoWebRtcController::class, 'validarDestino'])->name('validar-destino');
        Route::get('/buscar-entrante', [TelefonoWebRtcController::class, 'buscarEntrante'])->name('buscar-entrante');
    });
