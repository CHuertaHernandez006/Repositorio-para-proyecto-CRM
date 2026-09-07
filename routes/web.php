<?php

use App\Http\Controllers\PruebaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CampanasController;
use App\Http\Controllers\LlamadasController;

// Ruta de clientes con el nombre que espera app.blade.php ('clientes.index')
Route::get('/llamadas', [LlamadasController::class, 'index'])->name('llamadas.index');

// Ruta de clientes con el nombre que espera app.blade.php ('clientes.index')
Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');

// Ruta de clientes con el nombre que espera app.blade.php ('clientes.index')
Route::get('/campañas', [CampanasController::class, 'index'])->name('campanas.index');

// Mostrar el formulario de login en la raíz
Route::get('/', function () {
    return view('welcome');
})->name('login');

// Procesar la verificación del formulario
Route::post('/login-verificar', [PruebaController::class, 'verificarAcceso'])->name('login.verificar');

// Dashboard limpio utilizando sesión
Route::get('/dashboard', [PruebaController::class, 'mostrarExito'])->name('dashboard');

// Cerrar sesión
Route::post('/logout', [PruebaController::class, 'logout'])->name('logout');