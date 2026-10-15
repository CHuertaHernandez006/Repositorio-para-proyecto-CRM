<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignacionClienteController extends Controller
{
    private function operarios($usuario)
    {
        return \App\Support\PerfilOperario::filtrarAsignacion(User::query())->where('id_empresa', $usuario->id_empresa)
            ->where('id_rol', 3)->where('activo', true)
            ->whereHas('aprobacionActual', fn ($q) => $q->where('estado', 'aprobado'));
    }

    public function index(Request $request)
    {
        $usuario = $request->user();
        abort_unless((int) $usuario->id_rol === 2, 403);
        $datos = $request->validate(['buscar' => 'nullable|string|max:100']);
        $buscar = trim($datos['buscar'] ?? '');
        $consulta = Cliente::query()->where('id_empresa', $usuario->id_empresa)
            ->with('responsable');
        if ($buscar !== '') {
            $consulta->where(function ($q) use ($buscar) {
                $q->where('nombre', 'ilike', '%'.$buscar.'%')
                    ->orWhere('apellido_paterno', 'ilike', '%'.$buscar.'%')
                    ->orWhere('organizacion', 'ilike', '%'.$buscar.'%')
                    ->orWhere('telefono_principal', 'ilike', '%'.$buscar.'%');
            });
        }
        $clientes = $consulta->orderByDesc('id_cliente')->paginate(15)->withQueryString();
        $operarios = $this->operarios($usuario)->orderBy('nombre')->get();
        return view('clientes.asignaciones', compact('clientes', 'operarios', 'buscar'));
    }

    public function update(Request $request, $id_cliente)
    {
        $usuario = $request->user();
        abort_unless((int) $usuario->id_rol === 2, 403);
        $datos = $request->validate([
            'id_usuario_responsable' => 'nullable|integer|min:1',
            'responsable_anterior' => 'nullable|integer|min:1',
        ]);
        DB::transaction(function () use ($usuario, $datos, $id_cliente) {
            $cliente = Cliente::query()->where('id_empresa', $usuario->id_empresa)
                ->lockForUpdate()->findOrFail($id_cliente);
            if ((int) $cliente->id_usuario_responsable !== (int) ($datos['responsable_anterior'] ?? 0)) {
                throw ValidationException::withMessages(['asignacion' =>
                    'La asignación cambió mientras tenías abierta la página. Recarga antes de volver a guardar.']);
            }
            $responsable = $datos['id_usuario_responsable'] ?? null;
            if ($responsable !== null) {
                $operario = $this->operarios($usuario)->where('id_usuario', $responsable)
                    ->lockForUpdate()->first();
                if (!$operario) {
                    throw ValidationException::withMessages(['asignacion' =>
                        'Selecciona un operario de Asignación activo y aprobado de tu empresa.']);
                }
            }
            $cliente->id_usuario_responsable = $responsable;
            $cliente->save();
        });
        return back()->with('success', 'Responsable actualizado correctamente.');
    }
}
