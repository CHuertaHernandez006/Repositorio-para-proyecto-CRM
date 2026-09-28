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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CitaController;


/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/

// Mostrar formulario de inicio de sesión
Route::get('/', function () {
    return view('welcome');
})->name('login');


// Verificar credenciales
Route::post('/login-verificar', [
    AuthController::class,
    'verificarAcceso',
])->name('login.verificar');


// Cerrar sesión
Route::post('/logout', [
    AuthController::class,
    'logout',
])->name('logout');


/*
|--------------------------------------------------------------------------
| SELECCIÓN Y REGISTRO
|--------------------------------------------------------------------------
*/

Route::get('/registro_seleccion', [
    RegistroSeleccionController::class,
    'registroseleccion',
])->name('registro_seleccion');


Route::get('/registro-operador', [
    RegistroOperadorController::class,
    'create',
])->name('seleccion.registro_operador');


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [
    DashboardController::class,
    'index',
])
    ->middleware(['auth'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| CLIENTES
|--------------------------------------------------------------------------
| Acceso exclusivo para Admin Cliente (Rol 2)
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:2'])->group(function () {

    Route::get('/clientes', [
        ClienteController::class,
        'index',
    ])->name('clientes.index');


    Route::get('/clientes/crear', [
        ClienteController::class,
        'create',
    ])->name('clientes.create');


    Route::post('/clientes', [
        ClienteController::class,
        'store',
    ])->name('clientes.store');


    Route::get('/clientes/{id_cliente}/editar', [
        ClienteController::class,
        'edit',
    ])
        ->whereNumber('id_cliente')
        ->name('clientes.edit');


    Route::put('/clientes/{id_cliente}', [
        ClienteController::class,
        'update',
    ])
        ->whereNumber('id_cliente')
        ->name('clientes.update');


    Route::delete('/clientes/{id_cliente}', [
        ClienteController::class,
        'destroy',
    ])
        ->whereNumber('id_cliente')
        ->name('clientes.destroy');

});


/*
|--------------------------------------------------------------------------
| CAMPAÑAS
|--------------------------------------------------------------------------
| Admin Cliente y Operario pueden consultar campañas.
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:2,3'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    Route::get('/campanas', [
        CampanasController::class,
        'index',
    ])->name('campanas.index');

});


/*
|--------------------------------------------------------------------------
| ADMINISTRACIÓN DE CAMPAÑAS
|--------------------------------------------------------------------------
| Exclusivo para Admin Cliente (Rol 2)
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:2'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CREAR CAMPAÑA
    |--------------------------------------------------------------------------
    */

    Route::get('/campanas/crear', [
        CampanasController::class,
        'create',
    ])->name('campanas.create');


    Route::post('/campanas', [
        CampanasController::class,
        'store',
    ])->name('campanas.store');


    /*
    |--------------------------------------------------------------------------
    | CLIENTES DE CAMPAÑA
    |--------------------------------------------------------------------------
    |
    | GET:
    | Muestra los clientes disponibles y los que ya están asignados.
    |
    | POST:
    | Agrega uno o varios clientes a una campaña.
    |
    | DELETE:
    | Retira un cliente de una campaña.
    |
    */

    Route::get('/campanas/{campana}/clientes', [
        CampanasController::class,
        'clientes',
    ])
        ->whereNumber('campana')
        ->name('campanas.clientes');


    Route::post('/campanas/{campana}/clientes', [
        CampanasController::class,
        'asignarClientes',
    ])
        ->whereNumber('campana')
        ->name('campanas.clientes.asignar');


    Route::delete(
        '/campanas/{campana}/clientes/{cliente}',
        [
            CampanasController::class,
            'quitarCliente',
        ]
    )
        ->whereNumber('campana')
        ->whereNumber('cliente')
        ->name('campanas.clientes.quitar');


    /*
    |--------------------------------------------------------------------------
    | EDITAR CAMPAÑA
    |--------------------------------------------------------------------------
    */

    Route::get('/campanas/{campana}/editar', [
        CampanasController::class,
        'edit',
    ])
        ->whereNumber('campana')
        ->name('campanas.edit');


    Route::put('/campanas/{campana}', [
        CampanasController::class,
        'update',
    ])
        ->whereNumber('campana')
        ->name('campanas.update');


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR CAMPAÑA
    |--------------------------------------------------------------------------
    */

    Route::delete('/campanas/{campana}', [
        CampanasController::class,
        'destroy',
    ])
        ->whereNumber('campana')
        ->name('campanas.destroy');

});


/*
|--------------------------------------------------------------------------
| VER CAMPAÑA
|--------------------------------------------------------------------------
| Admin Cliente y Operario
|--------------------------------------------------------------------------
|
| Esta ruta va después de:
|
| /campanas/crear
| /campanas/{campana}/clientes
| /campanas/{campana}/editar
|
| Así evitamos conflictos con /campanas/{campana}.
|
*/

Route::middleware(['rol:2,3'])->group(function () {

    Route::get('/campanas/{campana}', [
        CampanasController::class,
        'show',
    ])
        ->whereNumber('campana')
        ->name('campanas.show');

});


