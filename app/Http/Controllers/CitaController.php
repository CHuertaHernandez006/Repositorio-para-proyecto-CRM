<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\EstadoCita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $usuario = auth()->user();

        if (!$usuario) {
            abort(403);
        }

        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
            'estado' => 'nullable|integer',
            'operario' => 'nullable|integer',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
        ]);

        $buscar = trim($datos['buscar'] ?? '');

        $consulta = Cita::query()
            ->with([
                'cliente',
                'usuario',
                'estadoCita',
            ]);

        /*
        |--------------------------------------------------------------------------
        | PERMISOS / TENANT
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        } elseif ((int) $usuario->id_rol === 3) {
            $consulta
                ->where(
                    'id_empresa',
                    $usuario->id_empresa
                )
                ->where(
                    'id_usuario',
                    $usuario->id_usuario
                );
        } elseif ((int) $usuario->id_rol !== 1) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        if ($buscar !== '') {
            $termino =
                '%' .
                mb_strtolower($buscar, 'UTF-8') .
                '%';

            $consulta->whereHas(
                'cliente',
                function ($query) use ($termino) {
                    $query->where(
                        function ($cliente) use ($termino) {
                            $cliente
                                ->whereRaw(
                                    'LOWER(nombre) LIKE ?',
                                    [$termino]
                                )
                                ->orWhereRaw(
                                    'LOWER(apellido_paterno) LIKE ?',
                                    [$termino]
                                )
                                ->orWhereRaw(
                                    "LOWER(COALESCE(apellido_materno, '')) LIKE ?",
                                    [$termino]
                                )
                                ->orWhereRaw(
                                    "LOWER(COALESCE(organizacion, '')) LIKE ?",
                                    [$termino]
                                )
                                ->orWhereRaw(
                                    "LOWER(COALESCE(correo, '')) LIKE ?",
                                    [$termino]
                                )
                                ->orWhereRaw(
                                    'LOWER(telefono_principal) LIKE ?',
                                    [$termino]
                                );
                        }
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        if (!empty($datos['estado'])) {
            $consulta->where(
                'id_estado_cita',
                $datos['estado']
            );
        }

        if (
            !empty($datos['operario']) &&
            in_array(
                (int) $usuario->id_rol,
                [1, 2],
                true
            )
        ) {
            $consulta->where(
                'id_usuario',
                $datos['operario']
            );
        }

        if (!empty($datos['fecha_desde'])) {
            $consulta->whereDate(
                'fecha_hora_inicio',
                '>=',
                $datos['fecha_desde']
            );
        }

        if (!empty($datos['fecha_hasta'])) {
            $consulta->whereDate(
                'fecha_hora_inicio',
                '<=',
                $datos['fecha_hasta']
            );
        }

        $citas = $consulta
            ->orderBy(
                'fecha_hora_inicio',
                'asc'
            )
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | CATÁLOGOS
        |--------------------------------------------------------------------------
        */

        $operarios = collect();

        if ((int) $usuario->id_rol === 2) {
            $operarios = $this
                ->consultaOperariosDisponibles(
                    $usuario->id_empresa
                )
                ->get();
        } elseif ((int) $usuario->id_rol === 1) {
            $operarios = User::query()
                ->with([
                    'empresa',
                    'aprobacionActual',
                ])
                ->where('id_rol', 3)
                ->where('activo', true)
                ->whereHas(
                    'aprobacionActual',
                    fn ($query) =>
                    $query->where(
                        'estado',
                        'aprobado'
                    )
                )
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->get();
        }

        $estados = EstadoCita::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        if ((int) $usuario->id_rol === 3) {
            return view(
                'citas.mis-citas',
                compact(
                    'citas',
                    'buscar',
                    'estados'
                )
            );
        }

        return view(
            'citas.index',
            compact(
                'citas',
                'buscar',
                'operarios',
                'estados'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULARIO CREAR
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 2
        ) {
            abort(403);
        }

        $clientes = Cliente::query()
            ->where(
                'id_empresa',
                $usuario->id_empresa
            )
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get();

        $operarios = $this
            ->consultaOperariosDisponibles(
                $usuario->id_empresa
            )
            ->get();

        $estados = EstadoCita::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'citas.create',
            compact(
                'clientes',
                'operarios',
                'estados'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREAR CITA
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 2
        ) {
            abort(403);
        }

        $idEmpresa =
            (int) $usuario->id_empresa;

        $datos = $request->validate([
            'id_cliente' => [
                'required',
                'integer',
            ],

            'id_usuario' => [
                'required',
                'integer',
            ],

            'id_llamada' => [
                'nullable',
                'integer',
            ],

            'fecha_hora_inicio' => [
                'required',
                'date',
            ],

            'motivo' => [
                'nullable',
                'string',
                'max:255',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDAR CLIENTE DEL MISMO TENANT
        |--------------------------------------------------------------------------
        */

        Cliente::query()
            ->where(
                'id_cliente',
                $datos['id_cliente']
            )
            ->where(
                'id_empresa',
                $idEmpresa
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | VALIDAR OPERARIO
        |--------------------------------------------------------------------------
        |
        | Debe pertenecer a la misma empresa, estar activo, tener tipo y estar
        | aprobado.
        |--------------------------------------------------------------------------
        */

        $this
            ->consultaOperariosDisponibles(
                $idEmpresa
            )
            ->where(
                'id_usuario',
                $datos['id_usuario']
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | VALIDAR LLAMADA, SI EXISTE
        |--------------------------------------------------------------------------
        */

        if (!empty($datos['id_llamada'])) {
            $existeLlamada = DB::table('llamadas')
                ->where(
                    'id_llamada',
                    $datos['id_llamada']
                )
                ->where(
                    'id_empresa',
                    $idEmpresa
                )
                ->exists();

            abort_unless(
                $existeLlamada,
                404
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADO INICIAL
        |--------------------------------------------------------------------------
        */

        $estadoPendiente = EstadoCita::query()
            ->whereRaw(
                'LOWER(nombre) = LOWER(?)',
                ['pendiente']
            )
            ->where(
                'activo',
                true
            )
            ->first();

        if (!$estadoPendiente) {
            return back()
                ->withErrors([
                    'id_estado_cita' =>
                        'No existe un estado de cita activo llamado "Pendiente".',
                ])
                ->withInput();
        }

        Cita::create([
            'id_empresa' =>
                $idEmpresa,

            'id_cliente' =>
                (int) $datos['id_cliente'],

            'id_usuario' =>
                (int) $datos['id_usuario'],

            'id_llamada' =>
                $datos['id_llamada'] ?? null,

            'id_estado_cita' =>
                $estadoPendiente->id_estado_cita,

            'fecha_hora_inicio' =>
                $datos['fecha_hora_inicio'],

            'fecha_hora_fin' =>
                null,

            'motivo' =>
                $datos['motivo'] ?? null,

            'observaciones' =>
                $datos['observaciones'] ?? null,
        ]);

        return redirect()
            ->route('citas.index')
            ->with(
                'exito',
                'Cita registrada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DETALLE
    |--------------------------------------------------------------------------
    */

    public function show($id_cita)
    {
        $usuario = auth()->user();

        if (!$usuario) {
            abort(403);
        }

        $consulta = Cita::query()
            ->with([
                'cliente',
                'usuario',
                'estadoCita',
                'llamada',
            ]);

        if ((int) $usuario->id_rol === 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        } elseif ((int) $usuario->id_rol === 3) {
            $consulta
                ->where(
                    'id_empresa',
                    $usuario->id_empresa
                )
                ->where(
                    'id_usuario',
                    $usuario->id_usuario
                );
        } elseif ((int) $usuario->id_rol !== 1) {
            abort(403);
        }

        $cita = $consulta
            ->findOrFail($id_cita);

        return view(
            'citas.show',
            compact('cita')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULARIO EDITAR
    |--------------------------------------------------------------------------
    */

    public function edit($id_cita)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 2
        ) {
            abort(403);
        }

        $cita = Cita::query()
            ->with([
                'cliente',
                'usuario',
                'estadoCita',
            ])
            ->where(
                'id_empresa',
                $usuario->id_empresa
            )
            ->findOrFail($id_cita);

        $operarios = $this
            ->consultaOperariosDisponibles(
                $usuario->id_empresa
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Si la cita ya tenía un operario que ahora está inactivo o dejó de
        | estar disponible, se conserva en la lista para no romper la edición.
        |--------------------------------------------------------------------------
        */

        if (
            $cita->usuario &&
            !$operarios->contains(
                'id_usuario',
                $cita->id_usuario
            )
        ) {
            $operarios->push(
                $cita->usuario
            );
        }

        $estados = EstadoCita::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'citas.edit',
            compact(
                'cita',
                'operarios',
                'estados'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id_cita
    ) {
        $usuario = auth()->user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 2
        ) {
            abort(403);
        }

        $idEmpresa =
            (int) $usuario->id_empresa;

        $datos = $request->validate([
            'id_cliente' => [
                'required',
                'integer',
            ],

            'id_usuario' => [
                'required',
                'integer',
            ],

            'id_llamada' => [
                'nullable',
                'integer',
            ],

            'id_estado_cita' => [
                'required',
                'integer',
            ],

            'fecha_hora_inicio' => [
                'required',
                'date',
            ],

            'motivo' => [
                'nullable',
                'string',
                'max:255',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $cita = Cita::query()
            ->where(
                'id_empresa',
                $idEmpresa
            )
            ->findOrFail($id_cita);

        Cliente::query()
            ->where(
                'id_cliente',
                $datos['id_cliente']
            )
            ->where(
                'id_empresa',
                $idEmpresa
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Permitir conservar el operario actual incluso si fue desactivado
        | después de crearse la cita. Para cambiarlo, el nuevo sí debe estar
        | disponible.
        |--------------------------------------------------------------------------
        */

        if (
            (int) $datos['id_usuario'] !==
            (int) $cita->id_usuario
        ) {
            $this
                ->consultaOperariosDisponibles(
                    $idEmpresa
                )
                ->where(
                    'id_usuario',
                    $datos['id_usuario']
                )
                ->firstOrFail();
        } else {
            User::query()
                ->where(
                    'id_usuario',
                    $datos['id_usuario']
                )
                ->where(
                    'id_empresa',
                    $idEmpresa
                )
                ->where(
                    'id_rol',
                    3
                )
                ->firstOrFail();
        }

        EstadoCita::query()
            ->where(
                'id_estado_cita',
                $datos['id_estado_cita']
            )
            ->where(
                'activo',
                true
            )
            ->firstOrFail();

        if (!empty($datos['id_llamada'])) {
            $existeLlamada = DB::table('llamadas')
                ->where(
                    'id_llamada',
                    $datos['id_llamada']
                )
                ->where(
                    'id_empresa',
                    $idEmpresa
                )
                ->exists();

            abort_unless(
                $existeLlamada,
                404
            );
        }

        $cita->update([
            'id_cliente' =>
                (int) $datos['id_cliente'],

            'id_usuario' =>
                (int) $datos['id_usuario'],

            'id_llamada' =>
                $datos['id_llamada'] ?? null,

            'id_estado_cita' =>
                (int) $datos['id_estado_cita'],

            'fecha_hora_inicio' =>
                $datos['fecha_hora_inicio'],

            'motivo' =>
                $datos['motivo'] ?? null,

            'observaciones' =>
                $datos['observaciones'] ?? null,
        ]);

        return redirect()
            ->route('citas.index')
            ->with(
                'exito',
                'Cita actualizada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    */

    public function destroy($id_cita)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 2
        ) {
            abort(403);
        }

        $cita = Cita::query()
            ->where(
                'id_empresa',
                $usuario->id_empresa
            )
            ->findOrFail($id_cita);

        $cita->delete();

        return redirect()
            ->route('citas.index')
            ->with(
                'exito',
                'Cita eliminada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | OPERARIOS DISPONIBLES
    |--------------------------------------------------------------------------
    */

    private function consultaOperariosDisponibles(
        int $idEmpresa
    ) {
        return User::query()
            ->with([
                'tipoOperario',
                'aprobacionActual',
            ])
            ->where(
                'id_empresa',
                $idEmpresa
            )
            ->where(
                'id_rol',
                3
            )
            ->where(
                'activo',
                true
            )
            ->whereNotNull(
                'id_tipo_operario'
            )
            ->whereHas(
                'aprobacionActual',
                fn ($query) =>
                $query->where(
                    'estado',
                    'aprobado'
                )
            )
            ->orderBy('nombre')
            ->orderBy('apellido_paterno');
    }
}
