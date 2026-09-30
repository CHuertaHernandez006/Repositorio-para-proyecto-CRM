<?php

namespace App\Http\Controllers;

use App\Models\Campana;
use App\Models\Cliente;
use App\Models\EstadoCampana;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampanasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        /*
        |--------------------------------------------------------------------------
        | SINCRONIZAR ESTADOS AUTOMÁTICOS
        |--------------------------------------------------------------------------
        */

        $this->sincronizarEstadosAutomaticos($usuario);

        /*
        |--------------------------------------------------------------------------
        | CONSULTA BASE
        |--------------------------------------------------------------------------
        */

        $consulta = $this
            ->consultaCampanasVisibles($usuario)
            ->with([
                'estadoCampana',

                'clientes' => function ($query) {
                    $query
                        ->orderBy('nombre')
                        ->orderBy('apellido_paterno');
                },
            ])
            ->withCount('clientes');

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        if ($request->filled('buscar')) {
            $buscar = trim((string) $request->buscar);

            $termino =
                '%' .
                mb_strtolower($buscar, 'UTF-8') .
                '%';

            $consulta->where(
                function ($query) use ($termino) {
                    $query
                        ->whereRaw(
                            'LOWER(nombre) LIKE ?',
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(COALESCE(descripcion, '')) LIKE ?",
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(COALESCE(objetivo, '')) LIKE ?",
                            [$termino]
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR ESTADO
        |--------------------------------------------------------------------------
        */

        if ($request->filled('estado')) {
            $consulta->where(
                'id_estado_campana',
                $request->estado
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ORDEN
        |--------------------------------------------------------------------------
        */

        switch ($request->get('orden')) {
            case 'nombre_desc':
                $consulta->orderByDesc('nombre');
                break;

            case 'inicio_asc':
                $consulta->orderBy(
                    'fecha_inicio',
                    'asc'
                );
                break;

            case 'inicio_desc':
                $consulta->orderByDesc(
                    'fecha_inicio'
                );
                break;

            case 'fin_asc':
                $consulta->orderBy(
                    'fecha_fin',
                    'asc'
                );
                break;

            case 'fin_desc':
                $consulta->orderByDesc(
                    'fecha_fin'
                );
                break;

            case 'nombre_asc':
            default:
                $consulta->orderBy(
                    'nombre',
                    'asc'
                );
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | RESULTADOS
        |--------------------------------------------------------------------------
        */

        $campanas = $consulta
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | CATÁLOGO DE ESTADOS
        |--------------------------------------------------------------------------
        */

        $estados = EstadoCampana::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ESTADÍSTICAS
        |--------------------------------------------------------------------------
        */

        $hoy = now()->toDateString();

        $baseEstadisticas =
            $this->consultaCampanasVisibles(
                $usuario
            );

        $totalCampanas =
            (clone $baseEstadisticas)->count();

        $campanasEnCurso =
            (clone $baseEstadisticas)
                ->whereDate(
                    'fecha_inicio',
                    '<=',
                    $hoy
                )
                ->where(function ($query) use ($hoy) {
                    $query
                        ->whereNull('fecha_fin')
                        ->orWhereDate(
                            'fecha_fin',
                            '>=',
                            $hoy
                        );
                })
                ->count();

        $campanasProximas =
            (clone $baseEstadisticas)
                ->whereDate(
                    'fecha_inicio',
                    '>',
                    $hoy
                )
                ->count();

        $campanasFinalizadas =
            (clone $baseEstadisticas)
                ->whereNotNull('fecha_fin')
                ->whereDate(
                    'fecha_fin',
                    '<',
                    $hoy
                )
                ->count();

        return view(
            'campanas.index',
            compact(
                'campanas',
                'estados',
                'totalCampanas',
                'campanasEnCurso',
                'campanasProximas',
                'campanasFinalizadas'
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

        $this->autorizarAdministracion($usuario);

        $estados = EstadoCampana::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'campanas.create',
            compact('estados')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR CAMPAÑA
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        $datos = $request->validate(
            [
                'nombre' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'descripcion' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'objetivo' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'fecha_inicio' => [
                    'required',
                    'date',
                ],

                'fecha_fin' => [
                    'required',
                    'date',
                    'after_or_equal:fecha_inicio',
                ],

                /*
                |--------------------------------------------------------------------------
                | Super Admin puede mandar explícitamente la empresa.
                | Admin Cliente siempre queda forzado a su propia empresa.
                |--------------------------------------------------------------------------
                */

                'id_empresa' => [
                    'nullable',
                    'integer',
                    'exists:empresas,id_empresa',
                ],
            ],
            [
                'nombre.required' =>
                    'El nombre de la campaña es obligatorio.',

                'nombre.max' =>
                    'El nombre no puede exceder 150 caracteres.',

                'fecha_inicio.required' =>
                    'La fecha de inicio es obligatoria.',

                'fecha_inicio.date' =>
                    'La fecha de inicio no es válida.',

                'fecha_fin.required' =>
                    'La fecha final es obligatoria.',

                'fecha_fin.date' =>
                    'La fecha final no es válida.',

                'fecha_fin.after_or_equal' =>
                    'La fecha final no puede ser anterior a la fecha de inicio.',
            ]
        );

        $idEmpresa =
            $this->resolverEmpresaParaNuevaCampana(
                $usuario,
                $datos['id_empresa'] ?? null
            );

        $this->validarNombreUnico(
            $datos['nombre'],
            $idEmpresa
        );

        $estado =
            $this->obtenerEstadoAutomatico(
                $datos['fecha_inicio'],
                $datos['fecha_fin']
            );

        $datos['id_empresa'] =
            $idEmpresa;

        $datos['id_estado_campana'] =
            $estado->id_estado_campana;

        Campana::create($datos);

        return redirect()
            ->route('campanas.index')
            ->with(
                'success',
                'Campaña creada correctamente. El estado fue determinado automáticamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | VER CAMPAÑA
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        $campana = $this
            ->consultaCampanasVisibles($usuario)
            ->with('estadoCampana')
            ->withCount('clientes')
            ->findOrFail($id);

        $this->sincronizarEstadoAutomatico(
            $campana
        );

        $campana->load(
            'estadoCampana'
        );

        return view(
            'campanas.show',
            compact('campana')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULARIO EDITAR
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        $campana = $this
            ->consultaCampanasVisibles($usuario)
            ->with('estadoCampana')
            ->findOrFail($id);

        $this->sincronizarEstadoAutomatico(
            $campana
        );

        $campana->load(
            'estadoCampana'
        );

        $estados = EstadoCampana::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'campanas.edit',
            compact(
                'campana',
                'estados'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR CAMPAÑA
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        $campana = $this
            ->consultaCampanasVisibles($usuario)
            ->with('estadoCampana')
            ->findOrFail($id);

        $datos = $request->validate(
            [
                'nombre' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'descripcion' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'objetivo' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'fecha_inicio' => [
                    'required',
                    'date',
                ],

                'fecha_fin' => [
                    'required',
                    'date',
                    'after_or_equal:fecha_inicio',
                ],
            ],
            [
                'nombre.required' =>
                    'El nombre de la campaña es obligatorio.',

                'nombre.max' =>
                    'El nombre no puede exceder 150 caracteres.',

                'fecha_inicio.required' =>
                    'La fecha de inicio es obligatoria.',

                'fecha_inicio.date' =>
                    'La fecha de inicio no es válida.',

                'fecha_fin.required' =>
                    'La fecha final es obligatoria.',

                'fecha_fin.date' =>
                    'La fecha final no es válida.',

                'fecha_fin.after_or_equal' =>
                    'La fecha final no puede ser anterior a la fecha de inicio.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | El tenant de una campaña no se cambia desde edición.
        |--------------------------------------------------------------------------
        */

        $this->validarNombreUnico(
            $datos['nombre'],
            (int) $campana->id_empresa,
            (int) $campana->id_campana
        );

        /*
        |--------------------------------------------------------------------------
        | CANCELADA ES UNA EXCEPCIÓN
        |--------------------------------------------------------------------------
        */

        if (
            $this->campanaEstaCancelada(
                $campana
            )
        ) {
            $datos['id_estado_campana'] =
                $campana->id_estado_campana;
        } else {
            $estado =
                $this->obtenerEstadoAutomatico(
                    $datos['fecha_inicio'],
                    $datos['fecha_fin']
                );

            $datos['id_estado_campana'] =
                $estado->id_estado_campana;
        }

        $campana->update($datos);

        return redirect()
            ->route(
                'campanas.show',
                $campana->id_campana
            )
            ->with(
                'success',
                'Campaña actualizada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CLIENTES DE LA CAMPAÑA
    |--------------------------------------------------------------------------
    */

    public function clientes($campana)
    {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        $campana = $this
            ->consultaCampanasVisibles($usuario)
            ->with([
                'estadoCampana',
                'clientes',
            ])
            ->findOrFail($campana);

        $this->sincronizarEstadoAutomatico(
            $campana
        );

        /*
        |--------------------------------------------------------------------------
        | CLIENTES ASIGNADOS
        |--------------------------------------------------------------------------
        */

        $clientesAsignados =
            $campana
                ->clientes()
                ->where(
                    'clientes.id_empresa',
                    $campana->id_empresa
                )
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->get();

        $idsAsignados =
            $clientesAsignados
                ->pluck('id_cliente');

        /*
        |--------------------------------------------------------------------------
        | CLIENTES DISPONIBLES
        |--------------------------------------------------------------------------
        |
        | Siempre deben pertenecer al MISMO tenant que la campaña.
        |--------------------------------------------------------------------------
        */

        $clientesDisponibles =
            Cliente::query()
                ->where(
                    'id_empresa',
                    $campana->id_empresa
                )
                ->whereNotIn(
                    'id_cliente',
                    $idsAsignados
                )
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->get();

        $campana->load(
            'estadoCampana'
        );

        return view(
            'campanas.clientes',
            compact(
                'campana',
                'clientesAsignados',
                'clientesDisponibles'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ASIGNAR CLIENTES
    |--------------------------------------------------------------------------
    */

    public function asignarClientes(
        Request $request,
        $campana
    ) {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        $campana = $this
            ->consultaCampanasVisibles($usuario)
            ->findOrFail($campana);

        $datos = $request->validate(
            [
                'clientes' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'clientes.*' => [
                    'required',
                    'integer',
                ],
            ],
            [
                'clientes.required' =>
                    'Selecciona al menos un cliente.',

                'clientes.min' =>
                    'Selecciona al menos un cliente.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDAR QUE TODOS LOS CLIENTES PERTENEZCAN AL TENANT
        |--------------------------------------------------------------------------
        */

        $idsSolicitados =
            collect($datos['clientes'])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

        $clientesValidos =
            Cliente::query()
                ->where(
                    'id_empresa',
                    $campana->id_empresa
                )
                ->whereIn(
                    'id_cliente',
                    $idsSolicitados
                )
                ->pluck('id_cliente')
                ->map(fn ($id) => (int) $id);

        if (
            $clientesValidos->count() !==
            $idsSolicitados->count()
        ) {
            throw ValidationException::withMessages([
                'clientes' =>
                    'Uno o más clientes no existen o no pertenecen a la empresa de esta campaña.',
            ]);
        }

        DB::transaction(
            function () use (
                $campana,
                $idsSolicitados
            ) {
                foreach (
                    $idsSolicitados as $idCliente
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | La nueva tabla campana_cliente tiene UNIQUE(id_campana,id_cliente).
                    | Si ya existió la relación, se reactiva.
                    |--------------------------------------------------------------------------
                    */

                    $relacionExistente =
                        DB::table('campana_cliente')
                            ->where(
                                'id_campana',
                                $campana->id_campana
                            )
                            ->where(
                                'id_cliente',
                                $idCliente
                            )
                            ->first();

                    if ($relacionExistente) {
                        DB::table('campana_cliente')
                            ->where(
                                'id_campana_cliente',
                                $relacionExistente
                                    ->id_campana_cliente
                            )
                            ->update([
                                'id_empresa' =>
                                    $campana->id_empresa,

                                'activo' =>
                                    true,

                                'updated_at' =>
                                    now(),
                            ]);

                        continue;
                    }

                    DB::table('campana_cliente')
                        ->insert([
                            'id_empresa' =>
                                $campana->id_empresa,

                            'id_campana' =>
                                $campana->id_campana,

                            'id_cliente' =>
                                $idCliente,

                            'activo' =>
                                true,

                            'intentos' =>
                                0,

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);
                }
            }
        );

        return redirect()
            ->route(
                'campanas.clientes',
                $campana->id_campana
            )
            ->with(
                'success',
                $idsSolicitados->count() === 1
                    ? 'Cliente agregado a la campaña correctamente.'
                    : $idsSolicitados->count()
                        . ' clientes agregados a la campaña correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | QUITAR CLIENTE DE CAMPAÑA
    |--------------------------------------------------------------------------
    */

    public function quitarCliente(
        $campana,
        $cliente
    ) {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        $campana = $this
            ->consultaCampanasVisibles($usuario)
            ->findOrFail($campana);

        $cliente = Cliente::query()
            ->where(
                'id_empresa',
                $campana->id_empresa
            )
            ->findOrFail($cliente);

        $actualizados =
            DB::table('campana_cliente')
                ->where(
                    'id_empresa',
                    $campana->id_empresa
                )
                ->where(
                    'id_campana',
                    $campana->id_campana
                )
                ->where(
                    'id_cliente',
                    $cliente->id_cliente
                )
                ->update([
                    'activo' =>
                        false,

                    'updated_at' =>
                        now(),
                ]);

        if ($actualizados === 0) {
            return back()
                ->withErrors([
                    'cliente' =>
                        'El cliente no está asignado a esta campaña.',
                ]);
        }

        return redirect()
            ->route(
                'campanas.clientes',
                $campana->id_campana
            )
            ->with(
                'success',
                'Cliente retirado de la campaña correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR CAMPAÑA
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $usuario = auth()->user();

        $this->autorizarAdministracion($usuario);

        $campana = $this
            ->consultaCampanasVisibles($usuario)
            ->findOrFail($id);

        $nombre =
            $campana->nombre;

        /*
        |--------------------------------------------------------------------------
        | Campana usa SoftDeletes.
        |--------------------------------------------------------------------------
        */

        $campana->delete();

        return redirect()
            ->route('campanas.index')
            ->with(
                'success',
                'La campaña "' .
                $nombre .
                '" fue eliminada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MÉTODOS INTERNOS
    |--------------------------------------------------------------------------
    */

    private function autorizarAdministracion(
        $usuario
    ): void {
        if (
            !$usuario ||
            !in_array(
                (int) $usuario->id_rol,
                [1, 2],
                true
            )
        ) {
            abort(403);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CAMPAÑAS VISIBLES SEGÚN ROL
    |--------------------------------------------------------------------------
    */

    private function consultaCampanasVisibles(
        $usuario
    ) {
        $consulta =
            Campana::query();

        if ((int) $usuario->id_rol === 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        return $consulta;
    }


    /*
    |--------------------------------------------------------------------------
    | EMPRESA PARA UNA CAMPAÑA NUEVA
    |--------------------------------------------------------------------------
    */

    private function resolverEmpresaParaNuevaCampana(
        $usuario,
        $idEmpresaSolicitada = null
    ): int {
        if ((int) $usuario->id_rol === 2) {
            if (!$usuario->id_empresa) {
                throw ValidationException::withMessages([
                    'id_empresa' =>
                        'Tu cuenta no tiene una empresa asociada.',
                ]);
            }

            return (int) $usuario->id_empresa;
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Si la vista todavía no tiene selector de empresa, usamos su empresa
        | asociada como compatibilidad.
        |--------------------------------------------------------------------------
        */

        $idEmpresa =
            $idEmpresaSolicitada
            ?: $usuario->id_empresa;

        if (!$idEmpresa) {
            throw ValidationException::withMessages([
                'id_empresa' =>
                    'Debes seleccionar una empresa para crear la campaña.',
            ]);
        }

        $existe =
            DB::table('empresas')
                ->where(
                    'id_empresa',
                    $idEmpresa
                )
                ->exists();

        if (!$existe) {
            throw ValidationException::withMessages([
                'id_empresa' =>
                    'La empresa seleccionada no existe.',
            ]);
        }

        return (int) $idEmpresa;
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR NOMBRE ÚNICO POR EMPRESA
    |--------------------------------------------------------------------------
    |
    | PostgreSQL tiene una restricción única por:
    |
    | id_empresa + LOWER(nombre)
    |
    | mientras deleted_at sea NULL.
    |--------------------------------------------------------------------------
    */

    private function validarNombreUnico(
        string $nombre,
        int $idEmpresa,
        ?int $ignorarCampana = null
    ): void {
        $consulta =
            DB::table('campanas')
                ->where(
                    'id_empresa',
                    $idEmpresa
                )
                ->whereNull(
                    'deleted_at'
                )
                ->whereRaw(
                    'LOWER(nombre) = LOWER(?)',
                    [trim($nombre)]
                );

        if ($ignorarCampana !== null) {
            $consulta->where(
                'id_campana',
                '!=',
                $ignorarCampana
            );
        }

        if ($consulta->exists()) {
            throw ValidationException::withMessages([
                'nombre' =>
                    'Ya existe una campaña con este nombre dentro de la empresa.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | OBTENER ESTADO SEGÚN FECHAS
    |--------------------------------------------------------------------------
    */

    private function obtenerEstadoAutomatico(
        $fechaInicio,
        $fechaFin
    ): EstadoCampana {
        $hoy =
            Carbon::today();

        $inicio =
            Carbon::parse(
                $fechaInicio
            )->startOfDay();

        $fin =
            $fechaFin
                ? Carbon::parse(
                    $fechaFin
                )->startOfDay()
                : null;

        /*
        |--------------------------------------------------------------------------
        | PLANEADA
        |--------------------------------------------------------------------------
        */

        if ($hoy->lt($inicio)) {
            return $this
                ->buscarEstadoPorNombre([
                    'Planeada',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | FINALIZADA
        |--------------------------------------------------------------------------
        */

        if (
            $fin !== null &&
            $hoy->gt($fin)
        ) {
            return $this
                ->buscarEstadoPorNombre([
                    'Finalizada',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | EN PROCESO
        |--------------------------------------------------------------------------
        |
        | Se mantiene compatibilidad con "Activa" porque el catálogo migrado
        | puede conservar ese nombre.
        |--------------------------------------------------------------------------
        */

        return $this
            ->buscarEstadoPorNombre([
                'En proceso',
                'Activa',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR ESTADO POR NOMBRE
    |--------------------------------------------------------------------------
    */

    private function buscarEstadoPorNombre(
        array $nombres
    ): EstadoCampana {
        foreach ($nombres as $nombre) {
            $estado =
                EstadoCampana::query()
                    ->where(
                        'activo',
                        true
                    )
                    ->whereRaw(
                        'LOWER(nombre) = LOWER(?)',
                        [trim($nombre)]
                    )
                    ->first();

            if ($estado) {
                return $estado;
            }
        }

        abort(
            500,
            'No se encontró en estados_campana uno de los estados requeridos: '
            . implode(
                ', ',
                $nombres
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SI ESTÁ CANCELADA
    |--------------------------------------------------------------------------
    */

    private function campanaEstaCancelada(
        Campana $campana
    ): bool {
        if (
            !$campana->relationLoaded(
                'estadoCampana'
            )
        ) {
            $campana->load(
                'estadoCampana'
            );
        }

        if (!$campana->estadoCampana) {
            return false;
        }

        return mb_strtolower(
            trim(
                $campana
                    ->estadoCampana
                    ->nombre
            ),
            'UTF-8'
        ) === 'cancelada';
    }


    /*
    |--------------------------------------------------------------------------
    | SINCRONIZAR UNA CAMPAÑA
    |--------------------------------------------------------------------------
    */

    private function sincronizarEstadoAutomatico(
        Campana $campana
    ): void {
        if (
            $this->campanaEstaCancelada(
                $campana
            )
        ) {
            return;
        }

        $estadoCorrecto =
            $this->obtenerEstadoAutomatico(
                $campana->fecha_inicio,
                $campana->fecha_fin
            );

        if (
            (int) $campana->id_estado_campana
            !==
            (int) $estadoCorrecto
                ->id_estado_campana
        ) {
            $campana->updateQuietly([
                'id_estado_campana' =>
                    $estadoCorrecto
                        ->id_estado_campana,
            ]);

            $campana->id_estado_campana =
                $estadoCorrecto
                    ->id_estado_campana;

            $campana->setRelation(
                'estadoCampana',
                $estadoCorrecto
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SINCRONIZAR CAMPAÑAS VISIBLES
    |--------------------------------------------------------------------------
    */

    private function sincronizarEstadosAutomaticos(
        $usuario
    ): void {
        $campanas = $this
            ->consultaCampanasVisibles($usuario)
            ->with('estadoCampana')
            ->get();

        foreach ($campanas as $campana) {
            $this->sincronizarEstadoAutomatico(
                $campana
            );
        }
    }
}
