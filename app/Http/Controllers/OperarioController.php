<?php

namespace App\Http\Controllers;

use App\Models\AprobacionUsuario;
use App\Models\ObjetivoOperario;
use App\Models\TipoOperario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OperarioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO DE OPERARIOS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $consulta = $this
            ->consultaOperariosVisibles($usuario)
            ->with([
                'empresa',
                'tipoOperario',
                'aprobacionActual',
            ]);

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        if ($request->filled('buscar')) {
            $buscar = trim((string) $request->buscar);
            $termino = '%' . mb_strtolower($buscar, 'UTF-8') . '%';

            $consulta->where(function ($query) use ($termino) {
                $query
                    ->whereRaw(
                        "LOWER(nombre) LIKE ?",
                        [$termino]
                    )
                    ->orWhereRaw(
                        "LOWER(apellido_paterno) LIKE ?",
                        [$termino]
                    )
                    ->orWhereRaw(
                        "LOWER(COALESCE(apellido_materno, '')) LIKE ?",
                        [$termino]
                    )
                    ->orWhereRaw(
                        "LOWER(correo) LIKE ?",
                        [$termino]
                    )
                    ->orWhereRaw(
                        "LOWER(
                            CONCAT_WS(
                                ' ',
                                nombre,
                                apellido_paterno,
                                apellido_materno
                            )
                        ) LIKE ?",
                        [$termino]
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR ESTADO
        |--------------------------------------------------------------------------
        */

        if ($request->filled('estado')) {
            switch ($request->estado) {
                case 'activo':
                    $consulta->where('activo', true);
                    $this->filtrarAprobados($consulta);
                    break;

                case 'inactivo':
                    $consulta->where('activo', false);
                    $this->filtrarAprobados($consulta);
                    break;

                case 'pendiente':
                    $this->filtrarPorEstadoAprobacion(
                        $consulta,
                        'pendiente'
                    );
                    break;

                case 'rechazado':
                    $this->filtrarPorEstadoAprobacion(
                        $consulta,
                        'rechazado'
                    );
                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR TIPO
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tipo_operario')) {
            $consulta->where(
                'id_tipo_operario',
                $request->tipo_operario
            );
        }

        $operarios = $consulta
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | ESTADÍSTICAS
        |--------------------------------------------------------------------------
        */

        $base = $this->consultaOperariosVisibles($usuario);

        $totalOperarios = (clone $base)->count();

        $consultaActivos = clone $base;
        $this->filtrarAprobados($consultaActivos);

        $operariosActivos = $consultaActivos
            ->where('activo', true)
            ->count();

        $consultaInactivos = clone $base;
        $this->filtrarAprobados($consultaInactivos);

        $operariosInactivos = $consultaInactivos
            ->where('activo', false)
            ->count();

        $consultaPendientes = clone $base;
        $this->filtrarPorEstadoAprobacion(
            $consultaPendientes,
            'pendiente'
        );

        $operariosPendientes = $consultaPendientes->count();

        $consultaRechazados = clone $base;
        $this->filtrarPorEstadoAprobacion(
            $consultaRechazados,
            'rechazado'
        );

        $operariosRechazados = $consultaRechazados->count();

        $tiposOperario = TipoOperario::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'operarios.index',
            compact(
                'operarios',
                'totalOperarios',
                'operariosActivos',
                'operariosInactivos',
                'operariosPendientes',
                'operariosRechazados',
                'tiposOperario'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PANEL GENERAL DE OBJETIVOS
    |--------------------------------------------------------------------------
    */

    public function objetivos()
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADÍSTICAS DE OPERARIOS APROBADOS
        |--------------------------------------------------------------------------
        */

        $baseAprobados = $this
            ->consultaOperariosVisibles($usuario);

        $this->filtrarAprobados($baseAprobados);

        $totalOperarios = (clone $baseAprobados)->count();

        $operariosActivos = (clone $baseAprobados)
            ->where('activo', true)
            ->count();

        $operariosInactivos = (clone $baseAprobados)
            ->where('activo', false)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | LISTA DE OPERARIOS DISPONIBLES PARA OBJETIVOS
        |--------------------------------------------------------------------------
        */

        $consultaOperarios = $this
            ->consultaOperariosVisibles($usuario)
            ->with([
                'empresa',
                'tipoOperario',
                'aprobacionActual',
            ])
            ->where('activo', true);

        $this->filtrarAprobados($consultaOperarios);

        $operarios = $consultaOperarios
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get();

        $idsOperarios = $operarios
            ->pluck('id_usuario')
            ->filter()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR OBJETIVOS VENCIDOS
        |--------------------------------------------------------------------------
        */

        if ($idsOperarios->isNotEmpty()) {
            ObjetivoOperario::query()
                ->whereIn(
                    'id_usuario',
                    $idsOperarios
                )
                ->whereIn(
                    'estado',
                    [
                        'pendiente',
                        'en_progreso',
                    ]
                )
                ->whereDate(
                    'fecha_fin',
                    '<',
                    now()->toDateString()
                )
                ->update([
                    'estado' => 'vencido',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | OBJETIVO ACTUAL POR OPERARIO
        |--------------------------------------------------------------------------
        */

        foreach ($operarios as $operario) {
            $objetivoActual = ObjetivoOperario::query()
                ->where(
                    'id_usuario',
                    $operario->id_usuario
                )
                ->whereIn(
                    'estado',
                    [
                        'pendiente',
                        'en_progreso',
                    ]
                )
                ->whereDate(
                    'fecha_fin',
                    '>=',
                    now()->toDateString()
                )
                ->latest('id_objetivo')
                ->first();

            $operario->objetivo_actual = $objetivoActual;
            $operario->situacion_objetivo = 'sin_objetivo';
            $operario->llamadas_realizadas = 0;

            if ($objetivoActual) {
                $llamadasRealizadas = DB::table('llamadas')
                    ->where(
                        'id_usuario',
                        $operario->id_usuario
                    )
                    ->whereDate(
                        'fecha_inicio',
                        '>=',
                        $objetivoActual->fecha_inicio
                    )
                    ->whereDate(
                        'fecha_inicio',
                        '<=',
                        $objetivoActual->fecha_fin
                    )
                    ->count();

                $operario->llamadas_realizadas =
                    $llamadasRealizadas;

                if (
                    $llamadasRealizadas >=
                    (int) $objetivoActual->objetivo_llamadas
                ) {
                    $operario->situacion_objetivo =
                        'cumplido';

                    if (
                        $objetivoActual->estado !==
                        'cumplido'
                    ) {
                        $objetivoActual->update([
                            'estado' => 'cumplido',
                        ]);
                    }
                } elseif (
                    $objetivoActual->estado === 'pendiente' ||
                    Carbon::parse(
                        $objetivoActual->fecha_inicio
                    )->isFuture()
                ) {
                    $operario->situacion_objetivo =
                        'pendiente';
                } else {
                    $fechaFin = Carbon::parse(
                        $objetivoActual->fecha_fin
                    )->startOfDay();

                    $diasRestantes = now()
                        ->startOfDay()
                        ->diffInDays(
                            $fechaFin,
                            false
                        );

                    $operario->situacion_objetivo =
                        $diasRestantes <= 2
                            ? 'proximo_vencer'
                            : 'en_progreso';
                }
            }
        }

        $operariosConObjetivo = $operarios
            ->filter(
                fn ($operario) =>
                $operario->objetivo_actual !== null
            )
            ->count();

        $operariosSinObjetivo =
            $operariosActivos -
            $operariosConObjetivo;

        $objetivosProximosVencer = $operarios
            ->where(
                'situacion_objetivo',
                'proximo_vencer'
            )
            ->count();

        return view(
            'operarios.objetivos',
            compact(
                'operarios',
                'totalOperarios',
                'operariosActivos',
                'operariosInactivos',
                'operariosConObjetivo',
                'operariosSinObjetivo',
                'objetivosProximosVencer'
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
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $tiposOperario = TipoOperario::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'operarios.create',
            compact('tiposOperario')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREAR OPERARIO / GENERAR SOLICITUD
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $datos = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:150',
                    Rule::unique(
                        'usuarios',
                        'correo'
                    )->whereNull('deleted_at'),
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/[A-Z]/',
                    'regex:/[a-z]/',
                    'regex:/[0-9]/',
                    'regex:/[^A-Za-z0-9]/',
                    'not_regex:/\s/',
                ],

                'id_tipo_operario' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'tipos_operario',
                        'id_tipo_operario'
                    )->where(
                        fn ($query) =>
                        $query->where(
                            'activo',
                            true
                        )
                    ),
                ],

                'id_empresa' => [
                    'nullable',
                    'integer',
                    'exists:empresas,id_empresa',
                ],
            ],
            [
                'name.required' =>
                    'El nombre del operario es obligatorio.',

                'email.required' =>
                    'El correo electrónico es obligatorio.',

                'email.email' =>
                    'Ingresa un correo electrónico válido.',

                'email.unique' =>
                    'Ya existe un usuario registrado con este correo.',

                'password.required' =>
                    'La contraseña es obligatoria.',

                'password.min' =>
                    'La contraseña debe tener al menos 8 caracteres.',

                'password.confirmed' =>
                    'La confirmación de contraseña no coincide.',

                'id_tipo_operario.required' =>
                    'Debes seleccionar un tipo de operario.',

                'id_tipo_operario.exists' =>
                    'El tipo de operario seleccionado no es válido.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | EMPRESA DEL OPERARIO
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 2) {
            if (!$usuario->id_empresa) {
                return back()
                    ->withErrors([
                        'id_empresa' =>
                            'Tu cuenta no tiene una empresa asociada.',
                    ])
                    ->withInput();
            }

            $datos['id_empresa'] =
                $usuario->id_empresa;
        }

        /*
        |--------------------------------------------------------------------------
        | El nuevo esquema exige id_empresa para todos los usuarios.
        |--------------------------------------------------------------------------
        */

        $idEmpresa =
            $datos['id_empresa']
            ?? $usuario->id_empresa;

        if (!$idEmpresa) {
            return back()
                ->withErrors([
                    'id_empresa' =>
                        'Debes seleccionar una empresa para el operario.',
                ])
                ->withInput();
        }

        $operario = DB::transaction(
            function () use (
                $datos,
                $usuario,
                $idEmpresa
            ) {
                $operario = new User();

                /*
                |--------------------------------------------------------------------------
                | Estos alias siguen funcionando gracias a User.php:
                |
                | name     -> nombre/apellidos
                | email    -> correo
                | password -> password_hash
                |--------------------------------------------------------------------------
                */

                $operario->name =
                    $datos['name'];

                $operario->email =
                    mb_strtolower(
                        trim($datos['email']),
                        'UTF-8'
                    );

                $operario->password =
                    $datos['password'];

                $operario->id_rol = 3;
                $operario->id_empresa =
                    (int) $idEmpresa;

                $operario->id_tipo_operario =
                    (int) $datos['id_tipo_operario'];

                /*
                |--------------------------------------------------------------------------
                | Admin Cliente: nace inactivo hasta aprobación.
                | Super Admin: nace activo y aprobado.
                |--------------------------------------------------------------------------
                */

                $operario->activo =
                    (int) $usuario->id_rol === 1;

                $operario->save();

                if ((int) $usuario->id_rol === 2) {
                    AprobacionUsuario::create([
                        'id_usuario' =>
                            $operario->id_usuario,

                        'estado' =>
                            'pendiente',

                        'solicitado_por' =>
                            $usuario->id_usuario,

                        'fecha_solicitud' =>
                            now(),

                        'revisado_por' =>
                            null,

                        'fecha_revision' =>
                            null,

                        'motivo_rechazo' =>
                            null,
                    ]);
                } else {
                    AprobacionUsuario::create([
                        'id_usuario' =>
                            $operario->id_usuario,

                        'estado' =>
                            'aprobado',

                        'solicitado_por' =>
                            $usuario->id_usuario,

                        'fecha_solicitud' =>
                            now(),

                        'revisado_por' =>
                            $usuario->id_usuario,

                        'fecha_revision' =>
                            now(),

                        'motivo_rechazo' =>
                            null,
                    ]);
                }

                return $operario;
            }
        );

        if ((int) $usuario->id_rol === 2) {
            return redirect()
                ->route('operarios.index')
                ->with(
                    'success',
                    'Solicitud enviada correctamente. El nuevo operario deberá ser aprobado por un Super Administrador antes de poder acceder al sistema.'
                );
        }

        return redirect()
            ->route('operarios.index')
            ->with(
                'success',
                'Operario registrado y aprobado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SOLICITUDES DE OPERARIOS
    |--------------------------------------------------------------------------
    */

    public function solicitudes(Request $request)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 1
        ) {
            abort(403);
        }

        $consulta = User::query()
            ->with([
                'empresa',
                'tipoOperario',
                'aprobacionActual',
            ])
            ->where('id_rol', 3)
            ->whereHas(
                'aprobacionActual',
                fn ($query) =>
                $query->where(
                    'estado',
                    'pendiente'
                )
            );

        if ($request->filled('buscar')) {
            $buscar = trim(
                (string) $request->buscar
            );

            $termino =
                '%' .
                mb_strtolower(
                    $buscar,
                    'UTF-8'
                ) .
                '%';

            $consulta->where(
                function ($query) use ($termino) {
                    $query
                        ->whereRaw(
                            "LOWER(nombre) LIKE ?",
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(apellido_paterno) LIKE ?",
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(COALESCE(apellido_materno, '')) LIKE ?",
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(correo) LIKE ?",
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(
                                CONCAT_WS(
                                    ' ',
                                    nombre,
                                    apellido_paterno,
                                    apellido_materno
                                )
                            ) LIKE ?",
                            [$termino]
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ordenar por la fecha de la aprobación más reciente.
        |--------------------------------------------------------------------------
        */

        $solicitudes = $consulta
            ->orderByRaw(
                "(
                    SELECT au.fecha_solicitud
                    FROM aprobaciones_usuario au
                    WHERE au.id_usuario =
                        usuarios.id_usuario
                    ORDER BY au.id_aprobacion DESC
                    LIMIT 1
                ) ASC"
            )
            ->paginate(10)
            ->withQueryString();

        $idsSolicitantes = $solicitudes
            ->getCollection()
            ->pluck('solicitado_por')
            ->filter()
            ->unique()
            ->values();

        $solicitantes = User::query()
            ->whereIn(
                'id_usuario',
                $idsSolicitantes
            )
            ->get()
            ->keyBy('id_usuario');

        return view(
            'operarios.solicitudes',
            compact(
                'solicitudes',
                'solicitantes'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | APROBAR SOLICITUD
    |--------------------------------------------------------------------------
    */

    public function aprobarSolicitud($id)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 1
        ) {
            abort(403);
        }

        $operario = User::query()
            ->with('aprobacionActual')
            ->where('id_rol', 3)
            ->findOrFail($id);

        if (!$operario->id_tipo_operario) {
            return back()
                ->withErrors([
                    'operario' =>
                        'No se puede aprobar este operario porque no tiene un tipo de operario asignado.',
                ]);
        }

        $aprobacion =
            $operario->aprobacionActual;

        if (
            $aprobacion &&
            $aprobacion->estado === 'aprobado'
        ) {
            return back()
                ->with(
                    'success',
                    'Este operario ya se encuentra aprobado.'
                );
        }

        DB::transaction(
            function () use (
                $operario,
                $usuario,
                $aprobacion
            ) {
                if ($aprobacion) {
                    $aprobacion->update([
                        'estado' =>
                            'aprobado',

                        'revisado_por' =>
                            $usuario->id_usuario,

                        'fecha_revision' =>
                            now(),

                        'motivo_rechazo' =>
                            null,
                    ]);
                } else {
                    AprobacionUsuario::create([
                        'id_usuario' =>
                            $operario->id_usuario,

                        'estado' =>
                            'aprobado',

                        'solicitado_por' =>
                            $usuario->id_usuario,

                        'fecha_solicitud' =>
                            now(),

                        'revisado_por' =>
                            $usuario->id_usuario,

                        'fecha_revision' =>
                            now(),

                        'motivo_rechazo' =>
                            null,
                    ]);
                }

                $operario->activo = true;
                $operario->save();
            }
        );

        return back()
            ->with(
                'success',
                'Solicitud aprobada correctamente. El operario ya puede acceder al sistema.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | RECHAZAR SOLICITUD
    |--------------------------------------------------------------------------
    */

    public function rechazarSolicitud(
        Request $request,
        $id
    ) {
        $usuario = auth()->user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 1
        ) {
            abort(403);
        }

        $datos = $request->validate(
            [
                'motivo_rechazo' => [
                    'required',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'motivo_rechazo.required' =>
                    'Debes indicar el motivo del rechazo.',

                'motivo_rechazo.max' =>
                    'El motivo no puede exceder 1000 caracteres.',
            ]
        );

        $operario = User::query()
            ->with('aprobacionActual')
            ->where('id_rol', 3)
            ->findOrFail($id);

        $aprobacion =
            $operario->aprobacionActual;

        DB::transaction(
            function () use (
                $operario,
                $usuario,
                $aprobacion,
                $datos
            ) {
                if ($aprobacion) {
                    $aprobacion->update([
                        'estado' =>
                            'rechazado',

                        'revisado_por' =>
                            $usuario->id_usuario,

                        'fecha_revision' =>
                            now(),

                        'motivo_rechazo' =>
                            $datos['motivo_rechazo'],
                    ]);
                } else {
                    AprobacionUsuario::create([
                        'id_usuario' =>
                            $operario->id_usuario,

                        'estado' =>
                            'rechazado',

                        'solicitado_por' =>
                            $usuario->id_usuario,

                        'fecha_solicitud' =>
                            now(),

                        'revisado_por' =>
                            $usuario->id_usuario,

                        'fecha_revision' =>
                            now(),

                        'motivo_rechazo' =>
                            $datos['motivo_rechazo'],
                    ]);
                }

                $operario->activo = false;
                $operario->save();
            }
        );

        return back()
            ->with(
                'success',
                'La solicitud del operario fue rechazada.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | REENVIAR SOLICITUD
    |--------------------------------------------------------------------------
    */

    public function reenviarSolicitud($id)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $operario = $this
            ->consultaOperariosVisibles($usuario)
            ->with('aprobacionActual')
            ->findOrFail($id);

        if (
            $operario->estado_aprobacion ===
            'aprobado'
        ) {
            return back()
                ->withErrors([
                    'operario' =>
                        'El operario ya se encuentra aprobado.',
                ]);
        }

        if (
            $operario->estado_aprobacion ===
            'pendiente'
        ) {
            return back()
                ->with(
                    'success',
                    'La solicitud de este operario ya se encuentra pendiente de revisión.'
                );
        }

        if (!$operario->id_tipo_operario) {
            return back()
                ->withErrors([
                    'operario' =>
                        'Debes asignar un tipo de operario antes de reenviar la solicitud.',
                ]);
        }

        DB::transaction(
            function () use (
                $operario,
                $usuario
            ) {
                $operario->activo = false;
                $operario->save();

                AprobacionUsuario::create([
                    'id_usuario' =>
                        $operario->id_usuario,

                    'estado' =>
                        'pendiente',

                    'solicitado_por' =>
                        $usuario->id_usuario,

                    'fecha_solicitud' =>
                        now(),

                    'revisado_por' =>
                        null,

                    'fecha_revision' =>
                        null,

                    'motivo_rechazo' =>
                        null,
                ]);
            }
        );

        return back()
            ->with(
                'success',
                'La solicitud fue enviada nuevamente al Super Administrador.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | INFORMACIÓN DEL OPERARIO
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $operario = $this
            ->consultaOperariosVisibles($usuario)
            ->with([
                'empresa',
                'tipoOperario',
                'aprobacionActual',
            ])
            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | VENCIMIENTO AUTOMÁTICO
        |--------------------------------------------------------------------------
        */

        ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id_usuario
            )
            ->whereIn(
                'estado',
                [
                    'pendiente',
                    'en_progreso',
                ]
            )
            ->whereDate(
                'fecha_fin',
                '<',
                now()->toDateString()
            )
            ->update([
                'estado' => 'vencido',
            ]);

        /*
        |--------------------------------------------------------------------------
        | OBJETIVO ACTUAL
        |--------------------------------------------------------------------------
        */

        $objetivoActual = null;
        $llamadasRealizadas = 0;

        if (
            $this->operarioEstaAprobado(
                $operario
            ) &&
            $operario->activo
        ) {
            $objetivoActual = ObjetivoOperario::query()
                ->where(
                    'id_usuario',
                    $operario->id_usuario
                )
                ->whereIn(
                    'estado',
                    [
                        'pendiente',
                        'en_progreso',
                    ]
                )
                ->whereDate(
                    'fecha_inicio',
                    '<=',
                    now()->toDateString()
                )
                ->whereDate(
                    'fecha_fin',
                    '>=',
                    now()->toDateString()
                )
                ->latest('id_objetivo')
                ->first();

            if ($objetivoActual) {
                $llamadasRealizadas = DB::table(
                    'llamadas'
                )
                    ->where(
                        'id_usuario',
                        $operario->id_usuario
                    )
                    ->whereDate(
                        'fecha_inicio',
                        '>=',
                        $objetivoActual->fecha_inicio
                    )
                    ->whereDate(
                        'fecha_inicio',
                        '<=',
                        $objetivoActual->fecha_fin
                    )
                    ->count();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SITUACIÓN
        |--------------------------------------------------------------------------
        */

        $situacionObjetivo = null;

        if ($objetivoActual) {
            $objetivo =
                (int) $objetivoActual
                    ->objetivo_llamadas;

            if (
                $llamadasRealizadas >=
                $objetivo
            ) {
                $situacionObjetivo =
                    'cumplido';

                if (
                    $objetivoActual->estado !==
                    'cumplido'
                ) {
                    $objetivoActual->update([
                        'estado' =>
                            'cumplido',
                    ]);
                }
            } else {
                $hoy =
                    now()->startOfDay();

                $fechaFin =
                    Carbon::parse(
                        $objetivoActual
                            ->fecha_fin
                    )->startOfDay();

                $diasRestantes =
                    $hoy->diffInDays(
                        $fechaFin,
                        false
                    );

                $situacionObjetivo =
                    $diasRestantes <= 2
                        ? 'proximo_vencer'
                        : 'en_progreso';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ÚLTIMO OBJETIVO VENCIDO
        |--------------------------------------------------------------------------
        */

        $objetivoVencido = ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id_usuario
            )
            ->where(
                'estado',
                'vencido'
            )
            ->whereDate(
                'fecha_fin',
                '<',
                now()->toDateString()
            )
            ->latest('fecha_fin')
            ->first();

        if (
            !$objetivoActual &&
            $objetivoVencido
        ) {
            $situacionObjetivo =
                'vencido';
        }

        /*
        |--------------------------------------------------------------------------
        | HISTORIAL
        |--------------------------------------------------------------------------
        */

        $objetivos = ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id_usuario
            )
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id_objetivo')
            ->get();

        return view(
            'operarios.show',
            compact(
                'operario',
                'objetivoActual',
                'objetivoVencido',
                'objetivos',
                'llamadasRealizadas',
                'situacionObjetivo'
            )
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

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $operario = $this
            ->consultaOperariosVisibles($usuario)
            ->with([
                'tipoOperario',
                'aprobacionActual',
            ])
            ->findOrFail($id);

        $tiposOperario = TipoOperario::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'operarios.edit',
            compact(
                'operario',
                'tiposOperario'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR OPERARIO
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $operario = $this
            ->consultaOperariosVisibles($usuario)
            ->with('aprobacionActual')
            ->findOrFail($id);

        $datos = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:150',

                    Rule::unique(
                        'usuarios',
                        'correo'
                    )
                        ->whereNull(
                            'deleted_at'
                        )
                        ->ignore(
                            $operario->id_usuario,
                            'id_usuario'
                        ),
                ],

                'id_tipo_operario' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'tipos_operario',
                        'id_tipo_operario'
                    )->where(
                        fn ($query) =>
                        $query->where(
                            'activo',
                            true
                        )
                    ),
                ],

                'current_password' => [
                    'nullable',
                    'string',
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/[A-Z]/',
                    'regex:/[a-z]/',
                    'regex:/[0-9]/',
                    'regex:/[^A-Za-z0-9]/',
                    'not_regex:/\s/',
                ],
            ],
            [
                'id_tipo_operario.required' =>
                    'Debes seleccionar un tipo de operario.',

                'id_tipo_operario.exists' =>
                    'El tipo de operario seleccionado no es válido.',
            ]
        );

        $cambioTipo =
            (int) $operario->id_tipo_operario
            !==
            (int) $datos['id_tipo_operario'];

        /*
        |--------------------------------------------------------------------------
        | CONTRASEÑA
        |--------------------------------------------------------------------------
        */

        if (!empty($datos['password'])) {
            if (
                empty(
                    $datos['current_password']
                )
            ) {
                return back()
                    ->withErrors([
                        'current_password' =>
                            'Debes ingresar la contraseña actual para cambiarla.',
                    ])
                    ->withInput();
            }

            if (
                !Hash::check(
                    $datos['current_password'],
                    $operario->password_hash
                )
            ) {
                return back()
                    ->withErrors([
                        'current_password' =>
                            'La contraseña actual es incorrecta.',
                    ])
                    ->withInput();
            }
        }

        $requiereNuevaAprobacion =
            (int) $usuario->id_rol === 2 &&
            $cambioTipo &&
            $operario->estado_aprobacion ===
                'aprobado';

        DB::transaction(
            function () use (
                $operario,
                $usuario,
                $datos,
                $requiereNuevaAprobacion
            ) {
                $operario->name =
                    $datos['name'];

                $operario->email =
                    mb_strtolower(
                        trim($datos['email']),
                        'UTF-8'
                    );

                $operario->id_tipo_operario =
                    (int)
                    $datos['id_tipo_operario'];

                if (!empty($datos['password'])) {
                    $operario->password =
                        $datos['password'];
                }

                if ($requiereNuevaAprobacion) {
                    $operario->activo = false;
                }

                $operario->save();

                if ($requiereNuevaAprobacion) {
                    AprobacionUsuario::create([
                        'id_usuario' =>
                            $operario->id_usuario,

                        'estado' =>
                            'pendiente',

                        'solicitado_por' =>
                            $usuario->id_usuario,

                        'fecha_solicitud' =>
                            now(),

                        'revisado_por' =>
                            null,

                        'fecha_revision' =>
                            null,

                        'motivo_rechazo' =>
                            null,
                    ]);
                }
            }
        );

        if ($requiereNuevaAprobacion) {
            return redirect()
                ->route(
                    'operarios.show',
                    $operario
                )
                ->with(
                    'success',
                    'Operario actualizado. El cambio de tipo fue enviado al Super Administrador para su aprobación.'
                );
        }

        return redirect()
            ->route(
                'operarios.show',
                $operario
            )
            ->with(
                'success',
                'Operario actualizado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ASIGNAR OBJETIVO
    |--------------------------------------------------------------------------
    */

    public function asignarObjetivo(
        Request $request,
        $id
    ) {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $datos = $request->validate([
            'objetivo_llamadas' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],

            'periodo' => [
                'required',

                Rule::in([
                    'semanal',
                    'mensual',
                    'personalizado',
                ]),
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
        ]);

        $operario = $this
            ->consultaOperariosVisibles($usuario)
            ->with('aprobacionActual')
            ->findOrFail($id);

        if (
            !$this->operarioEstaAprobado(
                $operario
            )
        ) {
            return back()
                ->withErrors([
                    'operario' =>
                        'No puedes asignar objetivos a un operario que todavía no ha sido aprobado.',
                ]);
        }

        if (!$operario->activo) {
            return back()
                ->withErrors([
                    'operario' =>
                        'No puedes asignar objetivos a un operario inactivo.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CERRAR OBJETIVO ANTERIOR
        |--------------------------------------------------------------------------
        */

        ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id_usuario
            )
            ->whereIn(
                'estado',
                [
                    'pendiente',
                    'en_progreso',
                ]
            )
            ->update([
                'estado' =>
                    'vencido',
            ]);

        $fechaInicio = Carbon::parse(
            $datos['fecha_inicio']
        )->startOfDay();

        $hoy = now()->startOfDay();

        $estadoInicial =
            $fechaInicio->greaterThan($hoy)
                ? 'pendiente'
                : 'en_progreso';

        ObjetivoOperario::create([
            'id_usuario' =>
                $operario->id_usuario,

            'objetivo_llamadas' =>
                $datos['objetivo_llamadas'],

            'periodo' =>
                $datos['periodo'],

            'fecha_inicio' =>
                $datos['fecha_inicio'],

            'fecha_fin' =>
                $datos['fecha_fin'],

            'estado' =>
                $estadoInicial,
        ]);

        return redirect()
            ->route(
                'operarios.show',
                $operario
            )
            ->with(
                'success',
                'Objetivo de llamadas asignado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR
    |--------------------------------------------------------------------------
    */

    public function toggleEstado($id)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $operario = $this
            ->consultaOperariosVisibles($usuario)
            ->with('aprobacionActual')
            ->findOrFail($id);

        if (
            !$this->operarioEstaAprobado(
                $operario
            )
        ) {
            return back()
                ->withErrors([
                    'operario' =>
                        'Este operario todavía no ha sido aprobado por un Super Administrador.',
                ]);
        }

        if (!$operario->id_tipo_operario) {
            return back()
                ->withErrors([
                    'operario' =>
                        'Debes asignar un tipo de operario antes de activar esta cuenta.',
                ]);
        }

        $operario->activo =
            !$operario->activo;

        $operario->save();

        return back()
            ->with(
                'success',
                $operario->activo
                    ? 'Operario activado correctamente.'
                    : 'Operario desactivado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR OPERARIO
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array((int) $usuario->id_rol, [1, 2], true)
        ) {
            abort(403);
        }

        $operario = $this
            ->consultaOperariosVisibles($usuario)
            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | User utiliza SoftDeletes porque usuarios tiene deleted_at.
        |--------------------------------------------------------------------------
        */

        $operario->delete();

        return redirect()
            ->route('operarios.index')
            ->with(
                'success',
                'Operario eliminado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MÉTODOS INTERNOS
    |--------------------------------------------------------------------------
    */

    private function consultaOperariosVisibles(
        User $usuario
    ) {
        $consulta = User::query()
            ->where('id_rol', 3);

        if ((int) $usuario->id_rol === 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        return $consulta;
    }


    private function filtrarAprobados(
        $consulta
    ) {
        return $consulta->whereHas(
            'aprobacionActual',
            fn ($query) =>
            $query->where(
                'estado',
                'aprobado'
            )
        );
    }


    private function filtrarPorEstadoAprobacion(
        $consulta,
        string $estado
    ) {
        return $consulta->whereHas(
            'aprobacionActual',
            fn ($query) =>
            $query->where(
                'estado',
                $estado
            )
        );
    }


    private function operarioEstaAprobado(
        User $operario
    ): bool {
        return
            $operario->estado_aprobacion ===
            'aprobado';
    }
}
