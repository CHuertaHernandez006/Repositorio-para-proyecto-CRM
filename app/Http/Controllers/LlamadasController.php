<?php

namespace App\Http\Controllers;

use App\Models\EstadoLlamada;
use App\Models\Llamada;
use App\Models\ResultadoLlamada;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LlamadasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO / HISTORIAL DE LLAMADAS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        $this->autorizarRol($usuario);

        $datos = $request->validate([
            'buscar' => [
                'nullable',
                'string',
                'max:150',
            ],

            'tipo' => [
                'nullable',
                'in:entrante,saliente',
            ],

            'estado' => [
                'nullable',
                'integer',
                'exists:estados_llamada,id_estado_llamada',
            ],

            'resultado' => [
                'nullable',
                'integer',
                'exists:resultados_llamada,id_resultado',
            ],

            'operario' => [
                'nullable',
                'integer',
            ],

            'fecha_desde' => [
                'nullable',
                'date',
            ],

            'fecha_hasta' => [
                'nullable',
                'date',
                'after_or_equal:fecha_desde',
            ],
        ]);

        $consulta = $this
            ->consultaLlamadasVisibles($usuario)
            ->with([
                'cliente',
                'usuario',
                'campana',
                'estadoLlamada',
                'resultado',
            ]);

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA GENERAL
        |--------------------------------------------------------------------------
        */

        if (!empty($datos['buscar'])) {
            $buscar =
                mb_strtolower(
                    trim($datos['buscar']),
                    'UTF-8'
                );

            $termino =
                '%' .
                str_replace(
                    ['%', '_'],
                    ['\%', '\_'],
                    $buscar
                ) .
                '%';

            $consulta->where(
                function (Builder $query) use ($termino) {
                    $query
                        ->whereRaw(
                            "LOWER(COALESCE(numero_origen, '')) LIKE ? ESCAPE '\\'",
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(COALESCE(numero_destino, '')) LIKE ? ESCAPE '\\'",
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(COALESCE(identificador_asterisk, '')) LIKE ? ESCAPE '\\'",
                            [$termino]
                        )
                        ->orWhereRaw(
                            "LOWER(COALESCE(observaciones, '')) LIKE ? ESCAPE '\\'",
                            [$termino]
                        )
                        ->orWhereHas(
                            'cliente',
                            function (Builder $cliente) use ($termino) {
                                $cliente->where(
                                    function (Builder $nombre) use ($termino) {
                                        $nombre
                                            ->whereRaw(
                                                "LOWER(nombre) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            )
                                            ->orWhereRaw(
                                                "LOWER(apellido_paterno) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            )
                                            ->orWhereRaw(
                                                "LOWER(COALESCE(apellido_materno, '')) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            )
                                            ->orWhereRaw(
                                                "LOWER(CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno)) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            )
                                            ->orWhereRaw(
                                                "LOWER(COALESCE(telefono_principal, '')) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            )
                                            ->orWhereRaw(
                                                "LOWER(COALESCE(correo, '')) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            );
                                    }
                                );
                            }
                        )
                        ->orWhereHas(
                            'usuario',
                            function (Builder $operario) use ($termino) {
                                $operario->where(
                                    function (Builder $nombre) use ($termino) {
                                        $nombre
                                            ->whereRaw(
                                                "LOWER(nombre) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            )
                                            ->orWhereRaw(
                                                "LOWER(apellido_paterno) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            )
                                            ->orWhereRaw(
                                                "LOWER(CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno)) LIKE ? ESCAPE '\\'",
                                                [$termino]
                                            );
                                    }
                                );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        if (!empty($datos['tipo'])) {
            $consulta->where(
                'tipo_llamada',
                $datos['tipo']
            );
        }

        if (!empty($datos['estado'])) {
            $consulta->where(
                'id_estado_llamada',
                $datos['estado']
            );
        }

        if (!empty($datos['resultado'])) {
            $consulta->where(
                'id_resultado',
                $datos['resultado']
            );
        }

        if (
            !empty($datos['operario']) &&
            (int) $usuario->id_rol !== 3
        ) {
            $consulta->where(
                'id_usuario',
                $datos['operario']
            );
        }

        if (!empty($datos['fecha_desde'])) {
            $consulta->whereDate(
                'fecha_inicio',
                '>=',
                $datos['fecha_desde']
            );
        }

        if (!empty($datos['fecha_hasta'])) {
            $consulta->whereDate(
                'fecha_inicio',
                '<=',
                $datos['fecha_hasta']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINACIÓN
        |--------------------------------------------------------------------------
        */

        $llamadas = $consulta
            ->orderByDesc('fecha_inicio')
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | CATÁLOGOS
        |--------------------------------------------------------------------------
        */

        $estados = EstadoLlamada::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $resultados = ResultadoLlamada::query()
            ->where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | OPERARIOS PARA FILTRO
        |--------------------------------------------------------------------------
        */

        $operarios = collect();

        if ((int) $usuario->id_rol === 1) {
            $operarios = User::query()
                ->where('id_rol', 3)
                ->where('activo', true)
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->get();
        }

        if ((int) $usuario->id_rol === 2) {
            $operarios = User::query()
                ->where(
                    'id_empresa',
                    $usuario->id_empresa
                )
                ->where('id_rol', 3)
                ->where('activo', true)
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | KPIs
        |--------------------------------------------------------------------------
        */

        $baseKpis =
            $this->consultaLlamadasVisibles(
                $usuario
            );

        $totalLlamadas =
            (clone $baseKpis)->count();

        $llamadasEntrantes =
            (clone $baseKpis)
                ->where(
                    'tipo_llamada',
                    'entrante'
                )
                ->count();

        $llamadasSalientes =
            (clone $baseKpis)
                ->where(
                    'tipo_llamada',
                    'saliente'
                )
                ->count();

        $llamadasEnCurso =
            (clone $baseKpis)
                ->whereNull('fecha_fin')
                ->count();

        $llamadasFinalizadas =
            (clone $baseKpis)
                ->whereNotNull('fecha_fin')
                ->count();

        $duracionPromedio =
            (int) round(
                (float) (
                    (clone $baseKpis)
                        ->whereNotNull('duracion')
                        ->avg('duracion')
                    ?? 0
                )
            );

        return view(
            'llamadas.index',
            compact(
                'llamadas',
                'estados',
                'resultados',
                'operarios',
                'totalLlamadas',
                'llamadasEntrantes',
                'llamadasSalientes',
                'llamadasEnCurso',
                'llamadasFinalizadas',
                'duracionPromedio'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DETALLE DE LLAMADA
    |--------------------------------------------------------------------------
    */

    public function show($id_llamada)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        $this->autorizarRol($usuario);

        $llamada = $this
            ->consultaLlamadasVisibles($usuario)
            ->with([
                'empresa',
                'cliente',
                'usuario',
                'campana',
                'estadoLlamada',
                'resultado',
                'citas.estadoCita',
            ])
            ->findOrFail($id_llamada);

        return view(
            'llamadas.show',
            compact('llamada')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CONSULTA BASE SEGÚN ROL
    |--------------------------------------------------------------------------
    |
    | Rol 1: todas las llamadas.
    | Rol 2: llamadas de su empresa.
    | Rol 3: llamadas de su empresa asignadas a él.
    |
    | Si id_usuario es NULL, la llamada fue atendida solamente por COMI.
    |--------------------------------------------------------------------------
    */

    private function consultaLlamadasVisibles(
        User $usuario
    ): Builder {
        $consulta =
            Llamada::query();

        if ((int) $usuario->id_rol === 1) {
            return $consulta;
        }

        if ((int) $usuario->id_rol === 2) {
            return $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        if ((int) $usuario->id_rol === 3) {
            return $consulta
                ->where(
                    'id_empresa',
                    $usuario->id_empresa
                )
                ->where(
                    'id_usuario',
                    $usuario->id_usuario
                );
        }

        abort(403);
    }


    /*
    |--------------------------------------------------------------------------
    | ROLES AUTORIZADOS
    |--------------------------------------------------------------------------
    */

    private function autorizarRol(
        User $usuario
    ): void {
        if (
            !in_array(
                (int) $usuario->id_rol,
                [1, 2, 3],
                true
            )
        ) {
            abort(403);
        }
    }
}
