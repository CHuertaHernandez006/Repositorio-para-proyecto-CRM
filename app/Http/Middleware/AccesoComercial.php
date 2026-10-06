<?php

namespace App\Http\Middleware;

use App\Support\Comercial;
use Closure;
use Illuminate\Http\Request;

class AccesoComercial
{
    public function handle(Request $request, Closure $next, string $nivel = 'equipo')
    {
        $usuario = $request->user();
        abort_unless($usuario && $usuario->activo && !$usuario->deleted_at, 403);
        $super = (int) $usuario->id_rol === 1;
        abort_unless($super || ($nivel !== 'admin' && Comercial::asesor($usuario)), 403);
        Comercial::empresa();
        abort_unless(Comercial::rol() !== null, 503, 'Instala y activa el rol comercial.');
        return $next($request);
    }
}
