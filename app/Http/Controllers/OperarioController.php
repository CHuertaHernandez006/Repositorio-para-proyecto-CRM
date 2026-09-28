<?php

namespace App\Http\Controllers;

use App\Models\ObjetivoOperario;
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

        $consulta = User::query()
            ->with('empresa')
            ->where('id_rol', 3);

        // Admin Cliente: solamente puede ver operarios de su empresa.
        if ($usuario->id_rol == 2) {
            $consulta->where('id_empresa', $usuario->id_empresa);
        }

        // Búsqueda por nombre o correo.
        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);

            $consulta->where(function ($query) use ($buscar) {
                $query->where('name', 'like', '%' . $buscar . '%')
                    ->orWhere('email', 'like', '%' . $buscar . '%');
            });
        }

        // Filtro por estado del operario.
        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $consulta->where('estado', true);
            }

            if ($request->estado === 'inactivo') {
                $consulta->where('estado', false);
            }
        }

        $operarios = $consulta
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('operarios.index', compact('operarios'));
    }


    /*
    |--------------------------------------------------------------------------
    | PANEL GENERAL DE OBJETIVOS
    |--------------------------------------------------------------------------
    |
    | Esta será la nueva sección desde donde podremos consultar el progreso
    | general de los operarios y posteriormente administrar objetivos
    | de manera más cómoda.
    |
    */

 public function objetivos()
{
    $usuario = auth()->user();

    if (!in_array($usuario->id_rol, [1, 2])) {
        abort(403);
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER OPERARIOS PERMITIDOS
    |--------------------------------------------------------------------------
    */

    $consultaOperarios = User::query()
        ->with('empresa')
        ->where('id_rol', 3);

    /*
     * Admin Cliente solamente puede ver
     * operarios pertenecientes a su empresa.
     */
    if ($usuario->id_rol == 2) {
        $consultaOperarios->where(
            'id_empresa',
            $usuario->id_empresa
        );
    }

    $operarios = $consultaOperarios
        ->orderBy('name')
        ->get();

    /*
     * IDs de los operarios permitidos.
     */
    $idsOperarios = $operarios->pluck('id');


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
        ->whereIn('estado', [
            'pendiente',
            'en_progreso',
        ])
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
    | OBTENER OBJETIVO ACTUAL DE CADA OPERARIO
    |--------------------------------------------------------------------------
    */

    foreach ($operarios as $operario) {

        /*
         * Primero buscamos un objetivo vigente.
         */
        $objetivoActual = ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id
            )
            ->whereIn('estado', [
                'pendiente',
                'en_progreso',
            ])
            ->whereDate(
                'fecha_fin',
                '>=',
                now()->toDateString()
            )
            ->latest('id_objetivo')
            ->first();

        /*
         * Guardamos temporalmente el objetivo
         * dentro del objeto del operario para
         * poder usarlo desde objetivos.blade.php.
         */
        $operario->objetivo_actual =
            $objetivoActual;


        /*
        |--------------------------------------------------------------------------
        | SITUACIÓN VISUAL DEL OBJETIVO
        |--------------------------------------------------------------------------
        */

        $operario->situacion_objetivo =
            'sin_objetivo';

        if ($objetivoActual) {

            /*
             * Si todavía no comienza.
             */
            if (
                $objetivoActual->estado === 'pendiente' ||
                \Carbon\Carbon::parse(
                    $objetivoActual->fecha_inicio
                )->isFuture()
            ) {

                $operario->situacion_objetivo =
                    'pendiente';

            } else {

                $fechaFin = \Carbon\Carbon::parse(
                    $objetivoActual->fecha_fin
                )->startOfDay();

                $diasRestantes = now()
                    ->startOfDay()
                    ->diffInDays(
                        $fechaFin,
                        false
                    );

                /*
                 * Si faltan dos días o menos.
                 */
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
    | ESTADÍSTICAS DE LA PANTALLA
    |--------------------------------------------------------------------------
    */

    $totalOperarios =
        $operarios->count();

    $operariosActivos =
        $operarios
            ->where('estado', true)
            ->count();

    $operariosInactivos =
        $operarios
            ->where('estado', false)
            ->count();

    $operariosConObjetivo =
        $operarios
            ->filter(function ($operario) {
                return $operario->objetivo_actual !== null;
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


    /*
    |--------------------------------------------------------------------------
    | ENVIAR DATOS A LA VISTA
    |--------------------------------------------------------------------------
    */

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

        if (!in_array($usuario->id_rol, [1, 2])) {
            abort(403);
        }

        return view('operarios.create');
    }


    /*
    |--------------------------------------------------------------------------
    | CREAR OPERARIO
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $usuario = auth()->user();

        if (!in_array($usuario->id_rol, [1, 2])) {
            abort(403);
        }

        $datos = $request->validate([
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

            'id_empresa' => [
                'nullable',
                'integer',
                'exists:empresas,id_empresa',
            ],
        ]);

        /*
         * Admin Cliente:
         * el operario pertenece automáticamente
         * a su misma empresa.
         */
        if ($usuario->id_rol == 2) {
            $datos['id_empresa'] =
                $usuario->id_empresa;
        }

        $datos['id_rol'] = 3;

        $datos['estado'] = true;

        $datos['password'] =
            Hash::make($datos['password']);

        User::create($datos);

        return redirect()
            ->route('operarios.index')
            ->with(
                'success',
                'Operario registrado correctamente.'
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

        /*
         * Buscar operario.
         */
        $consulta = User::query()
            ->with('empresa')
            ->where('id_rol', 3);

        /*
         * Admin Cliente solamente puede consultar
         * operarios de su empresa.
         */
        if ($usuario->id_rol == 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $operario = $consulta->findOrFail($id);

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
            ->whereIn('estado', [
                'pendiente',
                'en_progreso',
            ])
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
        |
        | Por ahora permanece en 0.
        |
        */

        $llamadasRealizadas = 0;

        /*
        |--------------------------------------------------------------------------
        | OBJETIVO ACTUAL
        |--------------------------------------------------------------------------
        */

        $objetivoActual = ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id
            )
            ->whereIn('estado', [
                'pendiente',
                'en_progreso',
            ])
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

        /*
        |--------------------------------------------------------------------------
        | SITUACIÓN DEL OBJETIVO
        |--------------------------------------------------------------------------
        */

        $situacionObjetivo = null;

        if ($objetivoActual) {

            $objetivo =
                (int) $objetivoActual->objetivo_llamadas;

            /*
             * Objetivo cumplido.
             */
            if ($llamadasRealizadas >= $objetivo) {

                $situacionObjetivo = 'cumplido';

                if (
                    $objetivoActual->estado !==
                    'cumplido'
                ) {
                    $objetivoActual->update([
                        'estado' => 'cumplido',
                    ]);
                }

            } else {

                /*
                 * Calcular días restantes.
                 */
                $hoy = now()->startOfDay();

                $fechaFin = Carbon::parse(
                    $objetivoActual->fecha_fin
                )->startOfDay();

                $diasRestantes = $hoy->diffInDays(
                    $fechaFin,
                    false
                );

                /*
                 * Si faltan dos días o menos,
                 * mostramos aviso.
                 */
                if ($diasRestantes <= 2) {
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

        $objetivoVencido = ObjetivoOperario::query()
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
            ->latest('fecha_fin')
            ->first();

        /*
         * Si no existe objetivo actual pero sí existe
         * uno vencido, mostramos esa situación.
         */
        if (
            !$objetivoActual &&
            $objetivoVencido
        ) {
            $situacionObjetivo = 'vencido';
        }

        /*
        |--------------------------------------------------------------------------
        | HISTORIAL DE OBJETIVOS
        |--------------------------------------------------------------------------
        */

        $objetivos = ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id
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

        if (!in_array($usuario->id_rol, [1, 2])) {
            abort(403);
        }

        $consulta = User::query()
            ->where('id_rol', 3);

        /*
         * Admin Cliente solamente puede editar
         * operarios de su empresa.
         */
        if ($usuario->id_rol == 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $operario = $consulta->findOrFail($id);

        return view(
            'operarios.edit',
            compact('operario')
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

        if (!in_array($usuario->id_rol, [1, 2])) {
            abort(403);
        }

        $consulta = User::query()
            ->where('id_rol', 3);

        /*
         * Admin Cliente solamente puede actualizar
         * operarios de su empresa.
         */
        if ($usuario->id_rol == 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $operario = $consulta->findOrFail($id);

        $datos = $request->validate([
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
                )->ignore($operario->id),
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
        ]);

        /*
         * Actualizar datos generales.
         */
        $operario->name =
            $datos['name'];

        $operario->email =
            $datos['email'];

        /*
         * Cambiar contraseña solamente si
         * se proporcionó una nueva.
         */
        if (!empty($datos['password'])) {

            /*
             * Se necesita la contraseña actual.
             */
            if (
                empty(
                    $datos['current_password']
                )
            ) {
                return back()
                    ->withErrors([
                        'current_password' =>
                            'Debes ingresar tu contraseña actual para cambiarla.',
                    ])
                    ->withInput();
            }

            /*
             * Comprobar contraseña actual.
             */
            if (
                !Hash::check(
                    $datos['current_password'],
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

            /*
             * Guardar nueva contraseña.
             */
            $operario->password =
                Hash::make(
                    $datos['password']
                );
        }

        $operario->save();

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

        /*
         * Solo Super Administrador
         * y Admin Cliente.
         */
        if (!in_array($usuario->id_rol, [1, 2])) {
            abort(403);
        }

        /*
         * Validar datos.
         */
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

        /*
         * Buscar operario.
         */
        $consulta = User::query()
            ->where(
                'id',
                $id
            )
            ->where(
                'id_rol',
                3
            );

        /*
         * Admin Cliente solamente puede asignar
         * objetivos a operarios de su empresa.
         */
        if ($usuario->id_rol == 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $operario =
            $consulta->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | CERRAR OBJETIVO ANTERIOR
        |--------------------------------------------------------------------------
        |
        | No eliminamos el objetivo anterior.
        | Lo conservamos como parte del historial.
        |
        */

        ObjetivoOperario::query()
            ->where(
                'id_usuario',
                $operario->id
            )
            ->whereIn('estado', [
                'pendiente',
                'en_progreso',
            ])
            ->update([
                'estado' => 'vencido',
            ]);

        /*
        |--------------------------------------------------------------------------
        | DETERMINAR ESTADO INICIAL
        |--------------------------------------------------------------------------
        |
        | Si el objetivo comienza en una fecha futura,
        | queda pendiente.
        |
        | Si comienza hoy o antes, queda en progreso.
        |
        */

        $fechaInicio = Carbon::parse(
            $datos['fecha_inicio']
        )->startOfDay();

        $hoy = now()->startOfDay();

        $estadoInicial =
            $fechaInicio->greaterThan($hoy)
                ? 'pendiente'
                : 'en_progreso';

        /*
        |--------------------------------------------------------------------------
        | CREAR NUEVO OBJETIVO
        |--------------------------------------------------------------------------
        */

        ObjetivoOperario::create([
            'id_usuario' =>
                $operario->id,

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
    | ACTIVAR / DESACTIVAR OPERARIO
    |--------------------------------------------------------------------------
    */

    public function toggleEstado($id)
    {
        $usuario = auth()->user();

        if (!in_array($usuario->id_rol, [1, 2])) {
            abort(403);
        }

        $consulta = User::query()
            ->where(
                'id',
                $id
            )
            ->where(
                'id_rol',
                3
            );

        /*
         * Admin Cliente solamente puede modificar
         * operarios de su empresa.
         */
        if ($usuario->id_rol == 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $operario =
            $consulta->firstOrFail();

        /*
         * Cambiar estado.
         */
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

        if (!in_array($usuario->id_rol, [1, 2])) {
            abort(403);
        }

        $consulta = User::query()
            ->where(
                'id',
                $id
            )
            ->where(
                'id_rol',
                3
            );

        /*
         * Admin Cliente solamente puede eliminar
         * operarios de su empresa.
         */
        if ($usuario->id_rol == 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $operario =
            $consulta->firstOrFail();

        /*
         * Los objetivos relacionados se eliminan
         * automáticamente gracias a cascadeOnDelete().
         */
        $operario->delete();

        return redirect()
            ->route('operarios.index')
            ->with(
                'success',
                'Operario eliminado correctamente.'
            );
    }
}