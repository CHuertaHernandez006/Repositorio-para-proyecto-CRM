
<?php

use App\Http\Controllers\TelefonoWebRtcController;
use App\Http\Controllers\ExtensionAsteriskController;
use Illuminate\Support\Facades\Route;

// Ya se incluye desde routes/web.php.
// NO duplicar require.

Route::middleware(['auth', 'rol:3'])
    ->prefix('telefono')
    ->name('telefono.')
    ->group(function () {

        Route::get('/', [
            TelefonoWebRtcController::class,
            'index'
        ])->name('index');

        Route::get('/clientes-disponibles', [
            TelefonoWebRtcController::class,
            'clientesDisponibles'
        ])->name('clientes-disponibles');

        Route::post('/validar-destino', [
            TelefonoWebRtcController::class,
            'validarDestino'
        ])->name('validar-destino');

        Route::get('/buscar-entrante', [
            TelefonoWebRtcController::class,
            'buscarEntrante'
        ])->name('buscar-entrante');

        // Credenciales SIP: límite individual por operario.
        Route::post('/credenciales', [
            TelefonoWebRtcController::class,
            'credenciales'
        ])
            ->middleware('throttle:webrtc-credentials')
            ->name('credenciales');
    });

// Gestión administrativa de WebRTC.
Route::middleware(['auth', 'rol:1,2', 'throttle:15,1'])
    ->prefix('admin/extensiones-sip')
    ->name('extensiones-sip.')
    ->group(function () {

        Route::post('/renovar-web', [
            ExtensionAsteriskController::class,
            'renovar'
        ])->name('renovar-web');

        Route::post('/reintentar-web', [
            ExtensionAsteriskController::class,
            'reintentar'
        ])->name('reintentar-web');
    });
