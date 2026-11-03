<?php

use App\Http\Controllers\LlamadaSalienteController;
use App\Http\Controllers\LlamadasController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LLAMADAS SALIENTES Y RESULTADO
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'rol:3',
])->group(function () {

    Route::post(
        '/clientes/{cliente}/llamar',
        [
            LlamadaSalienteController::class,
            'store',
        ]
    )
        ->whereNumber('cliente')
        ->name(
            'llamadas.salientes.store'
        );

    Route::patch(
        '/llamadas/{id_llamada}/resultado',
        [
            LlamadasController::class,
            'guardarResultado',
        ]
    )
        ->whereNumber('id_llamada')
        ->name(
            'llamadas.resultado.update'
        );

});


/*
|--------------------------------------------------------------------------
| AUDIO PROTEGIDO DE LLAMADA
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'rol:1,2,3',
])->group(function () {

    Route::get(
        '/llamadas/{id_llamada}/audio',
        [
            LlamadasController::class,
            'audio',
        ]
    )
        ->whereNumber('id_llamada')
        ->name(
            'llamadas.audio'
        );

});


/*
|--------------------------------------------------------------------------
| WEBHOOK ASTERISK - HANGUP
|--------------------------------------------------------------------------
*/

Route::post(
    '/api/asterisk/llamadas/finalizar',
    [
        LlamadaSalienteController::class,
        'finalizarLlamada',
    ]
)
    ->withoutMiddleware([
        ValidateCsrfToken::class,
    ])
    ->name(
        'asterisk.llamadas.finalizar'
    );
/*
|--------------------------------------------------------------------------
| ENDPOINT API PARA ASTERISK (Validación de Clientes)
|--------------------------------------------------------------------------
*/

Route::get('/asterisk/clientes/validar', function (\Illuminate\Http\Request $request) {

    // Validar token de seguridad
    $token = config('services.asterisk.validation_token');

    if (!$token || !hash_equals($token, (string) $request->header('X-Asterisk-Token', ''))) {
        return response('false', 401)
            ->header('Content-Type', 'text/plain');
    }

    // Obtener teléfono y eliminar caracteres que no sean números
    $telefono = preg_replace(
        '/\D+/',
        '',
        (string) $request->query('telefono', '')
    );

    // Comprobar que el número tenga al menos 10 dígitos
    if (strlen($telefono) < 10) {
        return response('false')
            ->header('Content-Type', 'text/plain');
    }

    // Utilizar los últimos 10 dígitos
    $telefono = substr($telefono, -10);

    // Buscar coincidencia en teléfono principal o secundario
    $existe = \App\Models\Cliente::query()
        ->where(function ($query) use ($telefono) {

            $query->whereRaw(
                "RIGHT(REGEXP_REPLACE(COALESCE(telefono_principal, ''), '[^0-9]', '', 'g'), 10) = ?",
                [$telefono]
            )->orWhereRaw(
                "RIGHT(REGEXP_REPLACE(COALESCE(telefono_secundario, ''), '[^0-9]', '', 'g'), 10) = ?",
                [$telefono]
            );

        })
        ->exists();

    // Responder exclusivamente true o false
    return response($existe ? 'true' : 'false')
        ->header('Content-Type', 'text/plain');

});