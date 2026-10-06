<?php

use App\Http\Controllers\ComercialController;
use App\Http\Middleware\AccesoComercial;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', AccesoComercial::class])->prefix('comercial')->name('comercial.')->group(function () {
    Route::get('/prospectos', [ComercialController::class, 'index'])->name('prospectos.index');
    Route::get('/agenda', [ComercialController::class, 'agenda'])->name('agenda');
    Route::get('/prospectos/{id}', [ComercialController::class, 'show'])->whereNumber('id')->name('prospectos.show');
    Route::post('/prospectos/{id}/seguimientos', [ComercialController::class, 'seguimiento'])->whereNumber('id')->name('seguimientos.store');

    Route::middleware(AccesoComercial::class.':admin')->group(function () {
        Route::post('/incorporar', [ComercialController::class, 'incorporar'])->name('incorporar');
        Route::put('/prospectos/{id}/asignacion', [ComercialController::class, 'asignar'])->whereNumber('id')->name('asignar');
        Route::post('/prospectos/{id}/contrato', [ComercialController::class, 'contratar'])->whereNumber('id')->name('contratar');
        Route::get('/equipo', [ComercialController::class, 'equipo'])->name('equipo');
        Route::post('/equipo', [ComercialController::class, 'crearAsesor'])->name('equipo.store');
        Route::put('/equipo/{id}/estado', [ComercialController::class, 'estadoAsesor'])->whereNumber('id')->name('equipo.estado');
    });
});
