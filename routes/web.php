<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CampanasController;
use App\Http\Controllers\LlamadasController;
use App\Http\Controllers\RegistroSeleccionController;
use App\Http\Controllers\RegistroOperadorController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\OperarioController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\CalendarioController;

/*
|--------------------------------------------------------------------------
| Autenticación y Registro Público
|--------------------------------------------------------------------------
*/

// Mostrar el formulario de inicio de sesión
Route::get('/', function () {
    return view('welcome');
})->name('login');

// Verificar credenciales
Route::post('/login-verificar', [AuthController::class, 'verificarAcceso'])->name('login.verificar');

// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Selección y registro público
Route::get('/registro_seleccion', [RegistroSeleccionController::class, 'registroseleccion'])->name('registro_seleccion');
Route::get('/registro-operador', [RegistroOperadorController::class, 'create'])->name('seleccion.registro_operador');


/*
|--------------------------------------------------------------------------
| Rutas Autenticadas General (`auth`)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AuthController::class, 'mostrarExito'])->name('dashboard');

    // Módulos Generales
    Route::get('/campañas', [CampanasController::class, 'index'])->name('campanas.index');
    Route::get('/llamadas', [LlamadasController::class, 'index'])->name('llamadas.index');

    // Operarios (CRUD)
    Route::resource('operarios', OperarioController::class);
    Route::patch('/operarios/{operario}/estado', [OperarioController::class, 'toggleEstado'])->name('operarios.toggleEstado');

    /*
    |--------------------------------------------------------------------------
    | Super Admin (Rol 1)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:1'])->group(function () {
        Route::resource('empresas', EmpresaController::class)->except(['show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Clientes (Roles 1 y 2)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:1,2'])->group(function () {
        Route::resource('clientes', ClienteController::class)->except(['show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Citas y Administración (Roles 2 y 3)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:2,3'])->group(function () {
        Route::get('/citas', [CitaController::class, 'index'])->name('citas.index');
        Route::get('/citas/crear', [CitaController::class, 'create'])->name('citas.create');
        Route::get('/citas/{id_cita}', [CitaController::class, 'show'])->name('citas.show');
    });

    // Gestión exclusiva de citas por Admin Cliente (Rol 2)
    Route::middleware(['rol:2'])->group(function () {
        Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
        Route::get('/citas/{id_cita}/editar', [CitaController::class, 'edit'])->name('citas.edit');
        Route::put('/citas/{id_cita}', [CitaController::class, 'update'])->name('citas.update');
        Route::delete('/citas/{id_cita}', [CitaController::class, 'destroy'])->name('citas.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Calendario Operario (Exclusivo Rol 3)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['rol:3'])->group(function () {
        Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index');
        Route::get('/calendario/crear', [CalendarioController::class, 'create'])->name('calendario.create');
        Route::post('/calendario', [CalendarioController::class, 'store'])->name('calendario.store');
        Route::get('/calendario/{id_cita}/editar', [CalendarioController::class, 'edit'])->name('calendario.edit');
        Route::put('/calendario/{id_cita}', [CalendarioController::class, 'update'])->name('calendario.update');
        Route::delete('/calendario/{id_cita}', [CalendarioController::class, 'destroy'])->name('calendario.destroy');
    });

});