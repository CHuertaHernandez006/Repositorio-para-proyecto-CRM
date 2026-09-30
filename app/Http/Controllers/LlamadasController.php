<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class LlamadasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MÓDULO DE LLAMADAS
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | La autenticación actual del CRM usa Laravel Auth.
        |
        | Ya no utilizamos session()->has('usuario'), porque la sesión de usuario
        | se administra mediante Auth::user().
        |--------------------------------------------------------------------------
        */

        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()
                ->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES PERMITIDOS
        |--------------------------------------------------------------------------
        |
        | 1 = Super Admin
        | 2 = Admin Cliente
        | 3 = Operario
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                (int) $usuario->id_rol,
                [1, 2, 3],
                true
            )
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Por ahora este controlador únicamente abre el módulo.
        |
        | La nueva base PostgreSQL ya dispone de la tabla llamadas, pero no
        | agregamos consultas aquí hasta revisar el modelo Llamada y la vista
        | llamadas.index, para no asumir qué información consume esa pantalla.
        |--------------------------------------------------------------------------
        */

        return view(
            'llamadas.index',
            compact('usuario')
        );
    }
}
