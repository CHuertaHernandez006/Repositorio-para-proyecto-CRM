<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\User;
use App\Models\EstadoCita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
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

        $consulta = Cita::query()->with([
            'cliente',
            'usuario',
            'estadoCita',
        ]);

        /*
        |--------------------------------------------------------------------------
        | PERMISOS
        |--------------------------------------------------------------------------
        */

        if ($usuario->id_rol == 2) {

            $consulta->whereHas('usuario', function ($query) use ($usuario) {
                $query->where('id_empresa', $usuario->id_empresa);
            });

        } elseif ($usuario->id_rol == 3) {

            $consulta->where('id_usuario', $usuario->id);

        }

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        if ($buscar !== '') {

            $termino = '%' . mb_strtolower($buscar, 'UTF-8') . '%';

            $consulta->whereHas('cliente', function ($query) use ($termino) {

                $query->where(function ($cliente) use ($termino) {

                    $cliente
                        ->whereRaw('LOWER(nombre) LIKE ?', [$termino])
                        ->orWhereRaw('LOWER(apellido_paterno) LIKE ?', [$termino])
                        ->orWhereRaw('LOWER(apellido_materno) LIKE ?', [$termino])
                        ->orWhereRaw('LOWER(empresa) LIKE ?', [$termino])
                        ->orWhereRaw('LOWER(correo) LIKE ?', [$termino])
                        ->orWhereRaw('LOWER(telefono_principal) LIKE ?', [$termino]);

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
        | RESULTADOS
        |--------------------------------------------------------------------------
        */

        $citas = $consulta
            ->orderBy('fecha_hora_inicio', 'asc')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | CATÁLOGOS
        |--------------------------------------------------------------------------
        */

        $operarios = collect();

        if ($usuario->id_rol == 2) {

            $operarios = User::query()
                ->where('id_empresa', $usuario->id_empresa)
                ->where('id_rol', 3)
                ->orderBy('name')
                ->get();

        }

        $estados = EstadoCita::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | VISTAS SEGÚN ROL
        |--------------------------------------------------------------------------
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


    public function create()
    {
        $usuario = auth()->user();

        if ($usuario->id_rol != 2) {
            abort(403);
        }

        $clientes = Cliente::query()
            ->where('id_empresa', $usuario->id_empresa)
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get();

        $operarios = User::query()
            ->where('id_empresa', $usuario->id_empresa)
            ->where('id_rol', 3)
            ->orderBy('name')
            ->get();

        $estados = EstadoCita::query()
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
    }


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
        | VALIDAR OPERARIO
        |--------------------------------------------------------------------------
        */

        User::query()
            ->where('id', $datos['id_usuario'])
            ->where('id_empresa', $usuario->id_empresa)
            ->where('id_rol', 3)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | VALIDAR CLIENTE
        |--------------------------------------------------------------------------
        */

        Cliente::query()
            ->where('id_cliente', $datos['id_cliente'])
            ->where('id_empresa', $usuario->id_empresa)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | ESTADO INICIAL
        |--------------------------------------------------------------------------
        */

        $estadoPendiente = EstadoCita::query()
            ->whereRaw('LOWER(nombre) = ?', ['pendiente'])
            ->where('estado', true)
            ->firstOrFail();

        $datos['id_estado_cita'] = $estadoPendiente->id_estado_cita;

        /*
        |--------------------------------------------------------------------------
        | LA CITA TODAVÍA NO TERMINA
        |--------------------------------------------------------------------------
        */

        $datos['fecha_hora_fin'] = null;

        /*
        |--------------------------------------------------------------------------
        | CREAR CITA
        |--------------------------------------------------------------------------
        */

        Cita::create($datos);

        return redirect()
            ->route('citas.index')
            ->with(
                'exito',
                'Cita registrada correctamente.'
            );
    }


    public function show($id_cita)
    {
        $usuario = auth()->user();

        $consulta = Cita::query()
            ->with([
                'cliente',
                'usuario',
                'estadoCita',
            ]);

        /*
        |--------------------------------------------------------------------------
        | PERMISOS
        |--------------------------------------------------------------------------
        */

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


    public function edit($id_cita)
    {
        $usuario = auth()->user();

        if ($usuario->id_rol != 2) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | OBTENER CITA
        |--------------------------------------------------------------------------
        */

        $cita = Cita::query()
            ->with([
                'cliente',
                'usuario',
                'estadoCita',
            ])
            ->whereHas('usuario', function ($query) use ($usuario) {

                $query->where(
                    'id_empresa',
                    $usuario->id_empresa
                );

            })
            ->findOrFail($id_cita);

        /*
        |--------------------------------------------------------------------------
        | OPERARIOS DE LA EMPRESA
        |--------------------------------------------------------------------------
        */

        $operarios = User::query()
            ->where(
                'id_empresa',
                $usuario->id_empresa
            )
            ->where(
                'id_rol',
                3
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ESTADOS DISPONIBLES
        |--------------------------------------------------------------------------
        */

        $estados = EstadoCita::query()
            ->where(
                'estado',
                true
            )
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


    public function update(Request $request, $id_cita)
    {
        $usuario = auth()->user();

        if ($usuario->id_rol != 2) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate([
            'id_cliente' => 'required|integer',
            'id_usuario' => 'required|integer',
            'id_llamada' => 'nullable|integer',
            'id_estado_cita' => 'required|integer',
            'fecha_hora_inicio' => 'required|date',
            'motivo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        /*
        |--------------------------------------------------------------------------
        | OBTENER CITA
        |--------------------------------------------------------------------------
        */

        $cita = Cita::query()
            ->whereHas('usuario', function ($query) use ($usuario) {

                $query->where(
                    'id_empresa',
                    $usuario->id_empresa
                );

            })
            ->findOrFail($id_cita);

        /*
        |--------------------------------------------------------------------------
        | VALIDAR OPERARIO
        |--------------------------------------------------------------------------
        */

        User::query()
            ->where(
                'id',
                $datos['id_usuario']
            )
            ->where(
                'id_empresa',
                $usuario->id_empresa
            )
            ->where(
                'id_rol',
                3
            )
            ->firstOrFail();

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
                $usuario->id_empresa
            )
            ->firstOrFail();

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
                'estado',
                true
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        | fecha_hora_fin NO viene del formulario.
        | Por lo tanto, editar una cita nunca modifica
        | su hora real de finalización.
        |
        */

        $cita->update($datos);

        return redirect()
            ->route('citas.index')
            ->with(
                'exito',
                'Cita actualizada correctamente.'
            );
    }


    public function destroy($id_cita)
    {
        $usuario = auth()->user();

        if ($usuario->id_rol != 2) {
            abort(403);
        }

        $cita = Cita::query()
            ->whereHas('usuario', function ($query) use ($usuario) {

                $query->where(
                    'id_empresa',
                    $usuario->id_empresa
                );

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