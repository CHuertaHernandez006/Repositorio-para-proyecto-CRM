<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegistroOperadorController extends Controller
{
    public function create()
    {
        // Nota el punto para acceder a la subcarpeta 'seleccion'
        return view('seleccion.registro_operador');
    }

    public function store(Request $request)
    {
        // Aquí procesarás el formulario cuando lo tengas listo
    }
}
