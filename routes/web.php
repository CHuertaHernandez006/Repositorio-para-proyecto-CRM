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


use App\Http\Controllers\ConfiguracionController;


use App\Http\Controllers\PasswordRecoveryController;


use App\Http\Controllers\ComiAgendaController;


use App\Http\Controllers\AsignacionClienteController;

use App\Http\Controllers\CRMRefreshController;
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


// Mostrar formulario para solicitar recuperación.*

Route::get('/recuperar-contrasena', [

    PasswordRecoveryController::class,

    'mostrarSolicitud',

])->name('password.request');


// Enviar correo con el enlace de recuperación.*

Route::post('/recuperar-contrasena', [

    PasswordRecoveryController::class,

    'enviarEnlace',

])->middleware('throttle:3,5')->name('password.email');


// Mostrar formulario para crear una nueva contraseña.*

Route::get('/restablecer-contrasena/{token}', [

    PasswordRecoveryController::class,

    'mostrarRestablecer',

])->name('password.reset.form');


// Guardar la nueva contraseña.*

Route::post('/restablecer-contrasena', [

    PasswordRecoveryController::class,

    'restablecer',

])->middleware('throttle:5,1')->name('password.update');


Route::get('/registro_seleccion', [


    RegistroSeleccionController::class,


    'registroseleccion',


])->name('registro_seleccion');


Route::get('/registro-operador', [


    RegistroOperadorController::class,


    'create',


])->name('seleccion.registro_operador');


Route::get('/dashboard', [


    DashboardController::class,


    'index',


])


    ->middleware(['auth'])


    ->name('dashboard');


Route::middleware(['auth', 'rol:2,3'])->group(function () {


    Route::get('/clientes', [


        ClienteController::class,


        'index',


    ])->name('clientes.index');


});


