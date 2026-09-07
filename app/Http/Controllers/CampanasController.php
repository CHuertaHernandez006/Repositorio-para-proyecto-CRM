<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CampanasController extends Controller
{
    public function index()
    {
        // 1. Validar que exista la sesión activa
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        // 2. Retornar la vista del módulo
        return view('campanas.index');
    }
}
