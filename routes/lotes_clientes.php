<?php

use App\Http\Controllers\LoteClientesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('asignaciones-clientes/importaciones')->name('clientes.lotes.')->group(function () {
    Route::get('/', [LoteClientesController::class, 'index'])->name('index');
    Route::get('/{lote}', [LoteClientesController::class, 'mostrar'])->whereUuid('lote')->name('mostrar');
    Route::put('/{lote}/asignar', [LoteClientesController::class, 'asignar'])->whereUuid('lote')->name('asignar');
    Route::put('/{lote}/clientes/{cliente}', [LoteClientesController::class, 'individual'])
        ->whereUuid('lote')->whereNumber('cliente')->name('individual');
});
