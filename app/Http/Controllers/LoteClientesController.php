<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Clientes\EstadoAsignacionLote;
use App\Support\PerfilOperario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoteClientesController extends Controller
{
    /** @return \App\Models\User */
    private function actor(Request $request)
    {
        /** @var \App\Models\User|null $usuario */
        $usuario = $request->user();
        abort_unless($usuario && (int) $usuario->id_rol === 2 && $usuario->activo && !$usuario->deleted_at
            && (int) $usuario->id_empresa > 0, 403);
        abort_unless(DB::table('empresas')->where('id_empresa', $usuario->id_empresa)->where('activo', true)->exists(), 403);
        return $usuario;
    }

    private function lote($usuario, string $id, bool $bloquear = false)
    {
        $consulta = DB::table('lotes_importacion_clientes')->where('id_empresa', $usuario->id_empresa)->where('id_lote', $id);
        if ($bloquear) {
            $consulta->lockForUpdate();
        }
        return $consulta->firstOrFail();
    }

    private function operarios($usuario)
    {
        return PerfilOperario::filtrarAsignacion(User::query())->where('id_empresa', $usuario->id_empresa)
            ->where('id_rol', 3)->where('activo', true)
            ->whereHas('aprobacionActual', fn ($q) => $q->where('estado', 'aprobado'));
    }

    private function validarOperario($usuario, $id): void
    {
        if ($id !== null && !$this->operarios($usuario)->where('id_usuario', $id)->lockForUpdate()->first()) {
            throw ValidationException::withMessages(['asignacion' => 'Elige un operario de Asignación activo y aprobado de tu empresa.']);
        }
    }

    private function clientes($usuario, string $id)
    {
        return DB::table('clientes as c')->join('lote_importacion_clientes as m', function ($join) {
            $join->on('c.id_cliente', '=', 'm.id_cliente')->on('c.id_empresa', '=', 'm.id_empresa');
        })->where('c.id_empresa', $usuario->id_empresa)->where('m.id_empresa', $usuario->id_empresa)
            ->where('m.id_lote', $id)->whereNull('c.deleted_at');
    }

    private function estado($usuario, string $id, bool $bloquear = false)
    {
        $consulta = $this->clientes($usuario, $id)->select('c.id_cliente', 'c.id_usuario_responsable',
            'c.updated_at', 'm.excepcion', 'm.updated_at as miembro_actualizado')->orderBy('c.id_cliente');
        if ($bloquear) {
            // Misma secuencia que la asignación individual: cliente antes de miembro y operario.
            $consulta->lock('FOR UPDATE OF c, m');
        }
        return $consulta->get();
    }

    private function comprobarRevision(string $recibida, string $actual): void
    {
        if (!hash_equals($actual, $recibida)) {
            throw ValidationException::withMessages(['asignacion' => 'El lote cambió mientras la página estaba abierta. Recarga y revisa los responsables antes de guardar.']);
        }
    }

    public function index(Request $request)
    {
        $usuario = $this->actor($request);
        $datos = $request->validate(['buscar' => 'nullable|string|max:100']);
        $buscar = trim($datos['buscar'] ?? '');
        $consulta = DB::table('lotes_importacion_clientes as l')->where('l.id_empresa', $usuario->id_empresa)
            ->select('l.*')->selectSub(function ($q) use ($usuario) {
                $q->from('lote_importacion_clientes as m')->join('clientes as c', function ($j) {
                    $j->on('c.id_cliente', '=', 'm.id_cliente')->on('c.id_empresa', '=', 'm.id_empresa');
                })->whereColumn('m.id_lote', 'l.id_lote')->where('m.id_empresa', $usuario->id_empresa)
                    ->whereNull('c.deleted_at')->selectRaw('COUNT(*)');
            }, 'disponibles');
        if ($buscar !== '') {
            $consulta->where('l.nombre_archivo', 'ilike', '%'.$buscar.'%');
        }
        $lotes = $consulta->orderByDesc('l.created_at')->orderByDesc('l.id_lote')->paginate(15)->withQueryString();
        return view('clientes.lotes.index', compact('lotes', 'buscar'));
    }

    public function mostrar(Request $request, string $lote)
    {
        $usuario = $this->actor($request);
        $registro = $this->lote($usuario, $lote);
        $datos = $request->validate(['buscar' => 'nullable|string|max:100',
            'filtro' => ['nullable', Rule::in(['todos', 'sin_asignar', 'excepciones'])]]);
        $buscar = trim($datos['buscar'] ?? '');
        $filtro = $datos['filtro'] ?? 'todos';
        $estado = $this->estado($usuario, $lote);
        $revision = EstadoAsignacionLote::revision($estado);
        $total = $estado->count();
        $excepciones = $estado->filter(fn ($c) => (bool) $c->excepcion)->count();
        $sinAsignar = $estado->filter(fn ($c) => $c->id_usuario_responsable === null)->count();
        $asignables = $estado->filter(fn ($c) => EstadoAsignacionLote::elegible($c, 'sin_asignar'))->count();
        $ordinarios = $total - $excepciones;
        $consulta = $this->clientes($usuario, $lote)->select('c.*', 'm.excepcion', 'm.fila_archivo',
            'm.updated_at as miembro_actualizado');
        if ($buscar !== '') {
            $consulta->where(function ($q) use ($buscar) {
                $q->where('c.nombre', 'ilike', '%'.$buscar.'%')->orWhere('c.apellido_paterno', 'ilike', '%'.$buscar.'%')
                    ->orWhere('c.organizacion', 'ilike', '%'.$buscar.'%')->orWhere('c.telefono_principal', 'ilike', '%'.$buscar.'%');
            });
        }
        if ($filtro === 'sin_asignar') {
            $consulta->whereNull('c.id_usuario_responsable');
        } elseif ($filtro === 'excepciones') {
            $consulta->where('m.excepcion', true);
        }
        $clientes = $consulta->orderBy('c.id_cliente')->paginate(20)->withQueryString();
        $operarios = $this->operarios($usuario)->orderBy('nombre')->get();
        $ids = $estado->pluck('id_usuario_responsable')->filter()->unique()->values();
        $responsables = User::query()->where('id_empresa', $usuario->id_empresa)->whereIn('id_usuario', $ids)->get()->keyBy('id_usuario');
        $creador = User::query()->where('id_empresa', $usuario->id_empresa)->where('id_usuario', $registro->id_usuario)->first();
        return view('clientes.lotes.mostrar', compact('registro', 'clientes', 'operarios', 'responsables', 'creador',
            'revision', 'total', 'excepciones', 'sinAsignar', 'asignables', 'ordinarios', 'buscar', 'filtro'));
    }

    public function asignar(Request $request, string $lote)
    {
        $usuario = $this->actor($request);
        $datos = $request->validate(['id_usuario_responsable' => 'required|integer|min:1',
            'modo' => ['required', Rule::in(['sin_asignar', 'ordinarios'])], 'revision' => 'required|string|size:64']);
        $cambiados = DB::transaction(function () use ($usuario, $datos, $lote) {
            $this->lote($usuario, $lote, true);
            $estado = $this->estado($usuario, $lote, true);
            $this->comprobarRevision($datos['revision'], EstadoAsignacionLote::revision($estado));
            $this->validarOperario($usuario, (int) $datos['id_usuario_responsable']);
            $ids = $estado->filter(fn ($c) => EstadoAsignacionLote::elegible($c, $datos['modo'])
                && (int) $c->id_usuario_responsable !== (int) $datos['id_usuario_responsable'])->pluck('id_cliente')->all();
            $n = 0;
            foreach (array_chunk($ids, 300) as $grupo) {
                $n += DB::table('clientes')->where('id_empresa', $usuario->id_empresa)->whereNull('deleted_at')
                    ->whereIn('id_cliente', $grupo)->update(['id_usuario_responsable' => (int) $datos['id_usuario_responsable'], 'updated_at' => now()]);
            }
            return $n;
        }, 3);
        return redirect()->route('clientes.lotes.mostrar', $lote)
            ->with('success', $cambiados.' clientes cambiaron de responsable. Se conservaron las excepciones individuales.');
    }

    public function individual(Request $request, string $lote, string $cliente)
    {
        $usuario = $this->actor($request);
        $datos = $request->validate(['id_usuario_responsable' => 'nullable|integer|min:1',
            'revision_cliente' => 'required|string|size:64', 'excepcion' => 'required|boolean']);
        DB::transaction(function () use ($usuario, $datos, $lote, $cliente) {
            $this->lote($usuario, $lote, true);
            $actual = $this->clientes($usuario, $lote)->where('c.id_cliente', $cliente)
                ->select('c.id_cliente', 'c.id_usuario_responsable', 'c.updated_at', 'm.excepcion',
                    'm.updated_at as miembro_actualizado')->lock('FOR UPDATE OF c, m')->firstOrFail();
            $this->comprobarRevision($datos['revision_cliente'], EstadoAsignacionLote::revision([$actual]));
            $responsable = isset($datos['id_usuario_responsable']) ? (int) $datos['id_usuario_responsable'] : null;
            $this->validarOperario($usuario, $responsable);
            DB::table('clientes')->where('id_empresa', $usuario->id_empresa)->where('id_cliente', $cliente)
                ->whereNull('deleted_at')->update(['id_usuario_responsable' => $responsable, 'updated_at' => now()]);
            DB::table('lote_importacion_clientes')->where('id_empresa', $usuario->id_empresa)->where('id_lote', $lote)
                ->where('id_cliente', $cliente)->update(['excepcion' => (bool) $datos['excepcion'], 'updated_at' => now()]);
        }, 3);
        return back()->with('success', 'Responsable actualizado. '.($datos['excepcion']
            ? 'Este cliente está protegido de las asignaciones masivas.' : 'Este cliente vuelve a participar en las asignaciones masivas.'));
    }
}
