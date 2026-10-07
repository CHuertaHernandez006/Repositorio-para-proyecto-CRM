<?php

use App\Http\Controllers\LlamadaSalienteController;
use App\Http\Controllers\LlamadasController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LLAMADAS SALIENTES / RESULTADO DEL OPERARIO
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
| WEBHOOK ASTERISK - FIN DE LLAMADA
|--------------------------------------------------------------------------
|
| Lo invoca Asterisk al colgar.
|
| POST /api/asterisk/llamadas/finalizar
|
| Header:
| X-Asterisk-Token: <ASTERISK_WEBHOOK_TOKEN>
|
| Body:
| action_id=<CRM_ACTION_ID>
|
| También acepta:
| record_filename=<RECORD_FILENAME>
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
