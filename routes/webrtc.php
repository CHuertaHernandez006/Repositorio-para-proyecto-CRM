<?php

use App\Http\Controllers\TelefonoWebRtcController;
use Illuminate\Support\Facades\Route;

// Ya se incluye desde routes/web.php; NO duplicar require.
Route::middleware(['auth', 'rol:3'])
    ->prefix('telefono')
    ->name('telefono.')
    ->group(function () {
        Route::get('/', [TelefonoWebRtcController::class, 'index'])->name('index');
        Route::get('/clientes-disponibles', [TelefonoWebRtcController::class, 'clientesDisponibles'])->name('clientes-disponibles');
        Route::post('/validar-destino', [TelefonoWebRtcController::class, 'validarDestino'])->name('validar-destino');
        Route::get('/buscar-entrante', [TelefonoWebRtcController::class, 'buscarEntrante'])->name('buscar-entrante');
        Route::post('/credenciales', [TelefonoWebRtcController::class, 'credenciales'])
            ->middleware('throttle:5,1')
            ->name('credenciales');
    });
