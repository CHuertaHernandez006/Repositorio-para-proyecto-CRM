<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;
use App\Models\Cliente;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        // 1. KPIs exclusivos para Súper Admin (Rol 1)
        if ($usuario->id_rol == 1) {
            
            $totalEmpresas = Empresa::count();
            $empresasActivas = Empresa::where('estado', true)->count();
            $totalLeads = Cliente::count();
            $ultimasEmpresas = Empresa::orderBy('created_at', 'desc')->take(5)->get();

            return view('dashboard', compact('totalEmpresas', 'empresasActivas', 'totalLeads', 'ultimasEmpresas'));
        }

        // 2. Espacio reservado para Admin Cliente (Rol 2)
        elseif ($usuario->id_rol == 2) {
            // Aquí tu equipo agregará sus variables (ej. $totalMisClientes)
            
            return view('dashboard');
        }

        // 3. Espacio reservado para Operarios / Agentes (Rol 3)
        else {
            // Aquí tu equipo agregará sus variables (ej. $misLlamadasHoy)
            
            return view('dashboard');
        }
    }
}