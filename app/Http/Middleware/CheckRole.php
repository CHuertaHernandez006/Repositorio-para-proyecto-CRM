<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        // Si no hay sesión, enviar al login.
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Preparar los roles permitidos para esta ruta.
        $rolesPermitidos = array_map(
            'trim',
            explode(',', implode(',', $roles))
        );

        $rolUsuario = (string) Auth::user()->id_rol;

        // Se aplica a todos, incluido el Super Admin.
        if (!in_array($rolUsuario, $rolesPermitidos, true)) {
            abort(
                403,
                'No tienes permiso para acceder a esta sección del CRM.'
            );
        }

        return $next($request);
    }
}