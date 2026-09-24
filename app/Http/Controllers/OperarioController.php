<?php

namespace App\Http\Controllers;

use App\Models\ObjetivoOperario;
use App\Models\User;
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

        /*
         * Admin Cliente:
         * solamente puede ver operarios de su empresa.
         */
        if ($usuario->id_rol == 2) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        /*
         * Búsqueda
         */
        if ($request->filled('buscar')) {

            $buscar = trim($request->buscar);

            $consulta->where(function ($query) use ($buscar) {

                $query->where(
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
         * Filtro por estado
         */
        if ($request->filled('estado')) {

            if ($request->estado === 'activo') {

                $consulta->where(
                    'estado',
                    true
                );
            }

            if ($request->estado === 'inactivo') {

                $consulta->where(
                    'estado',
                    false
                );
            }
        }

        $operarios = $consulta
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view(
            'operarios.index',
            compact('operarios')
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
         * Si es Admin Cliente, el operario pertenece
         * automáticamente a su empresa.
         */
        if ($usuario->id_rol == 2) {

            $datos['id_empresa'] =
                $usuario->id_empresa;
        }

        /*
         * El usuario creado será operario.
         */
        $datos['id_rol'] = 3;

        /*
         * Operario activo por defecto.
         */
        $datos['estado'] = true;

        /*
         * Encriptar contraseña.
         */
        $datos['password'] = Hash::make(
            $datos['password']
        );

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
        |
        | Si la fecha final ya pasó y el objetivo todavía estaba
        | pendiente o en progreso, se marca automáticamente
        | como vencido.
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
        | Después podemos conectarlo directamente con la tabla
        | llamadas para que muestre el número real.
        |
        */

        $llamadasRealizadas = 0;


        /*
        |--------------------------------------------------------------------------
        | OBJETIVO ACTUAL
        |--------------------------------------------------------------------------
        |
        | Buscamos solamente objetivos cuyo periodo
        | actualmente esté vigente.
        |
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
             * Si alcanzó el objetivo.
             */
            if ($llamadasRealizadas >= $objetivo) {

                $situacionObjetivo = 'cumplido';

                /*
                 * Guardamos el estado como cumplido.
                 */
                if (
                    $objetivoActual->estado !== 'cumplido'
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

                $fechaFin = \Carbon\Carbon::parse(
                    $objetivoActual->fecha_fin
                )->startOfDay();

                $diasRestantes = $hoy->diffInDays(
                    $fechaFin,
                    false
                );


                /*
                 * Si faltan dos días o menos,
                 * mostrar aviso de próximo vencimiento.
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
        | COMPROBAR SI EXISTE UN OBJETIVO VENCIDO
        |--------------------------------------------------------------------------
        |
        | Como los objetivos vencidos ya no aparecen en
        | $objetivoActual, buscamos el último objetivo vencido
        | para poder mostrar la alerta.
        |
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
        |--------------------------------------------------------------------------
        | SITUACIÓN CUANDO EL ÚLTIMO OBJETIVO VENCIÓ
        |--------------------------------------------------------------------------
        |
        | Si no existe un objetivo actual pero sí existe
        | uno vencido, la vista recibirá "vencido".
        |
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


        /*
        |--------------------------------------------------------------------------
        | ENVIAR INFORMACIÓN A LA VISTA
        |--------------------------------------------------------------------------
        */

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


        /*
         * Validación.
         */
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
         * Actualizar nombre y correo.
         */
        $operario->name =
            $datos['name'];

        $operario->email =
            $datos['email'];


        /*
         * Cambiar contraseña solamente si
         * se escribió una nueva.
         */
        if (!empty($datos['password'])) {

            /*
             * Se requiere contraseña actual.
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
         * Solo Super Administrador y Admin Cliente.
         */
        if (!in_array($usuario->id_rol, [1, 2])) {
            abort(403);
        }


        /*
         * Validación.
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
        | Si ya existe un objetivo pendiente o en progreso,
        | se marca como vencido.
        |
        | No se elimina.
        |
        | De esta forma se conserva el historial.
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
        | CREAR NUEVO OBJETIVO
        |--------------------------------------------------------------------------
        |
        | La migración acepta únicamente:
        |
        | pendiente
        | en_progreso
        | cumplido
        | vencido
        |
        | Por eso utilizamos "en_progreso" como estado
        | activo del objetivo.
        |
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
                'en_progreso',

        ]);


        /*
         * Regresar a la información del operario.
         */
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
         * Eliminar operario.
         *
         * Los objetivos relacionados se eliminan
         * automáticamente por cascadeOnDelete().
         */
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
}