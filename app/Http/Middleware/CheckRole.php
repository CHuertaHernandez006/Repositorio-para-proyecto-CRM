<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
   public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Validar sesión
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Truco para convertir "1,2" en un arreglo real ["1", "2"]
        $rolesArray = explode(',', implode(',', $roles));

        // 3. Obtener el rol del usuario logueado en texto
        $rolUsuario = (string) Auth::user()->id_rol;

        // 4. Verificar si su rol está en la lista permitida
        if (!in_array($rolUsuario, $rolesArray)) {
            abort(403, 'No tienes permiso para acceder a esta sección del CRM.');
        }

        // 5. Si todo está bien, lo dejamos pasar a la vista
        return $next($request);
    }
}
