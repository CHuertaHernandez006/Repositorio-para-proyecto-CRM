<?php

use App\Http\Controllers\LlamadaSalienteController;
use App\Http\Controllers\LlamadasController;
use Illuminate\Support\Facades\Route;



Route::middleware([
    'auth',
    'rol:3',
])->group(function () {

    Route::post('/clientes/{cliente}/llamar', [
        LlamadaSalienteController::class,
        'store',
    ])
        ->whereNumber('cliente')
        ->name('llamadas.salientes.store');

    Route::patch('/llamadas/{id_llamada}/resultado', [
        LlamadasController::class,
        'guardarResultado',
    ])
        ->whereNumber('id_llamada')
        ->name('llamadas.resultado.update');

});
