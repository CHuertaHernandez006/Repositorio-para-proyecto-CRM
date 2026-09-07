<?php

namespace App\Http\Controllers;

use App\Models\Prueba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PruebaController extends Controller
{
    public function verificarAcceso(Request $request)
    {
        // 1. Validar las entradas enviadas desde la vista welcome.blade.php
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Consultar si el registro existe en la base de datos PostgreSQL
        $usuario = Prueba::where('correo', $request->correo)->first();

        // 3. Verificar usuario y hash de contraseña
        if ($usuario && Hash::check($request->password, $usuario->password)) {
            // Guardamos al usuario en la sesión de Laravel
            session(['usuario' => $usuario]);

            // Redirigimos sin parámetros de ID en la URL
            return redirect()->route('dashboard');
        }

        // 4. Si la verificación falla, regresa con error a la vista welcome
        return back()->withErrors([
            'correo' => 'Las credenciales no coinciden con nuestros registros en PostgreSQL.',
        ])->withInput();
    }

    public function mostrarExito()
    {
        // Verificar si existe la sesión activa
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = session('usuario');

        return view('dashboard', compact('usuario'));
    }

    public function logout()
    {
        session()->forget('usuario');
        return redirect()->route('login');
    }
}