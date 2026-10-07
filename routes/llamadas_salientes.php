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
