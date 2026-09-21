<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\User;
use App\Models\EstadoCita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    /**
     * Mostrar las citas según el rol del usuario.
     *
     * Admin Cliente:
     * - Ve las citas de los operarios de su empresa.
     *
     * Operario:
     * - Ve únicamente sus propias citas.
     */
    public function index(Request $request)
    {
        $usuario = auth()->user();

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
        | ADMIN CLIENTE
        |--------------------------------------------------------------------------
        |
        | Puede consultar las citas de los usuarios que pertenecen
        | a su misma empresa.
        |
        */
        if ($usuario->id_rol == 2) {

            $consulta->whereHas('usuario', function ($query) use ($usuario) {
                $query->where('id_empresa', $usuario->id_empresa);
            });

        }

        /*
        |--------------------------------------------------------------------------
        | OPERARIO
        |--------------------------------------------------------------------------
        |
        | Solamente puede consultar sus propias citas.
        |
        */
        elseif ($usuario->id_rol == 3) {

            $consulta->where('id_usuario', $usuario->id);

        }

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        |
        | Permite buscar por nombre, apellidos, empresa, correo o teléfono
        | del cliente.
        |
        */
        if ($buscar !== '') {

            $termino = '%' . mb_strtolower($buscar, 'UTF-8') . '%';

            $consulta->whereHas('cliente', function ($query) use ($termino) {

                $query->where(function ($cliente) use ($termino) {

                    $cliente->whereRaw(
                        'LOWER(nombre) LIKE ?',
                        [$termino]
                    )
                    ->orWhereRaw(
                        'LOWER(apellido_paterno) LIKE ?',
                        [$termino]
                    )
                    ->orWhereRaw(
                        'LOWER(apellido_materno) LIKE ?',
                        [$termino]
                    )
                    ->orWhereRaw(
                        'LOWER(empresa) LIKE ?',
                        [$termino]
                    )
                    ->orWhereRaw(
                        'LOWER(correo) LIKE ?',
                        [$termino]
                    )
                    ->orWhereRaw(
                        'LOWER(telefono_principal) LIKE ?',
                        [$termino]
                    );
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR ESTADO
        |--------------------------------------------------------------------------
        */
        if (!empty($datos['estado'])) {

            $consulta->where(
                'id_estado_cita',
                $datos['estado']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR OPERARIO
        |--------------------------------------------------------------------------
        |
        | Este filtro se utilizará principalmente para el Admin Cliente.
        |
        */
        if (!empty($datos['operario']) && $usuario->id_rol == 2) {

            $consulta->where(
                'id_usuario',
                $datos['operario']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR FECHA
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | ORDEN
        |--------------------------------------------------------------------------
        |
        | Las citas más próximas aparecen primero.
        |
        */
        $citas = $consulta
            ->orderBy('fecha_hora_inicio', 'asc')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | DATOS PARA FILTROS
        |--------------------------------------------------------------------------
        */
        $operarios = collect();
        $estados = collect();

        if ($usuario->id_rol == 2) {

            $operarios = \App\Models\User::query()
                ->where('id_empresa', $usuario->id_empresa)
                ->where('id_rol', 3)
                ->orderBy('name')
                ->get();

        }

        $estados = \App\Models\EstadoCita::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | VISTA
        |--------------------------------------------------------------------------
        |
        | Cada rol tendrá una experiencia diferente.
        |
        */
        if ($usuario->id_rol == 3) {

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


    /**
     * Mostrar formulario para crear una cita.
     */
public function create()
{
    $usuario = auth()->user();

    if ($usuario->id_rol != 2) {
        abort(403);
    }

    $clientes = \App\Models\Cliente::query()
        ->where('id_empresa', $usuario->id_empresa)
        ->orderBy('nombre')
        ->orderBy('apellido_paterno')
        ->get();

    $operarios = \App\Models\User::query()
        ->where('id_empresa', $usuario->id_empresa)
        ->where('id_rol', 3)
        ->orderBy('name')
        ->get();

    $estados = \App\Models\EstadoCita::query()
        ->where('estado', true)
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
}  /**
     * Guardar una nueva cita.
     */
   public function store(Request $request)
{
    $usuario = auth()->user();

    if ($usuario->id_rol != 2) {
        abort(403);
    }

    $datos = $request->validate([
        'id_cliente' => 'required|integer',
        'id_usuario' => 'required|integer',
        'id_llamada' => 'nullable|integer',
        'fecha_hora_inicio' => 'required|date',
        'motivo' => 'nullable|string|max:255',
        'observaciones' => 'nullable|string|max:1000',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Verificar que el operario pertenezca a la empresa
    |--------------------------------------------------------------------------
    */

    \App\Models\User::query()
        ->where('id', $datos['id_usuario'])
        ->where('id_empresa', $usuario->id_empresa)
        ->where('id_rol', 3)
        ->firstOrFail();


    /*
    |--------------------------------------------------------------------------
    | Verificar que el cliente pertenezca a la empresa
    |--------------------------------------------------------------------------
    */

    \App\Models\Cliente::query()
        ->where('id_cliente', $datos['id_cliente'])
        ->where('id_empresa', $usuario->id_empresa)
        ->firstOrFail();


    /*
    |--------------------------------------------------------------------------
    | Obtener automáticamente el estado "Pendiente"
    |--------------------------------------------------------------------------
    */

    $estadoPendiente = \App\Models\EstadoCita::query()
        ->whereRaw('LOWER(nombre) = ?', ['pendiente'])
        ->where('estado', true)
        ->firstOrFail();


    /*
    |--------------------------------------------------------------------------
    | Crear la cita
    |--------------------------------------------------------------------------
    */

    $datos['id_estado_cita'] = $estadoPendiente->id_estado_cita;

    $datos['fecha_hora_fin'] = null;

    Cita::create($datos);


    return redirect()
        ->route('citas.index')
        ->with(
            'exito',
            'Cita registrada correctamente.'
        );
}


    /**
     * Mostrar una cita específica.
     */
   public function show($id_cita)
{
    $usuario = auth()->user();

    $consulta = Cita::query()
        ->with([
            'cliente',
            'usuario',
            'estadoCita',
        ]);

    if ($usuario->id_rol == 2) {

        $consulta->whereHas('usuario', function ($query) use ($usuario) {
            $query->where(
                'id_empresa',
                $usuario->id_empresa
            );
        });

    } elseif ($usuario->id_rol == 3) {

        $consulta->where(
            'id_usuario',
            $usuario->id
        );

    } else {

        abort(403);

    }

    $cita = $consulta->findOrFail($id_cita);

    return view(
        'citas.show',
        compact('cita')
    );
}

    /**
     * Mostrar formulario para editar una cita.
     */
    public function edit($id_cita)
    {
        $usuario = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Solamente Admin Cliente puede editar.
        |--------------------------------------------------------------------------
        */
        if ($usuario->id_rol != 2) {
            abort(403);
        }

        $cita = Cita::query()
            ->with([
                'cliente',
                'usuario',
                'estadoCita',
            ])
            ->whereHas('usuario', function ($query) use ($usuario) {
                $query->where('id_empresa', $usuario->id_empresa);
            })
            ->findOrFail($id_cita);

        return view(
            'citas.edit',
            compact('cita')
        );
    }


    /**
     * Actualizar una cita.
     */
    public function update(
        Request $request,
        $id_cita
    ) {
        $usuario = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Solamente Admin Cliente puede actualizar.
        |--------------------------------------------------------------------------
        */
        if ($usuario->id_rol != 2) {
            abort(403);
        }

        $datos = $request->validate([
            'id_cliente' => 'required|integer',
            'id_usuario' => 'required|integer',
            'id_llamada' => 'nullable|integer',
            'id_estado_cita' => 'required|integer',
            'fecha_hora_inicio' => 'required|date',
            'fecha_hora_fin' => 'nullable|date|after_or_equal:fecha_hora_inicio',
            'motivo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Buscar únicamente una cita perteneciente a su empresa.
        |--------------------------------------------------------------------------
        */
        $cita = Cita::query()
            ->whereHas('usuario', function ($query) use ($usuario) {
                $query->where('id_empresa', $usuario->id_empresa);
            })
            ->findOrFail($id_cita);

        /*
        |--------------------------------------------------------------------------
        | Validar operario.
        |--------------------------------------------------------------------------
        */
        \App\Models\User::query()
            ->where('id', $datos['id_usuario'])
            ->where('id_empresa', $usuario->id_empresa)
            ->where('id_rol', 3)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Validar cliente.
        |--------------------------------------------------------------------------
        */
        \App\Models\Cliente::query()
            ->where('id_cliente', $datos['id_cliente'])
            ->where('id_empresa', $usuario->id_empresa)
            ->firstOrFail();

        $cita->update($datos);

        return redirect()
            ->route('citas.index')
            ->with(
                'exito',
                'Cita actualizada correctamente.'
            );
    }


    /**
     * Eliminar una cita.
     */
    public function destroy($id_cita)
    {
        $usuario = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Solamente Admin Cliente puede eliminar.
        |--------------------------------------------------------------------------
        */
        if ($usuario->id_rol != 2) {
            abort(403);
        }

        $cita = Cita::query()
            ->whereHas('usuario', function ($query) use ($usuario) {
                $query->where('id_empresa', $usuario->id_empresa);
            })
            ->findOrFail($id_cita);

        $cita->delete();

        return redirect()
            ->route('citas.index')
            ->with(
                'exito',
                'Cita eliminada correctamente.'
            );
    }
}