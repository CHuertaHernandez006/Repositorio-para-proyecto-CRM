<?php

// Cargar desde routes/webrtc.php, que ya es incluido por routes/web.php.
// No registrar ni reemplazar las rutas del teléfono SIP.js.
use App\Http\Controllers\TwilioVoicePilotoController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'rol:3'])
    ->prefix('telefono/twilio')
    ->name('telefono.twilio.')
    ->group(function () {
        Route::get('/prueba', [TwilioVoicePilotoController::class, 'index'])->name('prueba');
        Route::post('/token', [TwilioVoicePilotoController::class, 'token'])
            ->middleware('throttle:20,1')->name('token');
        Route::post('/preparar', [TwilioVoicePilotoController::class, 'preparar'])
            ->middleware('throttle:10,1')->name('preparar');
        Route::get('/intentos/{id}', [TwilioVoicePilotoController::class, 'consultar'])
            ->whereUuid('id')->middleware('throttle:60,1')->name('consultar');
    });

// Twilio envía POST firmados; sin sesión ni CSRF, validados criptográficamente en el controlador.
Route::prefix('telefono/twilio/webhook')
    ->name('telefono.twilio.webhook.')
    ->group(function () {
        Route::post('/voice', [TwilioVoicePilotoController::class, 'voice'])
            ->withoutMiddleware(ValidateCsrfToken::class)->name('voice');
        Route::post('/estado', [TwilioVoicePilotoController::class, 'estado'])
            ->withoutMiddleware(ValidateCsrfToken::class)->name('estado');
        Route::post('/fin', [TwilioVoicePilotoController::class, 'fin'])
            ->withoutMiddleware(ValidateCsrfToken::class)->name('fin');
    });
