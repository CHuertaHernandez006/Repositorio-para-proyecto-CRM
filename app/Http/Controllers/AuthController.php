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

        // 2. Mapeamos los datos. Laravel usa 'email' por defecto en su tabla 'users', 
        // pero tu formulario (welcome.blade.php) envía 'correo'. 
        $credenciales = [
            'email' => $request->correo,
            'password' => $request->password
        ];

        // 3. Auth::attempt busca al usuario, encripta la contraseña escrita y las compara.
        // Si todo es correcto, automáticamente genera una sesión súper segura.
        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();
            
            // Redirigimos al dashboard
            return redirect()->route('dashboard');
        }

        // 4. Si la verificación falla
        return back()->withErrors([
            'correo' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput();
    }

    public function mostrarExito()
    {
        // Usamos el Auth nativo para verificar si está logueado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Obtenemos todos los datos del usuario logueado (incluyendo su id_rol y id_empresa)
        $usuario = Auth::user();

        return view('dashboard', compact('usuario'));
    }

    public function logout(Request $request)
    {
        // Cerramos la sesión de Auth y limpiamos tokens por seguridad
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}