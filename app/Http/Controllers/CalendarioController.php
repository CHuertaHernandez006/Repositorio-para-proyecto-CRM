<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\EstadoCita;
use Illuminate\Support\Facades\Auth;

class CalendarioController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $idUsuario = $usuario->id_usuario ?? $usuario->id;

        $citas = Cita::with(['estadoCita'])
            ->where('id_usuario', $idUsuario)
            ->orderBy('fecha_hora_inicio', 'asc')
            ->paginate(10);

        return view('calendario.index', compact('citas'));
    }

    public function create()
    {
        // Opcional: Si tienes un modelo de Prospectos o Contactos registrados
        $prospectos = [];
        if (class_exists('App\Models\Prospecto')) {
            $prospectos = \App\Models\Prospecto::orderBy('nombre', 'asc')->get();
        }

        $estadosCita = EstadoCita::where('estado', true)->get();

        return view('calendario.create', compact('prospectos', 'estadosCita'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'id_prospecto'         => 'nullable|integer',
            'nombre_solicitante'   => 'required|string|max:255',
            'telefono_solicitante' => 'nullable|string|max:20',
            'correo_solicitante'   => 'nullable|email|max:255',
            'fecha_hora_inicio'    => 'required|date',
            'fecha_hora_fin'       => 'nullable|date|after_or_equal:fecha_hora_inicio',
            'motivo'               => 'required|string',
            'observaciones'        => 'nullable|string',
            'id_estado_cita'       => 'nullable|integer|exists:estado_citas,id_estado_cita',
        ]);

        $usuario = Auth::user();
        $datos['id_usuario'] = $usuario->id_usuario ?? $usuario->id;

        if (empty($datos['id_estado_cita'])) {
            $datos['id_estado_cita'] = 1; // ID 1 corresponde a 'Pendiente'
        }

        Cita::create($datos);

        return redirect()
            ->route('calendario.index')
            ->with('exito', 'Cita agendada correctamente.');
    }

    public function edit($id_cita)
    {
        $usuario = Auth::user();
        $idUsuario = $usuario->id_usuario ?? $usuario->id;

        $cita = Cita::where('id_usuario', $idUsuario)
            ->findOrFail($id_cita);

        $estadosCita = EstadoCita::where('estado', true)->get();

        return view('calendario.edit', compact('cita', 'estadosCita'));
    }

    public function update(Request $request, $id_cita)
    {
        $datos = $request->validate([
            'id_prospecto'         => 'nullable|integer',
            'nombre_solicitante'   => 'required|string|max:255',
            'telefono_solicitante' => 'nullable|string|max:20',
            'correo_solicitante'   => 'nullable|email|max:255',
            'fecha_hora_inicio'    => 'required|date',
            'fecha_hora_fin'       => 'nullable|date|after_or_equal:fecha_hora_inicio',
            'motivo'               => 'required|string',
            'observaciones'        => 'nullable|string',
            'id_estado_cita'       => 'required|integer|exists:estado_citas,id_estado_cita',
        ]);

        $usuario = Auth::user();
        $idUsuario = $usuario->id_usuario ?? $usuario->id;

        $cita = Cita::where('id_usuario', $idUsuario)
            ->findOrFail($id_cita);

        $cita->update($datos);

        return redirect()
            ->route('calendario.index')
            ->with('exito', 'Cita actualizada correctamente.');
    }

    public function destroy($id_cita)
    {
        $usuario = Auth::user();
        $idUsuario = $usuario->id_usuario ?? $usuario->id;

        $cita = Cita::where('id_usuario', $idUsuario)
            ->findOrFail($id_cita);

        $cita->delete();

        return redirect()
            ->route('calendario.index')
            ->with('exito', 'Cita eliminada correctamente.');
    }
}