/*
|--------------------------------------------------------------------------
| LLAMADAS
|--------------------------------------------------------------------------
| Acceso para Admin Cliente y Operario (Roles 2 y 3)
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:2,3'])->group(function () {

    Route::get('/llamadas', [
        LlamadasController::class,
        'index',
    ])->name('llamadas.index');

});


/*
|--------------------------------------------------------------------------
| EMPRESAS
|--------------------------------------------------------------------------
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
    ])
        ->whereNumber('id_empresa')
        ->name('empresas.edit');


    Route::put('/empresas/{id_empresa}', [
        EmpresaController::class,
        'update',
    ])
        ->whereNumber('id_empresa')
        ->name('empresas.update');


    Route::delete('/empresas/{id_empresa}', [
        EmpresaController::class,
        'destroy',
    ])
        ->whereNumber('id_empresa')
        ->name('empresas.destroy');


    /*
    |--------------------------------------------------------------------------
    | ADMINISTRADOR DE EMPRESA
    |--------------------------------------------------------------------------
    */

    Route::get('/empresas/{id_empresa}/admin/editar', [
        EmpresaController::class,
        'editAdmin',
    ])
        ->whereNumber('id_empresa')
        ->name('empresas.admin.edit');


    Route::put('/empresas/{id_empresa}/admin', [
        EmpresaController::class,
        'updateAdmin',
    ])
        ->whereNumber('id_empresa')
        ->name('empresas.admin.update');

});


/*
|--------------------------------------------------------------------------
| OPERARIOS
|--------------------------------------------------------------------------
| Acceso exclusivo para Admin Cliente (Rol 2)
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:2'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    Route::get('/operarios', [
        OperarioController::class,
        'index',
    ])->name('operarios.index');


    /*
    |--------------------------------------------------------------------------
    | OBJETIVOS
    |--------------------------------------------------------------------------
    |
    | Esta ruta debe estar antes de /operarios/{operario}.
    |
    */

    Route::get('/operarios/objetivos', [
        OperarioController::class,
        'objetivos',
    ])->name('operarios.objetivos');


    /*
    |--------------------------------------------------------------------------
    | CREAR
    |--------------------------------------------------------------------------
    */

    Route::get('/operarios/create', [
        OperarioController::class,
        'create',
    ])->name('operarios.create');


    Route::post('/operarios', [
        OperarioController::class,
        'store',
    ])->name('operarios.store');


    /*
    |--------------------------------------------------------------------------
    | VER
    |--------------------------------------------------------------------------
    */

    Route::get('/operarios/{operario}', [
        OperarioController::class,
        'show',
    ])
        ->whereNumber('operario')
        ->name('operarios.show');


    /*
    |--------------------------------------------------------------------------
    | EDITAR
    |--------------------------------------------------------------------------
    */

    Route::get('/operarios/{operario}/edit', [
        OperarioController::class,
        'edit',
    ])
        ->whereNumber('operario')
        ->name('operarios.edit');


    Route::put('/operarios/{operario}', [
        OperarioController::class,
        'update',
    ])
        ->whereNumber('operario')
        ->name('operarios.update');


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR
    |--------------------------------------------------------------------------
    */

    Route::patch('/operarios/{operario}/estado', [
        OperarioController::class,
        'toggleEstado',
    ])
        ->whereNumber('operario')
        ->name('operarios.toggleEstado');


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    */

    Route::delete('/operarios/{operario}', [
        OperarioController::class,
        'destroy',
    ])
        ->whereNumber('operario')
        ->name('operarios.destroy');

});


/*
|--------------------------------------------------------------------------
| ASIGNACIÓN DE OBJETIVOS DE OPERARIOS
|--------------------------------------------------------------------------
| Súper Admin y Admin Cliente (Roles 1 y 2)
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:1,2'])->group(function () {

    Route::post('/operarios/{operario}/objetivo', [
        OperarioController::class,
        'asignarObjetivo',
    ])
        ->whereNumber('operario')
        ->name('operarios.asignarObjetivo');

});


/*
|--------------------------------------------------------------------------
| CALENDARIO
|--------------------------------------------------------------------------
| Acceso exclusivo para Operario (Rol 3)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:3'])->group(function () {

    Route::get('/calendario', [
        CalendarioController::class,
        'index',
    ])->name('calendario.index');


    Route::get('/calendario/crear', [
        CalendarioController::class,
        'create',
    ])->name('calendario.create');


    Route::post('/calendario', [
        CalendarioController::class,
        'store',
    ])->name('calendario.store');


    Route::get('/calendario/{id_cita}/editar', [
        CalendarioController::class,
        'edit',
    ])
        ->whereNumber('id_cita')
        ->name('calendario.edit');


    Route::put('/calendario/{id_cita}', [
        CalendarioController::class,
        'update',
    ])
        ->whereNumber('id_cita')
        ->name('calendario.update');


    Route::delete('/calendario/{id_cita}', [
        CalendarioController::class,
        'destroy',
    ])
        ->whereNumber('id_cita')
        ->name('calendario.destroy');

});


/*
|--------------------------------------------------------------------------
| CITAS
|--------------------------------------------------------------------------
| Acceso exclusivo para Admin Cliente (Rol 2)
|--------------------------------------------------------------------------
*/

Route::middleware(['rol:2'])->group(function () {

    Route::get('/citas', [
        CitaController::class,
        'index',
    ])->name('citas.index');


    Route::get('/citas/crear', [
        CitaController::class,
        'create',
    ])->name('citas.create');


    Route::post('/citas', [
        CitaController::class,
        'store',
    ])->name('citas.store');


    Route::get('/citas/{id_cita}', [
        CitaController::class,
        'show',
    ])
        ->whereNumber('id_cita')
        ->name('citas.show');


    Route::get('/citas/{id_cita}/editar', [
        CitaController::class,
        'edit',
    ])
        ->whereNumber('id_cita')
        ->name('citas.edit');


    Route::put('/citas/{id_cita}', [
        CitaController::class,
        'update',
    ])
        ->whereNumber('id_cita')
        ->name('citas.update');


    Route::delete('/citas/{id_cita}', [
        CitaController::class,
        'destroy',
    ])
        ->whereNumber('id_cita')
        ->name('citas.destroy');

});