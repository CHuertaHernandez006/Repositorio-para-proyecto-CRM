<?php

namespace App\Http\Controllers;

use App\Models\ObjetivoOperario;
use App\Models\TipoOperario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            !in_array($usuario->id_rol, [1, 2])
        ) {
            abort(403);
        }

        $consulta = $this
            ->consultaOperariosVisibles($usuario)
            ->with([
                'empresa',
                'tipoOperario',
            ]);

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);

            $consulta->where(function ($query) use ($buscar) {
                $query
                    ->where(
                        'name',
                        'like',
                        '%' . $buscar . '%'
                    )
                    ->orWhere(
                        'email',
                        'like',
                        '%' . $buscar . '%'
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

                    $consulta->where(
                        'estado',
                        true
                    );

                    $this->filtrarAprobados(
                        $consulta
                    );

                    break;

                case 'inactivo':

                    $consulta->where(
                        'estado',
                        false
                    );

                    $this->filtrarAprobados(
                        $consulta
                    );

                    break;

                case 'pendiente':

                    $consulta->where(
                        'estado_aprobacion',
                        'pendiente'
                    );

                    break;

                case 'rechazado':

                    $consulta->where(
                        'estado_aprobacion',
                        'rechazado'
                    );

                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR TIPO DE OPERARIO
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tipo_operario')) {
            $consulta->where(
                'id_tipo_operario',
                $request->tipo_operario
            );
        }

        $operarios = $consulta
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | ESTADÍSTICAS
        |--------------------------------------------------------------------------
        */

        $base = $this
            ->consultaOperariosVisibles(
                $usuario
            );

        $totalOperarios =
            (clone $base)->count();

        $consultaActivos =
            clone $base;

        $this->filtrarAprobados(
            $consultaActivos
        );

        $operariosActivos =
            $consultaActivos
                ->where('estado', true)
                ->count();

        $consultaInactivos =
            clone $base;

        $this->filtrarAprobados(
            $consultaInactivos
        );

        $operariosInactivos =
            $consultaInactivos
                ->where('estado', false)
                ->count();

        $operariosPendientes =
            (clone $base)
                ->where(
                    'estado_aprobacion',
                    'pendiente'
                )
                ->count();

        $operariosRechazados =
            (clone $base)
                ->where(
                    'estado_aprobacion',
                    'rechazado'
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | TIPOS PARA FILTROS DE LA VISTA
        |--------------------------------------------------------------------------
        */

        $tiposOperario =
            TipoOperario::query()
                ->where('estado', true)
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
            !in_array($usuario->id_rol, [1, 2])
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | SOLO OPERARIOS APROBADOS Y ACTIVOS
        |--------------------------------------------------------------------------
        */

        $consultaOperarios = $this
            ->consultaOperariosVisibles(
                $usuario
            )
            ->with([
                'empresa',
                'tipoOperario',
            ])
            ->where(
                'estado',
                true
            );

        $this->filtrarAprobados(
            $consultaOperarios
        );

        $operarios = $consultaOperarios
            ->orderBy('name')
            ->get();

        $idsOperarios =
            $operarios->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR OBJETIVOS VENCIDOS
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | OBJETIVO ACTUAL DE CADA OPERARIO
        |--------------------------------------------------------------------------
        */

        foreach ($operarios as $operario) {
            $objetivoActual =
                ObjetivoOperario::query()
                    ->where(
                        'id_usuario',
                        $operario->id
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
                    ->latest(
                        'id_objetivo'
                    )
                    ->first();

            $operario->objetivo_actual =
                $objetivoActual;

            $operario->situacion_objetivo =
                'sin_objetivo';

            if ($objetivoActual) {
                if (
                    $objetivoActual->estado === 'pendiente' ||
                    Carbon::parse(
                        $objetivoActual->fecha_inicio
                    )->isFuture()
                ) {
                    $operario->situacion_objetivo =
                        'pendiente';
                } else {
                    $fechaFin =
                        Carbon::parse(
                            $objetivoActual->fecha_fin
                        )->startOfDay();

                    $diasRestantes =
                        now()
                            ->startOfDay()
                            ->diffInDays(
                                $fechaFin,
                                false
                            );

                    if ($diasRestantes <= 2) {
                        $operario->situacion_objetivo =
                            'proximo_vencer';
                    } else {
                        $operario->situacion_objetivo =
                            'en_progreso';
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADÍSTICAS
        |--------------------------------------------------------------------------
        */

        $totalOperarios =
            $operarios->count();

        $operariosActivos =
            $operarios
                ->where(
                    'estado',
                    true
                )
                ->count();

        $operariosInactivos =
            $operarios
                ->where(
                    'estado',
                    false
                )
                ->count();

        $operariosConObjetivo =
            $operarios
                ->filter(function ($operario) {
                    return
                        $operario
                            ->objetivo_actual
                        !== null;
                })
                ->count();

        $operariosSinObjetivo =
            $totalOperarios -
            $operariosConObjetivo;

        $objetivosProximosVencer =
            $operarios
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
            !in_array(
                $usuario->id_rol,
                [1, 2]
            )
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | TIPOS DE OPERARIO DISPONIBLES
        |--------------------------------------------------------------------------
        */

        $tiposOperario =
            TipoOperario::query()
                ->where(
                    'estado',
                    true
                )
                ->orderBy(
                    'nombre'
                )
                ->get();

        return view(
            'operarios.create',
            compact(
                'tiposOperario'
            )
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
            !in_array(
                $usuario->id_rol,
                [1, 2]
            )
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
                    'unique:users,email',
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

                /*
                |--------------------------------------------------------------------------
                | TIPO DE OPERARIO
                |--------------------------------------------------------------------------
                */

                'id_tipo_operario' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'tipo_operarios',
                        'id_tipo_operario'
                    )->where(
                        function ($query) {
                            $query->where(
                                'estado',
                                true
                            );
                        }
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
        | ADMIN CLIENTE
        |--------------------------------------------------------------------------
        */

        if ($usuario->id_rol == 2) {
            if (!$usuario->id_empresa) {
                return back()
                    ->withErrors([
                        'id_empresa' =>
                            'Tu cuenta no tiene una empresa asociada.',
                    ])
                    ->withInput();
            }

            /*
            |--------------------------------------------------------------------------
            | El operario siempre pertenece a la misma empresa
            | que el Admin Cliente que realiza la solicitud.
            |--------------------------------------------------------------------------
            */

            $datos['id_empresa'] =
                $usuario->id_empresa;
        }

        /*
        |--------------------------------------------------------------------------
        | CREAR USUARIO
        |--------------------------------------------------------------------------
        */

        $operario =
            new User();

        $operario->name =
            $datos['name'];

        $operario->email =
            $datos['email'];

        $operario->password =
            Hash::make(
                $datos['password']
            );

        $operario->id_rol =
            3;

        $operario->id_empresa =
            $datos['id_empresa']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | TIPO DE OPERARIO
        |--------------------------------------------------------------------------
        */

        $operario->id_tipo_operario =
            $datos['id_tipo_operario'];

        /*
        |--------------------------------------------------------------------------
        | ADMIN CLIENTE
        |--------------------------------------------------------------------------
        |
        | Genera una solicitud.
        |--------------------------------------------------------------------------
        */

        if ($usuario->id_rol == 2) {
            $operario->estado =
                false;

            $operario->estado_aprobacion =
                'pendiente';

            $operario->solicitado_por =
                $usuario->id;

            $operario->fecha_solicitud =
                now();

            $operario->revisado_por =
                null;

            $operario->fecha_revision =
                null;

            $operario->motivo_rechazo =
                null;
        }

        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        |
        | Si en algún momento el Super Admin crea directamente
        | un operario, queda aprobado automáticamente.
        |--------------------------------------------------------------------------
        */

        if ($usuario->id_rol == 1) {
            $operario->estado =
                true;

            $operario->estado_aprobacion =
                'aprobado';

            $operario->solicitado_por =
                $usuario->id;

            $operario->fecha_solicitud =
                now();

            $operario->revisado_por =
                $usuario->id;

            $operario->fecha_revision =
                now();

            $operario->motivo_rechazo =
                null;
        }

        $operario->save();

        /*
        |--------------------------------------------------------------------------
        | RESPUESTA
        |--------------------------------------------------------------------------
        */

        if ($usuario->id_rol == 2) {
            return redirect()
                ->route(
                    'operarios.index'
                )
                ->with(
                    'success',
                    'Solicitud enviada correctamente. El nuevo operario deberá ser aprobado por un Super Administrador antes de poder acceder al sistema.'
                );
        }

        return redirect()
            ->route(
                'operarios.index'
            )
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

    public function solicitudes(
        Request $request
    ) {
        $usuario = auth()->user();

        if (
            !$usuario ||
            $usuario->id_rol != 1
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Incluimos empresa y tipo de operario.
        |--------------------------------------------------------------------------
        */

        $consulta =
            User::query()
                ->with([
                    'empresa',
                    'tipoOperario',
                ])
                ->where(
                    'id_rol',
                    3
                )
                ->where(
                    'estado_aprobacion',
                    'pendiente'
                );

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'buscar'
            )
        ) {
            $buscar =
                trim(
                    $request->buscar
                );

            $consulta->where(
                function ($query) use ($buscar) {
                    $query
                        ->where(
                            'name',
                            'like',
                            '%' . $buscar . '%'
                        )
                        ->orWhere(
                            'email',
                            'like',
                            '%' . $buscar . '%'
                        );
                }
            );
        }

        $solicitudes =
            $consulta
                ->orderBy(
                    'fecha_solicitud',
                    'asc'
                )
                ->paginate(10)
                ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | USUARIOS QUE REALIZARON LA SOLICITUD
        |--------------------------------------------------------------------------
        */

        $idsSolicitantes =
            $solicitudes
                ->getCollection()
                ->pluck(
                    'solicitado_por'
                )
                ->filter()
                ->unique();

        $solicitantes =
            User::query()
                ->whereIn(
                    'id',
                    $idsSolicitantes
                )
                ->get()
                ->keyBy('id');

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
            $usuario->id_rol != 1
        ) {
            abort(403);
        }

        $operario =
            User::query()
                ->where(
                    'id_rol',
                    3
                )
                ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | EVITAR APROBACIÓN SIN TIPO
        |--------------------------------------------------------------------------
        */

        if (
            !$operario->id_tipo_operario
        ) {
            return back()
                ->withErrors([
                    'operario' =>
                        'No se puede aprobar este operario porque no tiene un tipo de operario asignado.',
                ]);
        }

        if (
            $operario->estado_aprobacion
            === 'aprobado'
        ) {
            return back()
                ->with(
                    'success',
                    'Este operario ya se encuentra aprobado.'
                );
        }

        $operario->estado_aprobacion =
            'aprobado';

        $operario->estado =
            true;

        $operario->revisado_por =
            $usuario->id;

        $operario->fecha_revision =
            now();

        $operario->motivo_rechazo =
            null;

        $operario->save();

        return redirect()
            ->back()
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
            $usuario->id_rol != 1
        ) {
            abort(403);
        }

        $datos =
            $request->validate(
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

        $operario =
            User::query()
                ->where(
                    'id_rol',
                    3
                )
                ->findOrFail($id);

        $operario->estado_aprobacion =
            'rechazado';

        $operario->estado =
            false;

        $operario->revisado_por =
            $usuario->id;

        $operario->fecha_revision =
            now();

        $operario->motivo_rechazo =
            $datos[
                'motivo_rechazo'
            ];

        $operario->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'La solicitud del operario fue rechazada.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | REENVIAR SOLICITUD RECHAZADA
    |--------------------------------------------------------------------------
    */

    public function reenviarSolicitud($id)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array(
                $usuario->id_rol,
                [1, 2]
            )
        ) {
            abort(403);
        }

        $operario =
            $this
                ->consultaOperariosVisibles(
                    $usuario
                )
                ->findOrFail($id);

        if (
            $operario->estado_aprobacion
            === 'aprobado'
        ) {
            return back()
                ->withErrors([
                    'operario' =>
                        'El operario ya se encuentra aprobado.',
                ]);
        }

        if (
            !$operario->id_tipo_operario
        ) {
            return back()
                ->withErrors([
                    'operario' =>
                        'Debes asignar un tipo de operario antes de reenviar la solicitud.',
                ]);
        }

        $operario->estado_aprobacion =
            'pendiente';

        $operario->estado =
            false;

        $operario->solicitado_por =
            $usuario->id;

        $operario->fecha_solicitud =
            now();

        $operario->revisado_por =
            null;

        $operario->fecha_revision =
            null;

        $operario->motivo_rechazo =
            null;

        $operario->save();

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

        if (!$usuario) {
            abort(403);
        }

        $operario =
            $this
                ->consultaOperariosVisibles(
                    $usuario
                )
                ->with([
                    'empresa',
                    'tipoOperario',
                ])
                ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | VENCIMIENTO AUTOMÁTICO DE OBJETIVOS
        |--------------------------------------------------------------------------
        */

        ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id
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
        | LLAMADAS REALIZADAS
        |--------------------------------------------------------------------------
        */

        $llamadasRealizadas = 0;

        /*
        |--------------------------------------------------------------------------
        | OBJETIVO ACTUAL
        |--------------------------------------------------------------------------
        */

        $objetivoActual =
            null;

        if (
            $this->operarioEstaAprobado(
                $operario
            ) &&
            $operario->estado
        ) {
            $objetivoActual =
                ObjetivoOperario::query()
                    ->where(
                        'id_usuario',
                        $operario->id
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
                    ->latest(
                        'id_objetivo'
                    )
                    ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | SITUACIÓN DEL OBJETIVO
        |--------------------------------------------------------------------------
        */

        $situacionObjetivo =
            null;

        if ($objetivoActual) {
            $objetivo =
                (int)
                $objetivoActual
                    ->objetivo_llamadas;

            if (
                $llamadasRealizadas >=
                $objetivo
            ) {
                $situacionObjetivo =
                    'cumplido';

                if (
                    $objetivoActual->estado
                    !== 'cumplido'
                ) {
                    $objetivoActual
                        ->update([
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

                if (
                    $diasRestantes <= 2
                ) {
                    $situacionObjetivo =
                        'proximo_vencer';
                } else {
                    $situacionObjetivo =
                        'en_progreso';
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ÚLTIMO OBJETIVO VENCIDO
        |--------------------------------------------------------------------------
        */

        $objetivoVencido =
            ObjetivoOperario::query()
                ->where(
                    'id_usuario',
                    $operario->id
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
                ->latest(
                    'fecha_fin'
                )
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

        $objetivos =
            ObjetivoOperario::query()
                ->where(
                    'id_usuario',
                    $operario->id
                )
                ->orderByDesc(
                    'fecha_inicio'
                )
                ->orderByDesc(
                    'id_objetivo'
                )
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
            !in_array(
                $usuario->id_rol,
                [1, 2]
            )
        ) {
            abort(403);
        }

        $operario =
            $this
                ->consultaOperariosVisibles(
                    $usuario
                )
                ->with(
                    'tipoOperario'
                )
                ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | TIPOS DISPONIBLES
        |--------------------------------------------------------------------------
        */

        $tiposOperario =
            TipoOperario::query()
                ->where(
                    'estado',
                    true
                )
                ->orderBy(
                    'nombre'
                )
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
            !in_array(
                $usuario->id_rol,
                [1, 2]
            )
        ) {
            abort(403);
        }

        $operario =
            $this
                ->consultaOperariosVisibles(
                    $usuario
                )
                ->findOrFail($id);

        $datos =
            $request->validate(
                [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'email' => [
                        'required',
                        'email',

                        Rule::unique(
                            'users',
                            'email'
                        )->ignore(
                            $operario->id
                        ),
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | TIPO DE OPERARIO
                    |--------------------------------------------------------------------------
                    */

                    'id_tipo_operario' => [
                        'required',
                        'integer',

                        Rule::exists(
                            'tipo_operarios',
                            'id_tipo_operario'
                        )->where(
                            function ($query) {
                                $query->where(
                                    'estado',
                                    true
                                );
                            }
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

        /*
        |--------------------------------------------------------------------------
        | COMPROBAR SI CAMBIÓ EL TIPO
        |--------------------------------------------------------------------------
        */

        $cambioTipo =
            (int)
            $operario->id_tipo_operario
            !==
            (int)
            $datos['id_tipo_operario'];

        /*
        |--------------------------------------------------------------------------
        | DATOS GENERALES
        |--------------------------------------------------------------------------
        */

        $operario->name =
            $datos['name'];

        $operario->email =
            $datos['email'];

        $operario->id_tipo_operario =
            $datos['id_tipo_operario'];

        /*
        |--------------------------------------------------------------------------
        | CAMBIAR CONTRASEÑA
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $datos['password']
            )
        ) {
            if (
                empty(
                    $datos[
                        'current_password'
                    ]
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
                    $datos[
                        'current_password'
                    ],
                    $operario->password
                )
            ) {
                return back()
                    ->withErrors([
                        'current_password' =>
                            'La contraseña actual es incorrecta.',
                    ])
                    ->withInput();
            }

            $operario->password =
                Hash::make(
                    $datos['password']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | SI CAMBIA EL TIPO DE UN OPERARIO APROBADO
        |--------------------------------------------------------------------------
        |
        | El tipo determina lo que podrá hacer dentro del sistema.
        |
        | Por eso, si un Admin Cliente cambia el tipo de un operario
        | que ya estaba aprobado, la modificación vuelve a requerir
        | autorización del Super Admin.
        |--------------------------------------------------------------------------
        */

        if (
            $usuario->id_rol == 2 &&
            $cambioTipo &&
            $operario->estado_aprobacion === 'aprobado'
        ) {
            $operario->estado =
                false;

            $operario->estado_aprobacion =
                'pendiente';

            $operario->solicitado_por =
                $usuario->id;

            $operario->fecha_solicitud =
                now();

            $operario->revisado_por =
                null;

            $operario->fecha_revision =
                null;

            $operario->motivo_rechazo =
                null;
        }

        $operario->save();

        /*
        |--------------------------------------------------------------------------
        | RESPUESTA
        |--------------------------------------------------------------------------
        */

        if (
            $usuario->id_rol == 2 &&
            $cambioTipo &&
            $operario->estado_aprobacion === 'pendiente'
        ) {
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
            !in_array(
                $usuario->id_rol,
                [1, 2]
            )
        ) {
            abort(403);
        }

        $datos =
            $request->validate([
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

        /*
        |--------------------------------------------------------------------------
        | BUSCAR OPERARIO
        |--------------------------------------------------------------------------
        */

        $operario =
            $this
                ->consultaOperariosVisibles(
                    $usuario
                )
                ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | APROBACIÓN
        |--------------------------------------------------------------------------
        */

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

        if (!$operario->estado) {
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
                $operario->id
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

        /*
        |--------------------------------------------------------------------------
        | ESTADO INICIAL
        |--------------------------------------------------------------------------
        */

        $fechaInicio =
            Carbon::parse(
                $datos['fecha_inicio']
            )->startOfDay();

        $hoy =
            now()->startOfDay();

        $estadoInicial =
            $fechaInicio
                ->greaterThan($hoy)
                ? 'pendiente'
                : 'en_progreso';

        /*
        |--------------------------------------------------------------------------
        | CREAR OBJETIVO
        |--------------------------------------------------------------------------
        */

        ObjetivoOperario::create([
            'id_usuario' =>
                $operario->id,

            'objetivo_llamadas' =>
                $datos[
                    'objetivo_llamadas'
                ],

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
    | ACTIVAR / DESACTIVAR OPERARIO
    |--------------------------------------------------------------------------
    */

    public function toggleEstado($id)
    {
        $usuario = auth()->user();

        if (
            !$usuario ||
            !in_array(
                $usuario->id_rol,
                [1, 2]
            )
        ) {
            abort(403);
        }

        $operario =
            $this
                ->consultaOperariosVisibles(
                    $usuario
                )
                ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | SOLICITUD NO APROBADA
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | OPERARIO SIN TIPO
        |--------------------------------------------------------------------------
        */

        if (
            !$operario->id_tipo_operario
        ) {
            return back()
                ->withErrors([
                    'operario' =>
                        'Debes asignar un tipo de operario antes de activar esta cuenta.',
                ]);
        }

        $operario->estado =
            !$operario->estado;

        $operario->save();

        return redirect()
            ->back()
            ->with(
                'success',
                $operario->estado
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
            !in_array(
                $usuario->id_rol,
                [1, 2]
            )
        ) {
            abort(403);
        }

        $operario =
            $this
                ->consultaOperariosVisibles(
                    $usuario
                )
                ->findOrFail($id);

        $operario->delete();

        return redirect()
            ->route(
                'operarios.index'
            )
            ->with(
                'success',
                'Operario eliminado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | MÉTODOS INTERNOS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | OPERARIOS VISIBLES SEGÚN EL USUARIO
    |--------------------------------------------------------------------------
    */

    private function consultaOperariosVisibles(
        User $usuario
    ) {
        $consulta =
            User::query()
                ->where(
                    'id_rol',
                    3
                );

        /*
        |--------------------------------------------------------------------------
        | ADMIN CLIENTE
        |--------------------------------------------------------------------------
        */

        if ($usuario->id_rol == 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        return $consulta;
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRAR OPERARIOS APROBADOS
    |--------------------------------------------------------------------------
    */

    private function filtrarAprobados(
        $consulta
    ) {
        return $consulta
            ->where(
                function ($query) {
                    $query
                        ->where(
                            'estado_aprobacion',
                            'aprobado'
                        )
                        ->orWhereNull(
                            'estado_aprobacion'
                        );
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR APROBACIÓN
    |--------------------------------------------------------------------------
    */

    private function operarioEstaAprobado(
        User $operario
    ): bool {
        return
            $operario
                ->estado_aprobacion
                === 'aprobado'
            ||
            $operario
                ->estado_aprobacion
                === null;
    }
}