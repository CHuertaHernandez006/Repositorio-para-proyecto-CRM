<?php

use App\Http\Controllers\PruebaController;
use Illuminate\Support\Facades\Route;

// Mostrar el formulario de login en la raíz
Route::get('/', function () {
    return view('welcome');
})->name('login');

// Procesar la verificación del formulario
Route::post('/login-verificar', [PruebaController::class, 'verificarAcceso'])->name('login.verificar');

// Vista de éxito tras validar el registro en PostgreSQL
Route::get('/exito/{id}', [PruebaController::class, 'mostrarExito'])->name('vista.exito');