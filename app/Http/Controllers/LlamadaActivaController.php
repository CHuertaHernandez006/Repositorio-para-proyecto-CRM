<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LlamadaActivaController extends Controller
{
    /**
     * Devuelve la llamada activa del operario autenticado.
     *
     * El widget consulta este endpoint periódicamente para saber
     * si debe mostrarse en pantalla.
     */
    public function show(): JsonResponse
    {
        $usuario = Auth::user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 3
        ) {
            abort(403);
        }

        if (!$usuario->id_empresa) {
            return response()->json([
                'activa' => false,
            ]);
        }

        $extension = trim(
            (string) (
                $usuario->extension_asterisk
                ?? ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | LLAMADA ABIERTA MÁS RECIENTE
        |--------------------------------------------------------------------------
        |
        | 1. Llamadas asignadas directamente al operario.
        | 2. Llamadas entrantes dirigidas a su extensión Asterisk.
        |
        | El límite de 4 horas evita que un registro antiguo sin fecha_fin
        | permanezca eternamente como "llamada activa" en el widget.
        |--------------------------------------------------------------------------
        */

        $consulta = DB::table('llamadas as l')
            ->leftJoin(
                'clientes as c',
                function ($join) {
                    $join
                        ->on(
                            'c.id_cliente',
                            '=',
                            'l.id_cliente'
                        )
                        ->on(
                            'c.id_empresa',
                            '=',
                            'l.id_empresa'
                        );
                }
            )
            ->leftJoin(
                'campanas as ca',
                function ($join) {
                    $join
                        ->on(
                            'ca.id_campana',
                            '=',
                            'l.id_campana'
                        )
                        ->on(
                            'ca.id_empresa',
                            '=',
                            'l.id_empresa'
                        );
                }
            )
            ->leftJoin(
                'estados_llamada as el',
                'el.id_estado_llamada',
                '=',
                'l.id_estado_llamada'
            )
            ->where(
                'l.id_empresa',
                $usuario->id_empresa
            )
            ->whereNull('l.fecha_fin')
            ->where(
                'l.fecha_inicio',
                '>=',
                now()->subHours(4)
            )
            ->where(
                function ($query) use (
                    $usuario,
                    $extension
                ) {
                    $query->where(
                        'l.id_usuario',
                        $usuario->id_usuario
                    );

                    if ($extension !== '') {
                        $query->orWhere(
                            function ($incoming) use (
                                $extension
                            ) {
                                $incoming
                                    ->where(
                                        'l.tipo_llamada',
                                        'entrante'
                                    )
                                    ->where(
                                        'l.numero_destino',
                                        $extension
                                    );
                            }
                        );
                    }
                }
            )
            ->orderByDesc('l.fecha_inicio')
            ->select([
                'l.id_llamada',
                'l.tipo_llamada',
                'l.fecha_inicio',
                'l.numero_origen',
                'l.numero_destino',
                'l.identificador_asterisk',
                'l.id_usuario',
                'c.id_cliente',
                'c.nombre as cliente_nombre',
                'c.apellido_paterno as cliente_apellido_paterno',
                'c.apellido_materno as cliente_apellido_materno',
                'c.telefono_principal',
                'c.organizacion',
                'ca.nombre as campana_nombre',
                'el.nombre as estado_nombre',
            ]);

        $llamada = $consulta->first();

        if (!$llamada) {
            return response()->json([
                'activa' => false,
            ]);
        }

        $nombreCliente = trim(
            implode(
                ' ',
                array_filter([
                    $llamada->cliente_nombre,
                    $llamada->cliente_apellido_paterno,
                    $llamada->cliente_apellido_materno,
                ])
            )
        );

        if ($nombreCliente === '') {
            $nombreCliente = 'Cliente';
        }

        $telefono =
            $llamada->tipo_llamada === 'entrante'
                ? $llamada->numero_origen
                : $llamada->numero_destino;

        if (!$telefono) {
            $telefono =
                $llamada->telefono_principal
                ?: 'Sin número';
        }

        $iniciales = $this->obtenerIniciales(
            $nombreCliente
        );

        return response()->json([
            'activa' => true,

            'llamada' => [
                'id' =>
                    (int) $llamada->id_llamada,

                'tipo' =>
                    $llamada->tipo_llamada,

                'tipo_texto' =>
                    $llamada->tipo_llamada === 'entrante'
                        ? 'Entrante'
                        : 'Saliente',

                'estado' =>
                    $llamada->estado_nombre
                    ?: 'En curso',

                'fecha_inicio' =>
                    Carbon::parse(
                        $llamada->fecha_inicio
                    )->toIso8601String(),

                'cliente' =>
                    $nombreCliente,

                'iniciales' =>
                    $iniciales,

                'telefono' =>
                    (string) $telefono,

                'organizacion' =>
                    $llamada->organizacion
                    ?: 'Sin organización',

                'campana' =>
                    $llamada->campana_nombre
                    ?: 'Sin campaña',

                'extension' =>
                    $extension !== ''
                        ? $extension
                        : null,

                'identificador_asterisk' =>
                    $llamada->identificador_asterisk,

                'detalle_url' =>
                    route(
                        'llamadas.show',
                        $llamada->id_llamada
                    ),
            ],
        ]);
    }

    /**
     * Iniciales para el avatar del widget.
     */
    private function obtenerIniciales(
        string $nombre
    ): string {
        $partes = preg_split(
            '/\s+/u',
            trim($nombre)
        ) ?: [];

        $partes = array_values(
            array_filter($partes)
        );

        if (count($partes) === 0) {
            return 'CL';
        }

        $primera = mb_substr(
            $partes[0],
            0,
            1,
            'UTF-8'
        );

        $segunda =
            count($partes) > 1
                ? mb_substr(
                    $partes[1],
                    0,
                    1,
                    'UTF-8'
                )
                : '';

        return mb_strtoupper(
            $primera . $segunda,
            'UTF-8'
        );
    }
}
