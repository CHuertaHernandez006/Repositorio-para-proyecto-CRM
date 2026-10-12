<?php

use App\Http\Controllers\ComercialController;
use App\Http\Middleware\AccesoComercial;
use Illuminate\Support\Facades\Route;


Route::middleware([
    'auth',
    AccesoComercial::class
])
->prefix('comercial')
->name('comercial.')
->group(function () {


    /*
    |--------------------------------------------------------------------------
    | PROSPECTOS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/prospectos',
        [
            ComercialController::class,
            'index'
        ]
    )
    ->name(
        'prospectos.index'
    );


    /*
    |--------------------------------------------------------------------------
    | AGENDA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/agenda',
        [
            ComercialController::class,
            'agenda'
        ]
    )
    ->name(
        'agenda'
    );


    /*
    |--------------------------------------------------------------------------
    | DETALLE DEL PROSPECTO
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/prospectos/{id}',
        [
            ComercialController::class,
            'show'
        ]
    )
    ->whereNumber(
        'id'
    )
    ->name(
        'prospectos.show'
    );


    /*
    |--------------------------------------------------------------------------
    | SEGUIMIENTO
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/prospectos/{id}/seguimientos',
        [
            ComercialController::class,
            'seguimiento'
        ]
    )
    ->whereNumber(
        'id'
    )
    ->name(
        'seguimientos.store'
    );


    /*
    |--------------------------------------------------------------------------
    | FUNCIONES EXCLUSIVAS DEL SUPER ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        AccesoComercial::class . ':admin'
    )
    ->group(function () {


        /*
        |--------------------------------------------------------------------------
        | ASIGNACIÓN DE PROSPECTOS
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/prospectos/{id}/asignacion',
            [
                ComercialController::class,
                'asignar'
            ]
        )
        ->whereNumber(
            'id'
        )
        ->name(
            'asignar'
        );


        /*
        |--------------------------------------------------------------------------
        | CONTRATACIÓN
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/prospectos/{id}/contrato',
            [
                ComercialController::class,
                'contratar'
            ]
        )
        ->whereNumber(
            'id'
        )
        ->name(
            'contratar'
        );


        /*
        |--------------------------------------------------------------------------
        | EQUIPO COMERCIAL
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/equipo',
            [
                ComercialController::class,
                'equipo'
            ]
        )
        ->name(
            'equipo'
        );


        /*
        |--------------------------------------------------------------------------
        | CREAR ASESOR COMERCIAL
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/equipo',
            [
                ComercialController::class,
                'crearAsesor'
            ]
        )
        ->name(
            'equipo.store'
        );


        /*
        |--------------------------------------------------------------------------
        | ACTIVAR / DESACTIVAR ASESOR
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/equipo/{id}/estado',
            [
                ComercialController::class,
                'estadoAsesor'
            ]
        )
        ->whereNumber(
            'id'
        )
        ->name(
            'equipo.estado'
        );

    });

});