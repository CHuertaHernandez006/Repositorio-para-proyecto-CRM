<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Importar el modelo específico
use App\Models\Calendario;
use App\Models\Cliente;
use App\Models\User;
use App\Models\EstadoCita;

class CalendarioController extends Controller
{
    /**
     * Muestra la lista de citas del operador autenticado.
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $idUsuario = $usuario->id ?? $usuario->id_usuario;

        $citas = Calendario::with(['cliente', 'estadoCita', 'usuario'])
            ->where('id_usuario', $idUsuario)
            ->orderBy('fecha_hora_inicio', 'asc')
            ->paginate(10);

        return view('calendario.index', compact('citas'));
    }

    /**
     * Formulario para agendar una nueva cita.
     */
    public function create()
    {
        $usuario = Auth::user();

        $clientes = Cliente::where('id_empresa', $usuario->id_empresa)
            ->orderBy('nombre')
            ->get();

        $operarios = User::where('id_empresa', $usuario->id_empresa)
            ->where('id_rol', 3)
            ->orderBy('name')
            ->get();

        $estados = EstadoCita::where('estado', true)
            ->orderBy('nombre')
            ->get();

        return view('calendario.create', compact('clientes', 'operarios', 'estados'));
    }

    /**
     * Guarda la nueva cita en la base de datos.
     */
    public function store(Request $request)
    {
        $usuario = Auth::user();
        $idUsuario = $usuario->id ?? $usuario->id_usuario;

        // Si es rol Operador (Rol 3) o no envió id_usuario, asigna su propio ID
        if ($usuario->id_rol == 3 || !$request->has('id_usuario')) {
            $request->merge(['id_usuario' => $idUsuario]);
        }

        $datos = $request->validate([
            'id_cliente'        => 'required|integer',
            'id_usuario'        => 'required|integer',
            'fecha_hora_inicio' => 'required|date',
            'motivo'            => 'nullable|string|max:255',
            'observaciones'     => 'nullable|string|max:1000',
        ]);

        $estadoPendiente = EstadoCita::whereRaw('LOWER(nombre) = ?', ['pendiente'])
            ->where('estado', true)
            ->first();

        $datos['id_estado_cita'] = $estadoPendiente 
            ? $estadoPendiente->id_estado_cita 
            : (EstadoCita::where('estado', true)->value('id_estado_cita') ?? 1);

        $datos['fecha_hora_fin'] = null;

        // Guarda usando el modelo Calendario
        Calendario::create($datos);

        return redirect()
            ->route('calendario.index')
            ->with('exito', 'Cita agendada correctamente.');
    }

    /**
     * Formulario para editar una cita existente.
     */
    public function edit($id_cita)
    {
        $usuario = Auth::user();
        $idUsuario = $usuario->id ?? $usuario->id_usuario;

        $cita = Calendario::where('id_usuario', $idUsuario)->findOrFail($id_cita);

        $clientes = Cliente::where('id_empresa', $usuario->id_empresa)
            ->orderBy('nombre')
            ->get();

        $operarios = User::where('id_empresa', $usuario->id_empresa)
            ->where('id_rol', 3)
            ->orderBy('name')
            ->get();

        $estados = EstadoCita::where('estado', true)
            ->orderBy('nombre')
            ->get();

        return view('calendario.edit', compact('cita', 'clientes', 'operarios', 'estados'));
    }

    /**
     * Actualiza la cita en la base de datos.
     */
    public function update(Request $request, $id_cita)
{
    $usuario = Auth::user();
    $idUsuario = $usuario->id ?? $usuario->id_usuario;

    // Buscar la cita asegurando que pertenezca al operario (si es rol 3)
    $cita = Calendario::where('id_usuario', $idUsuario)->findOrFail($id_cita);

    // Si es rol Operador (Rol 3), forzamos que no cambien el cliente ni el operario asignado
    if ($usuario->id_rol == 3) {
        $request->merge([
            'id_cliente' => $cita->id_cliente,
            'id_usuario' => $cita->id_usuario,
        ]);
    }

    $datos = $request->validate([
        'id_cliente'        => 'required|integer',
        'id_usuario'        => 'required|integer',
        'fecha_hora_inicio' => 'required|date',
        'fecha_hora_fin'    => 'nullable|date|after_or_equal:fecha_hora_inicio',
        'id_estado_cita'    => 'required|integer|exists:estado_citas,id_estado_cita',
        'motivo'            => 'nullable|string|max:255',
        'observaciones'     => 'nullable|string|max:1000',
    ]);

    $cita->update($datos);

    return redirect()
        ->route('calendario.index')
        ->with('exito', 'Cita actualizada correctamente.');
}

    /**
     * Elimina una cita.
     */
    public function destroy($id_cita)
    {
        $usuario = Auth::user();
        $idUsuario = $usuario->id ?? $usuario->id_usuario;

        $cita = Calendario::where('id_usuario', $idUsuario)->findOrFail($id_cita);
        $cita->delete();

        return redirect()
            ->route('calendario.index')
            ->with('exito', 'Cita eliminada correctamente.');
    }
}