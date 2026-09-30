<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegistroOperadorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FORMULARIO DE REGISTRO DE OPERADOR
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'seleccion.registro_operador'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR REGISTRO
    |--------------------------------------------------------------------------
    |
    | Este flujo todavía no tiene lógica de persistencia implementada en el
    | proyecto original. Se conserva así para no inventar un alta paralela al
    | flujo oficial de operarios y aprobaciones del CRM.
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        //
    }
}
