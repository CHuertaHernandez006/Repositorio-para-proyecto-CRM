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


/*
|--------------------------------------------------------------------------
| Inicio de sesión
|--------------------------------------------------------------------------
*/

// Mostrar el formulario de inicio de sesión.
Route::get('/', function () {
    return view('welcome');
})->name('login');

// Verificar las credenciales.
Route::post('/login-verificar', [
    AuthController::class,
    'verificarAcceso',
])->name('login.verificar');

// Cerrar sesión.
Route::post('/logout', [
    AuthController::class,
    'logout',
])->name('logout');


/*
|--------------------------------------------------------------------------
| Registro
|--------------------------------------------------------------------------
*/

// Mostrar la selección de registro.
Route::get('/registro_seleccion', [
    RegistroSeleccionController::class,
    'registroseleccion',
])->name('registro_seleccion');

// Mostrar el formulario de registro de operador.
Route::get('/registro-operador', [
    RegistroOperadorController::class,
    'create',
])->name('seleccion.registro_operador');


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [
    AuthController::class,
    'mostrarExito',
])->name('dashboard');


/*
|--------------------------------------------------------------------------
| Clientes
| Acceso para roles 1 y 2
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:1,2'])->group(function () {

    // Listar clientes.
    Route::get('/clientes', [
        ClienteController::class,
        'index',
    ])->name('clientes.index');

    // Mostrar formulario de creación.
    Route::get('/clientes/crear', [
        ClienteController::class,
        'create',
    ])->name('clientes.create');

    // Guardar nuevo cliente.
    Route::post('/clientes', [
        ClienteController::class,
        'store',
    ])->name('clientes.store');

    // Mostrar formulario de edición.
    Route::get('/clientes/{id_cliente}/editar', [
        ClienteController::class,
        'edit',
    ])->name('clientes.edit');

    // Actualizar cliente.
    Route::put('/clientes/{id_cliente}', [
        ClienteController::class,
        'update',
    ])->name('clientes.update');

    // Eliminar cliente.
    Route::delete('/clientes/{id_cliente}', [
        ClienteController::class,
        'destroy',
    ])->name('clientes.destroy');

});


/*
|--------------------------------------------------------------------------
| Campañas
|--------------------------------------------------------------------------
*/

Route::get('/campañas', [
    CampanasController::class,
    'index',
])->name('campanas.index');


/*
|--------------------------------------------------------------------------
| Llamadas
|--------------------------------------------------------------------------
*/

Route::get('/llamadas', [
    LlamadasController::class,
    'index',
])->name('llamadas.index');


/*
|--------------------------------------------------------------------------
| Empresas
| Acceso exclusivo para Súper Admin (Rol 1)
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:1'])->group(function () {

    Route::get('/empresas', [
        EmpresaController::class,
        'index',
    ])->name('empresas.index');

    Route::get('/empresas/crear', [
        EmpresaController::class,
        'create',
    ])->name('empresas.create');

    Route::post('/empresas', [
        EmpresaController::class,
        'store',
    ])->name('empresas.store');

    Route::get('/empresas/{id_empresa}/editar', [
        EmpresaController::class,
        'edit',
    ])->name('empresas.edit');

    Route::put('/empresas/{id_empresa}', [
        EmpresaController::class,
        'update',
    ])->name('empresas.update');

    Route::delete('/empresas/{id_empresa}', [
        EmpresaController::class,
        'destroy',
    ])->name('empresas.destroy');

});


/*
|--------------------------------------------------------------------------
| Operarios
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/operarios', [
        OperarioController::class,
        'index',
    ])->name('operarios.index');

    Route::get('/operarios/create', [
        OperarioController::class,
        'create',
    ])->name('operarios.create');

    Route::post('/operarios', [
        OperarioController::class,
        'store',
    ])->name('operarios.store');

    Route::get('/operarios/{operario}', [
        OperarioController::class,
        'show',
    ])->name('operarios.show');

    Route::get('/operarios/{operario}/edit', [
        OperarioController::class,
        'edit',
    ])->name('operarios.edit');

    Route::put('/operarios/{operario}', [
        OperarioController::class,
        'update',
    ])->name('operarios.update');

    Route::patch('/operarios/{operario}/estado', [
        OperarioController::class,
        'toggleEstado',
    ])->name('operarios.toggleEstado');

    Route::delete('/operarios/{operario}', [
        OperarioController::class,
        'destroy',
    ])->name('operarios.destroy');

});


/*
|--------------------------------------------------------------------------
| Citas
|--------------------------------------------------------------------------
|
| Admin Cliente (Rol 2):
| - Consulta todas las citas de los operarios de su empresa.
| - Puede crear, editar y eliminar citas.
|
| Operario (Rol 3):
| - Consulta únicamente sus propias citas.
|
*/


// ============================================================
// CONSULTA DE CITAS
// Roles 2 y 3
// ============================================================

Route::middleware(['rol:2,3'])->group(function () {

    // Listar citas.
    Route::get('/citas', [
        CitaController::class,
        'index',
    ])->name('citas.index');

    // Crear cita.
    // IMPORTANTE: debe estar antes de /citas/{id_cita}.
    Route::get('/citas/crear', [
        CitaController::class,
        'create',
    ])->name('citas.create');

    // Mostrar una cita específica.
    Route::get('/citas/{id_cita}', [
        CitaController::class,
        'show',
    ])->name('citas.show');

});


// ============================================================
// ADMINISTRACIÓN DE CITAS
// Solo Admin Cliente - Rol 2
// ============================================================

Route::middleware(['rol:2'])->group(function () {

    // Guardar nueva cita.
    Route::post('/citas', [
        CitaController::class,
        'store',
    ])->name('citas.store');

    // Mostrar formulario de edición.
    Route::get('/citas/{id_cita}/editar', [
        CitaController::class,
        'edit',
    ])->name('citas.edit');

    // Actualizar cita.
    Route::put('/citas/{id_cita}', [
        CitaController::class,
        'update',
    ])->name('citas.update');

    // Eliminar cita.
    Route::delete('/citas/{id_cita}', [
        CitaController::class,
        'destroy',
    ])->name('citas.destroy');

});