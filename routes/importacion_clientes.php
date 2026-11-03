<?php

use App\Http\Controllers\ImportacionClientesController;
use Illuminate\Support\Facades\Route;

// Además de auth, cada acción comprueba rol, actividad, empresa y propietario del lote.
Route::middleware('auth')->prefix('clientes/importar')->name('clientes.importacion.')->group(function () {
    Route::get('/', [ImportacionClientesController::class, 'index'])->name('index');
    Route::get('/plantilla/{formato}', [ImportacionClientesController::class, 'plantilla'])
        ->where('formato', 'csv|xlsx')->name('plantilla');
    Route::post('/previsualizar', [ImportacionClientesController::class, 'previsualizar'])->name('previsualizar');
    Route::get('/lotes/{importacion}', [ImportacionClientesController::class, 'mostrar'])->whereUuid('importacion')->name('mostrar');
    Route::post('/lotes/{importacion}/confirmar', [ImportacionClientesController::class, 'confirmar'])->whereUuid('importacion')->name('confirmar');
    Route::delete('/lotes/{importacion}', [ImportacionClientesController::class, 'cancelar'])->whereUuid('importacion')->name('cancelar');
    Route::get('/lotes/{importacion}/reporte', [ImportacionClientesController::class, 'reporte'])->whereUuid('importacion')->name('reporte');
});