Route::middleware(['auth', 'rol:2'])->group(function () {


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


Route::middleware(['auth', 'rol:2,3'])->group(function () {


    Route::get('/campanas', [


        CampanasController::class,


        'index',


    ])->name('campanas.index');


    Route::get('/campanas/{campana}/clientes', [


        CampanasController::class,


        'clientes',


    ])


        ->whereNumber('campana')


        ->name('campanas.clientes');


});


Route::middleware(['auth', 'rol:2'])->group(function () {


    Route::get('/campanas/crear', [


        CampanasController::class,


        'create',


    ])->name('campanas.create');


    Route::post('/campanas', [


        CampanasController::class,


        'store',


    ])->name('campanas.store');


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


    Route::delete('/campanas/{campana}', [


        CampanasController::class,


        'destroy',


    ])


        ->whereNumber('campana')


        ->name('campanas.destroy');


});


Route::middleware(['auth', 'rol:2,3'])->group(function () {


    Route::get('/campanas/{campana}', [


        CampanasController::class,


        'show',


    ])


        ->whereNumber('campana')


        ->name('campanas.show');


});


Route::middleware(['auth', 'rol:1,2,3'])->group(function () {


    Route::get('/llamadas', [


        LlamadasController::class,


        'index',


    ])->name('llamadas.index');


    Route::get('/llamadas/{id_llamada}', [


        LlamadasController::class,


        'show',


    ])


        ->whereNumber('id_llamada')


        ->name('llamadas.show');


});


Route::middleware(['auth', 'rol:1'])->group(function () {


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


Route::middleware(['auth', 'rol:2'])->group(function () {


    Route::get('/operarios', [


        OperarioController::class,


        'index',


    ])->name('operarios.index');


    Route::get('/operarios/objetivos', [


        OperarioController::class,


        'objetivos',


    ])->name('operarios.objetivos');


    Route::get('/operarios/create', [


        OperarioController::class,


        'create',


    ])->name('operarios.create');


    Route::post('/operarios', [


        OperarioController::class,


        'store',


    ])->name('operarios.store');


    Route::patch(


        '/operarios/{operario}/solicitud/reenviar',


        [


            OperarioController::class,


            'reenviarSolicitud',


        ]


    )


        ->whereNumber('operario')


        ->name('operarios.solicitud.reenviar');


    Route::get('/operarios/{operario}', [


        OperarioController::class,


        'show',


    ])


        ->whereNumber('operario')


        ->name('operarios.show');


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


    Route::patch('/operarios/{operario}/estado', [


        OperarioController::class,


        'toggleEstado',


    ])


        ->whereNumber('operario')


        ->name('operarios.toggleEstado');


    Route::delete('/operarios/{operario}', [


        OperarioController::class,


        'destroy',


    ])


        ->whereNumber('operario')


        ->name('operarios.destroy');


});


Route::middleware(['auth', 'rol:1'])->group(function () {


    Route::get('/operarios/solicitudes', [


        OperarioController::class,


        'solicitudes',


    ])->name('operarios.solicitudes');


    Route::patch(


        '/operarios/{operario}/solicitud/aprobar',


        [


            OperarioController::class,


            'aprobarSolicitud',


        ]


    )


        ->whereNumber('operario')


        ->name('operarios.solicitud.aprobar');


    Route::patch(


        '/operarios/{operario}/solicitud/rechazar',


        [


            OperarioController::class,


            'rechazarSolicitud',


        ]


    )


        ->whereNumber('operario')


        ->name('operarios.solicitud.rechazar');


});


Route::middleware(['auth', 'rol:1,2'])->group(function () {


    Route::post('/operarios/{operario}/objetivo', [


        OperarioController::class,


        'asignarObjetivo',


    ])


        ->whereNumber('operario')


        ->name('operarios.asignarObjetivo');


});


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


Route::middleware(['auth', 'rol:2'])->group(function () {


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


Route::middleware(['auth'])->group(function () {


    Route::get(


        '/configuracion',


        [


            ConfiguracionController::class,


            'index',


        ]


    )->name('configuracion.index');


    Route::put(


        '/configuracion/password',


        [


            ConfiguracionController::class,


            'actualizarPassword',


        ]


    )->name('configuracion.password.update');


});


Route::middleware(['auth'])->group(function () {

    // Agenda*

    Route::get('/comi/agenda', [

        ComiAgendaController::class,

        'index',

    ])->name('comi.agenda.index');


    Route::patch('/comi/agenda/{id_cita}/estado', [

        ComiAgendaController::class,

        'cambiarEstado',

    ])->whereNumber('id_cita')

      ->name('comi.agenda.estado');


    Route::get('/comi/agenda/{id_cita}/reprogramar', [

        ComiAgendaController::class,

        'reprogramarForm',

    ])->whereNumber('id_cita')

      ->name('comi.agenda.reprogramar.form');


    Route::patch('/comi/agenda/{id_cita}/reprogramar', [

        ComiAgendaController::class,

        'reprogramar',

    ])->whereNumber('id_cita')

      ->name('comi.agenda.reprogramar');



    /*
    |--------------------------------------------------------------------------
    | Agenda COMI - compatibilidad con citas_agenda
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/comi/agenda/legacy/{id}/estado',
        [
            ComiAgendaController::class,
            'cambiarEstadoLegacy',
        ]
    )
        ->whereNumber('id')
        ->name('comi.agenda.legacy.estado');


    Route::get(
        '/comi/agenda/legacy/{id}/reprogramar',
        [
            ComiAgendaController::class,
            'reprogramarLegacyForm',
        ]
    )
        ->whereNumber('id')
        ->name('comi.agenda.legacy.reprogramar.form');


    Route::patch(
        '/comi/agenda/legacy/{id}/reprogramar',
        [
            ComiAgendaController::class,
            'reprogramarLegacy',
        ]
    )
        ->whereNumber('id')
        ->name('comi.agenda.legacy.reprogramar');


    // Servicio técnico

    Route::get('/comi/servicio', [

        ComiAgendaController::class,

        'solicitudesServicio',

    ])->name('comi.servicio.index');


    Route::get('/comi/servicio/{id_solicitud}/programar', [

        ComiAgendaController::class,

        'programarVisitaForm',

    ])->whereNumber('id_solicitud')

      ->name('comi.servicio.programar.form');


    Route::post('/comi/servicio/{id_solicitud}/programar', [

        ComiAgendaController::class,

        'programarVisita',

    ])->whereNumber('id_solicitud')

      ->name('comi.servicio.programar');

});


require __DIR__.'/llamadas_salientes.php';


// Módulo comercial interno: permisos propios, sin modificar rutas de los inquilinos.*


require __DIR__.'/comercial.php';


// Alta: el controlador distingue Prospección de Asignación.*


Route::middleware(['auth', 'rol:2,3'])->group(function () {


    Route::get('/clientes/crear', [


        ClienteController::class,


        'create',


    ])->name('clientes.create');


    Route::post('/clientes', [


        ClienteController::class,


        'store',


    ])->name('clientes.store');


});


Route::middleware(['auth', 'rol:2'])->group(function () {


    Route::get('/asignaciones-clientes', [AsignacionClienteController::class, 'index'])


        ->name('clientes.asignaciones.index');


    Route::put('/asignaciones-clientes/{id_cliente}', [AsignacionClienteController::class, 'update'])


        ->whereNumber('id_cliente')->name('clientes.asignaciones.update');

Route::get('/crm/actualizaciones', [CRMRefreshController::class, 'estado'])
    ->name('crm.actualizaciones');
});

// Agregar a routes/web.php SIN borrar rutas existentes.
// Reutiliza los middleware de autenticación y autorización de los listados.

