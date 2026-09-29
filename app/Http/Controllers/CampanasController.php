<?php

namespace App\Http\Controllers;

use App\Models\Campana;
use App\Models\Cliente;
use App\Models\EstadoCampana;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CampanasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Antes de mostrar las campañas actualizamos automáticamente
        | su estado de acuerdo con las fechas.
        |--------------------------------------------------------------------------
        */

        $this->sincronizarEstadosAutomaticos();


        $consulta = Campana::query()
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

            $buscar = trim($request->buscar);

            $consulta->where(function ($query) use ($buscar) {

                $query
                    ->where(
                        'nombre',
                        'like',
                        '%' . $buscar . '%'
                    )
                    ->orWhere(
                        'descripcion',
                        'like',
                        '%' . $buscar . '%'
                    )
                    ->orWhere(
                        'objetivo',
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
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ESTADÍSTICAS
        |--------------------------------------------------------------------------
        */

        $hoy = now()->toDateString();


        $totalCampanas =
            Campana::count();


        $campanasEnCurso =
            Campana::query()
                ->whereDate(
                    'fecha_inicio',
                    '<=',
                    $hoy
                )
                ->whereDate(
                    'fecha_fin',
                    '>=',
                    $hoy
                )
                ->count();


        $campanasProximas =
            Campana::query()
                ->whereDate(
                    'fecha_inicio',
                    '>',
                    $hoy
                )
                ->count();


        $campanasFinalizadas =
            Campana::query()
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
        $estados = EstadoCampana::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();


        return view(
            'campanas.create',
            compact('estados')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Ya NO recibimos el estado como una decisión del usuario.
        |
        | Laravel solamente recibe las fechas y calcula el estado real.
        |--------------------------------------------------------------------------
        */

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
        | CALCULAR ESTADO
        |--------------------------------------------------------------------------
        */

        $estado =
            $this->obtenerEstadoAutomatico(
                $datos['fecha_inicio'],
                $datos['fecha_fin']
            );


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
        $campana = Campana::query()
            ->with('estadoCampana')
            ->withCount('clientes')
            ->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Actualizamos su estado antes de mostrarla.
        |--------------------------------------------------------------------------
        */

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
        $campana =
            Campana::query()
                ->with('estadoCampana')
                ->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Antes de editar comprobamos cuál es su estado actual.
        |--------------------------------------------------------------------------
        */

        $this->sincronizarEstadoAutomatico(
            $campana
        );


        $campana->load(
            'estadoCampana'
        );


        $estados =
            EstadoCampana::query()
                ->where('estado', true)
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
    | ACTUALIZAR
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {
        $campana =
            Campana::query()
                ->with('estadoCampana')
                ->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | El estado tampoco se recibe manualmente al editar.
        |--------------------------------------------------------------------------
        */

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
        | CANCELADA ES UNA EXCEPCIÓN
        |--------------------------------------------------------------------------
        |
        | Si una campaña fue cancelada explícitamente,
        | editar el nombre, descripción o fechas no la reactiva.
        |
        */

        if (
            $this->campanaEstaCancelada(
                $campana
            )
        ) {

            $datos['id_estado_campana'] =
                $campana->id_estado_campana;

        } else {

            /*
            |--------------------------------------------------------------------------
            | CÁLCULO AUTOMÁTICO NORMAL
            |--------------------------------------------------------------------------
            */

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
        $campana =
            Campana::query()
                ->with([
                    'estadoCampana',
                    'clientes',
                ])
                ->findOrFail($campana);


        /*
        |--------------------------------------------------------------------------
        | Antes de mostrar los clientes también actualizamos
        | el estado temporal de la campaña.
        |--------------------------------------------------------------------------
        */

        $this->sincronizarEstadoAutomatico(
            $campana
        );


        $usuario =
            auth()->user();


        /*
        |--------------------------------------------------------------------------
        | CLIENTES ASIGNADOS
        |--------------------------------------------------------------------------
        */

        $consultaAsignados =
            $campana
                ->clientes()
                ->orderBy('nombre');


        if (
            $usuario &&
            $usuario->id_rol == 2 &&
            $usuario->id_empresa
        ) {

            $consultaAsignados->where(
                'clientes.id_empresa',
                $usuario->id_empresa
            );
        }


        $clientesAsignados =
            $consultaAsignados->get();


        $idsAsignados =
            $clientesAsignados
                ->pluck('id_cliente');


        /*
        |--------------------------------------------------------------------------
        | CLIENTES DISPONIBLES
        |--------------------------------------------------------------------------
        */

        $consultaDisponibles =
            Cliente::query()
                ->whereNotIn(
                    'id_cliente',
                    $idsAsignados
                );


        if (
            $usuario &&
            $usuario->id_rol == 2 &&
            $usuario->id_empresa
        ) {

            $consultaDisponibles->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }


        $clientesDisponibles =
            $consultaDisponibles
                ->orderBy('nombre')
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
    | ASIGNAR CLIENTES A CAMPAÑA
    |--------------------------------------------------------------------------
    */

    public function asignarClientes(
        Request $request,
        $campana
    ) {
        $campana =
            Campana::findOrFail(
                $campana
            );


        $datos =
            $request->validate(
                [
                    'clientes' => [
                        'required',
                        'array',
                        'min:1',
                    ],

                    'clientes.*' => [
                        'required',
                        'integer',
                        'exists:clientes,id_cliente',
                    ],
                ],
                [
                    'clientes.required' =>
                        'Selecciona al menos un cliente.',

                    'clientes.min' =>
                        'Selecciona al menos un cliente.',

                    'clientes.*.exists' =>
                        'Uno de los clientes seleccionados no existe.',
                ]
            );


        foreach (
            $datos['clientes']
            as $idCliente
        ) {

            /*
            |--------------------------------------------------------------------------
            | Revisamos si el cliente ya estuvo relacionado anteriormente.
            |--------------------------------------------------------------------------
            */

            $relacionExistente =
                $campana
                    ->todosLosClientes()
                    ->wherePivot(
                        'id_cliente',
                        $idCliente
                    )
                    ->first();


            /*
            |--------------------------------------------------------------------------
            | REACTIVAR RELACIÓN
            |--------------------------------------------------------------------------
            */

            if ($relacionExistente) {

                $campana
                    ->todosLosClientes()
                    ->updateExistingPivot(
                        $idCliente,
                        [
                            'estado' => true,
                            'fecha_asignacion' => now(),
                        ]
                    );


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | NUEVA RELACIÓN
            |--------------------------------------------------------------------------
            */

            $campana
                ->todosLosClientes()
                ->attach(
                    $idCliente,
                    [
                        'fecha_asignacion' => now(),
                        'estado' => true,
                        'intentos' => 0,
                    ]
                );
        }


        return redirect()
            ->route(
                'campanas.clientes',
                $campana->id_campana
            )
            ->with(
                'success',
                count($datos['clientes']) === 1
                    ? 'Cliente agregado a la campaña correctamente.'
                    : count($datos['clientes'])
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
        $campana =
            Campana::findOrFail(
                $campana
            );


        $cliente =
            Cliente::findOrFail(
                $cliente
            );


        /*
        |--------------------------------------------------------------------------
        | No eliminamos físicamente la relación.
        | La desactivamos para conservar historial.
        |--------------------------------------------------------------------------
        */

        $campana
            ->todosLosClientes()
            ->updateExistingPivot(
                $cliente->id_cliente,
                [
                    'estado' => false,
                ]
            );


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
    | ELIMINAR
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $campana =
            Campana::findOrFail($id);


        $nombre =
            $campana->nombre;


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
    |--------------------------------------------------------------------------
    | MÉTODOS INTERNOS PARA ESTADOS AUTOMÁTICOS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | OBTENER ESTADO SEGÚN FECHAS
    |--------------------------------------------------------------------------
    |
    | Fecha actual < inicio
    |     => Planeada
    |
    | inicio <= fecha actual <= final
    |     => En proceso
    |
    | fecha actual > final
    |     => Finalizada
    |
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
            Carbon::parse(
                $fechaFin
            )->startOfDay();


        /*
        |--------------------------------------------------------------------------
        | PLANEADA
        |--------------------------------------------------------------------------
        */

        if (
            $hoy->lt($inicio)
        ) {

            return $this->buscarEstadoPorNombre([
                'Planeada',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | FINALIZADA
        |--------------------------------------------------------------------------
        */

        if (
            $hoy->gt($fin)
        ) {

            return $this->buscarEstadoPorNombre([
                'Finalizada',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EN PROCESO
        |--------------------------------------------------------------------------
        |
        | Permitimos "Activa" porque actualmente tu catálogo
        | todavía puede tener ese nombre.
        |--------------------------------------------------------------------------
        */

        return $this->buscarEstadoPorNombre([
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
                    ->where('estado', true)
                    ->whereRaw(
                        'LOWER(nombre) = ?',
                        [
                            mb_strtolower(
                                trim($nombre)
                            ),
                        ]
                    )
                    ->first();


            if ($estado) {

                return $estado;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Si llegamos aquí significa que falta un estado
        | necesario en estado_campanas.
        |--------------------------------------------------------------------------
        */

        abort(
            500,
            'No se encontró en estado_campanas uno de los estados requeridos: '
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


        if (
            !$campana->estadoCampana
        ) {

            return false;
        }


        return mb_strtolower(
            trim(
                $campana
                    ->estadoCampana
                    ->nombre
            )
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
        /*
        |--------------------------------------------------------------------------
        | Una campaña cancelada permanece cancelada.
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Solo hacemos UPDATE si realmente cambió el estado.
        |--------------------------------------------------------------------------
        */

        if (
            (int) $campana->id_estado_campana
            !==
            (int) $estadoCorrecto->id_estado_campana
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
    | SINCRONIZAR TODAS LAS CAMPAÑAS
    |--------------------------------------------------------------------------
    |
    | Se ejecuta al entrar al listado de campañas.
    |
    | De esta manera:
    |
    | Planeada
    |      ↓ llega fecha_inicio
    | En proceso
    |      ↓ pasa fecha_fin
    | Finalizada
    |
    |--------------------------------------------------------------------------
    */

    private function sincronizarEstadosAutomaticos(): void
    {
        $campanas =
            Campana::query()
                ->with('estadoCampana')
                ->get();


        foreach (
            $campanas
            as $campana
        ) {

            $this->sincronizarEstadoAutomatico(
                $campana
            );
        }
    }
}