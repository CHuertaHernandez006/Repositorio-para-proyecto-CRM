<?php

namespace App\Http\Controllers;

use App\Models\Campana;
use App\Models\EstadoCampana;
use Illuminate\Http\Request;
use App\Models\Cliente;

class CampanasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

   public function index(Request $request)
{
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
    | ESTADOS
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
        $datos = $request->validate([
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

            'id_estado_campana' => [
                'required',
                'integer',
                'exists:estado_campanas,id_estado_campana',
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
        ], [
            'nombre.required' =>
                'El nombre de la campaña es obligatorio.',

            'nombre.max' =>
                'El nombre no puede exceder 150 caracteres.',

            'id_estado_campana.required' =>
                'Debes seleccionar un estado.',

            'id_estado_campana.exists' =>
                'El estado seleccionado no es válido.',

            'fecha_inicio.required' =>
                'La fecha de inicio es obligatoria.',

            'fecha_fin.required' =>
                'La fecha final es obligatoria.',

            'fecha_fin.after_or_equal' =>
                'La fecha final no puede ser anterior a la fecha de inicio.',
        ]);

        Campana::create($datos);

        return redirect()
            ->route('campanas.index')
            ->with(
                'success',
                'Campaña creada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | VER
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $campana = Campana::query()
            ->with('estadoCampana')
            ->withCount('clientes')
            ->findOrFail($id);

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
            Campana::findOrFail($id);

        $estados = EstadoCampana::query()
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
            Campana::findOrFail($id);

        $datos = $request->validate([
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

            'id_estado_campana' => [
                'required',
                'integer',
                'exists:estado_campanas,id_estado_campana',
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
        ], [
            'nombre.required' =>
                'El nombre de la campaña es obligatorio.',

            'id_estado_campana.required' =>
                'Debes seleccionar un estado.',

            'fecha_inicio.required' =>
                'La fecha de inicio es obligatoria.',

            'fecha_fin.required' =>
                'La fecha final es obligatoria.',

            'fecha_fin.after_or_equal' =>
                'La fecha final no puede ser anterior a la fecha de inicio.',
        ]);

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
    $campana = Campana::query()
        ->with([
            'estadoCampana',
            'clientes',
        ])
        ->findOrFail($campana);

    $usuario = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | CLIENTES ASIGNADOS
    |--------------------------------------------------------------------------
    */

    $consultaAsignados = $campana
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
    $campana = Campana::findOrFail(
        $campana
    );


    $datos = $request->validate([
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
    ], [
        'clientes.required' =>
            'Selecciona al menos un cliente.',

        'clientes.min' =>
            'Selecciona al menos un cliente.',

        'clientes.*.exists' =>
            'Uno de los clientes seleccionados no existe.',
    ]);


    foreach ($datos['clientes'] as $idCliente) {

        /*
         * Revisamos si ese cliente ya estuvo
         * relacionado con la campaña anteriormente.
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
         * Si ya existía, simplemente reactivamos
         * la relación.
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
         * Si nunca había estado en la campaña,
         * creamos la relación.
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
                : count($datos['clientes']) .
                    ' clientes agregados a la campaña correctamente.'
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
    $campana = Campana::findOrFail(
        $campana
    );

    $cliente = Cliente::findOrFail(
        $cliente
    );


    /*
     * No eliminamos físicamente la relación.
     * La desactivamos para conservar historial.
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
}