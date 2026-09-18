<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function verificarAcceso(Request $request)
    {
        // 1. Validar las entradas del formulario
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Mapeamos los datos
        $credenciales = [
            'email' => $request->correo,
            'password' => $request->password
        ];

        // 3. Autenticación
        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // 4. Si la verificación falla
        return back()->withErrors([
            'correo' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput();
    }

    public function mostrarExito()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();
        $metricasData = [];

        // Si el usuario es Operador (id_rol = 3), podemos preparar/consultar los datos para metricas.index
        if ($usuario->id_rol == 3) {
            // Ejemplo de variables si tu vista metricas las requiere:
            // $metricasData = [
            //     'llamadasHoy' => 0,
            //     'tiempoPromedio' => '00:00',
            // ];
        }

        return view('dashboard', compact('usuario', 'metricasData'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}