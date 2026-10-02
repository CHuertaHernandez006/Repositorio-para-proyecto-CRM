<?php

use App\Http\Controllers\LlamadaSalienteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Llamadas salientes desde CRM
|--------------------------------------------------------------------------
|
| Este archivo se carga desde routes/web.php.
| El middleware rol:3 limita la acción a Operarios.
|
*/

Route::middleware(['auth', 'rol:3'])->group(function () {
    Route::post(
        '/clientes/{cliente}/llamar',
        [LlamadaSalienteController::class, 'store']
    )
        ->whereNumber('cliente')
        ->name('llamadas.salientes.store');
});
