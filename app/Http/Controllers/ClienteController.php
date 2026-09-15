<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index()
    {
        // 1. Validar que exista la sesión activa
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        // 2. Traemos todos los clientes de la base de datos
        $clientes = Cliente::all();

        // 3. Retornamos la vista, pero ahora pasándole los datos de la BD
        return view('clientes.index', compact('clientes'));
    }
}
