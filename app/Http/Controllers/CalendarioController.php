<?php

namespace App\Http\Controllers;

use App\Models\Calendario;
use App\Models\Cliente;
use App\Models\EstadoCita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CalendarioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO DEL CALENDARIO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        if (!in_array((int) $usuario->id_rol, [2, 3], true)) {
            abort(403);
        }

        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
        ]);

        $buscar = trim($datos['buscar'] ?? '');

        $consulta = Calendario::query()
            ->with([
                'cliente',
                'estadoCita',
                'usuario',
            ])
            ->where(
                'id_empresa',
                $usuario->id_empresa
            );

        /*
        |--------------------------------------------------------------------------
        | OPERARIO: SOLO SUS PROPIAS CITAS
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 3) {
            $consulta->where(
                'id_usuario',
                $usuario->id_usuario
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        if ($buscar !== '') {
            $palabras = preg_split(
                '/\s+/u',
                $buscar,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            foreach ($palabras as $palabra) {
                /*
                |--------------------------------------------------------------------------
                | Escapamos comodines para buscar texto literalmente.
                |--------------------------------------------------------------------------
                */

                $texto = str_replace(
                    ['!', '%', '_'],
                    ['!!', '!%', '!_'],
                    mb_strtolower(
                        $palabra,
                        'UTF-8'
                    )
                );

                $termino = '%' . $texto . '%';

                $consulta->where(
                    function ($q) use ($termino) {
                        $q
                            ->whereRaw(
                                "LOWER(COALESCE(motivo, '')) LIKE ? ESCAPE '!'",
                                [$termino]
                            )
                            ->orWhereRaw(
                                "LOWER(COALESCE(observaciones, '')) LIKE ? ESCAPE '!'",
                                [$termino]
                            )
                            ->orWhereHas(
                                'cliente',
                                function ($cliente) use ($termino) {
                                    $cliente->where(
                                        function ($nombre) use ($termino) {
                                            $nombre
                                                ->whereRaw(
                                                    "LOWER(nombre) LIKE ? ESCAPE '!'",
                                                    [$termino]
                                                )
                                                ->orWhereRaw(
                                                    "LOWER(apellido_paterno) LIKE ? ESCAPE '!'",
                                                    [$termino]
                                                )
                                                ->orWhereRaw(
                                                    "LOWER(COALESCE(apellido_materno, '')) LIKE ? ESCAPE '!'",
                                                    [$termino]
                                                )
                                                ->orWhereRaw(
                                                    "LOWER(CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno)) LIKE ? ESCAPE '!'",
                                                    [$termino]
                                                );
                                        }
                                    );
                                }
                            );
                    }
                );
            }
        }

        $citas = $consulta
            ->orderBy(
                'fecha_hora_inicio',
                'asc'
            )
            ->get();

        return view(
            'calendario.index',
            compact(
                'citas',
                'buscar'
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
        $usuario = Auth::user();

        if (
            !$usuario ||
            !in_array(
                (int) $usuario->id_rol,
                [2, 3],
                true
            )
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

        /*
        |--------------------------------------------------------------------------
        | ADMIN CLIENTE
        |--------------------------------------------------------------------------
        |
        | Puede seleccionar entre operarios activos y aprobados de su empresa.
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 2) {
            $operarios = $this
                ->consultaOperariosDisponibles(
                    (int) $usuario->id_empresa
                )
                ->get();
        } else {
            /*
            |--------------------------------------------------------------------------
            | OPERARIO
            |--------------------------------------------------------------------------
            |
            | Solo puede agendar para sí mismo.
            |--------------------------------------------------------------------------
            */

            $operarios = collect([
                $usuario,
            ]);
        }

        $estados = EstadoCita::query()
            ->where(
                'activo',
                true
            )
            ->orderBy('nombre')
            ->get();

        return view(
            'calendario.create',
            compact(
                'clientes',
                'operarios',
                'estados'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR CITA
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $usuario = Auth::user();

        if (
            !$usuario ||
            !in_array(
                (int) $usuario->id_rol,
                [2, 3],
                true
            )
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | OPERARIO: FORZAR SU PROPIO ID
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 3) {
            $request->merge([
                'id_usuario' =>
                    $usuario->id_usuario,
            ]);
        }

        $datos = $request->validate([
            'id_cliente' => [
                'required',
                'integer',
            ],

            'id_usuario' => [
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

        $idEmpresa =
            (int) $usuario->id_empresa;

        /*
        |--------------------------------------------------------------------------
        | VALIDAR CLIENTE
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
        */

        if ((int) $usuario->id_rol === 2) {
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
            if (
                (int) $datos['id_usuario'] !==
                (int) $usuario->id_usuario
            ) {
                abort(403);
            }
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
            throw ValidationException::withMessages([
                'id_estado_cita' =>
                    'No existe un estado de cita activo llamado "Pendiente".',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CREAR CITA
        |--------------------------------------------------------------------------
        */

        Calendario::create([
            'id_empresa' =>
                $idEmpresa,

            'id_cliente' =>
                (int) $datos['id_cliente'],

            'id_usuario' =>
                (int) $datos['id_usuario'],

            'id_llamada' =>
                null,

            'id_estado_cita' =>
                $estadoPendiente
                    ->id_estado_cita,

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
            ->route('calendario.index')
            ->with(
                'exito',
                'Cita agendada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULARIO EDITAR
    |--------------------------------------------------------------------------
    */

    public function edit($id_cita)
    {
        $usuario = Auth::user();

        if (
            !$usuario ||
            !in_array(
                (int) $usuario->id_rol,
                [2, 3],
                true
            )
        ) {
            abort(403);
        }

        $cita = $this
            ->consultaCitasVisibles(
                $usuario
            )
            ->findOrFail($id_cita);

        $clientes = Cliente::query()
            ->where(
                'id_empresa',
                $usuario->id_empresa
            )
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get();

        if ((int) $usuario->id_rol === 2) {
            $operarios = $this
                ->consultaOperariosDisponibles(
                    (int) $usuario->id_empresa
                )
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Si el operario actual fue desactivado después de crear la cita,
            | lo conservamos en el select para no romper la edición.
            |--------------------------------------------------------------------------
            */

            $operarioActual = User::query()
                ->where(
                    'id_usuario',
                    $cita->id_usuario
                )
                ->where(
                    'id_empresa',
                    $usuario->id_empresa
                )
                ->where(
                    'id_rol',
                    3
                )
                ->first();

            if (
                $operarioActual &&
                !$operarios->contains(
                    'id_usuario',
                    $operarioActual
                        ->id_usuario
                )
            ) {
                $operarios->push(
                    $operarioActual
                );
            }
        } else {
            $operarios = collect([
                $usuario,
            ]);
        }

        $estados = EstadoCita::query()
            ->where(
                'activo',
                true
            )
            ->orderBy('nombre')
            ->get();

        return view(
            'calendario.edit',
            compact(
                'cita',
                'clientes',
                'operarios',
                'estados'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR CITA
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id_cita
    ) {
        $usuario = Auth::user();

        if (
            !$usuario ||
            !in_array(
                (int) $usuario->id_rol,
                [2, 3],
                true
            )
        ) {
            abort(403);
        }

        $cita = $this
            ->consultaCitasVisibles(
                $usuario
            )
            ->findOrFail($id_cita);

        /*
        |--------------------------------------------------------------------------
        | OPERARIO
        |--------------------------------------------------------------------------
        |
        | No puede cambiar ni el cliente ni el operario asignado.
        |--------------------------------------------------------------------------
        */

        if ((int) $usuario->id_rol === 3) {
            $request->merge([
                'id_cliente' =>
                    $cita->id_cliente,

                'id_usuario' =>
                    $cita->id_usuario,
            ]);
        }

        $datos = $request->validate([
            'id_cliente' => [
                'required',
                'integer',
            ],

            'id_usuario' => [
                'required',
                'integer',
            ],

            'fecha_hora_inicio' => [
                'required',
                'date',
            ],

            'fecha_hora_fin' => [
                'nullable',
                'date',
                'after_or_equal:fecha_hora_inicio',
            ],

            'id_estado_cita' => [
                'required',
                'integer',
                'exists:estados_cita,id_estado_cita',
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

        $idEmpresa =
            (int) $usuario->id_empresa;

        /*
        |--------------------------------------------------------------------------
        | VALIDAR CLIENTE
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
        */

        if ((int) $usuario->id_rol === 2) {
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
        } else {
            if (
                (int) $datos['id_usuario'] !==
                (int) $usuario->id_usuario
            ) {
                abort(403);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAR ESTADO
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR
        |--------------------------------------------------------------------------
        */

        $cita->update([
            'id_cliente' =>
                (int) $datos['id_cliente'],

            'id_usuario' =>
                (int) $datos['id_usuario'],

            'fecha_hora_inicio' =>
                $datos['fecha_hora_inicio'],

            'fecha_hora_fin' =>
                $datos['fecha_hora_fin'] ?? null,

            'id_estado_cita' =>
                (int) $datos['id_estado_cita'],

            'motivo' =>
                $datos['motivo'] ?? null,

            'observaciones' =>
                $datos['observaciones'] ?? null,
        ]);

        return redirect()
            ->route('calendario.index')
            ->with(
                'exito',
                'Cita actualizada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR CITA
    |--------------------------------------------------------------------------
    */

    public function destroy($id_cita)
    {
        $usuario = Auth::user();

        if (
            !$usuario ||
            !in_array(
                (int) $usuario->id_rol,
                [2, 3],
                true
            )
        ) {
            abort(403);
        }

        $cita = $this
            ->consultaCitasVisibles(
                $usuario
            )
            ->findOrFail($id_cita);

        $cita->delete();

        return redirect()
            ->route('calendario.index')
            ->with(
                'exito',
                'Cita eliminada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CONSULTA DE CITAS VISIBLES
    |--------------------------------------------------------------------------
    */

    private function consultaCitasVisibles(
        User $usuario
    ) {
        $consulta = Calendario::query()
            ->where(
                'id_empresa',
                $usuario->id_empresa
            );

        if ((int) $usuario->id_rol === 3) {
            $consulta->where(
                'id_usuario',
                $usuario->id_usuario
            );
        }

        return $consulta;
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
