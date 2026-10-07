<?php

use App\Http\Controllers\LlamadaActivaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WIDGET DE LLAMADA FLOTANTE
|--------------------------------------------------------------------------
|
| Solo el Operario consulta su llamada activa.
|
*/

Route::middleware([
    'auth',
    'rol:3',
])->group(function () {

    Route::get(
        '/llamadas/widget/activa',
        [
            LlamadaActivaController::class,
            'show',
        ]
    )
        ->name('llamadas.widget.activa');

});
