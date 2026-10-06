<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | SÚPER ADMIN
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 1) {
            $totalEmpresas =
                Empresa::query()->count();

            $empresasActivas =
                Empresa::query()
                    ->where('activo', true)
                    ->count();

            $totalLeads =
                Cliente::query()->count();

            $ultimasEmpresas =
                Empresa::query()
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get();

            return view(
                'dashboard',
                compact(
                    'totalEmpresas',
                    'empresasActivas',
                    'totalLeads',
                    'ultimasEmpresas'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN CLIENTE
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 2) {
            return view('dashboard');
        }

        /*
        |--------------------------------------------------------------------------
        | OPERARIO
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 3) {
            return view('dashboard');
        }

        if (\App\Support\Comercial::asesor($usuario)) {
            abort_unless($usuario->activo && !$usuario->deleted_at, 403);
            return redirect()->route('comercial.prospectos.index');
        }

        abort(403);
    }
}
