<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Devuelve una versión de los datos visibles para el usuario autenticado.
 * No publica clientes, llamadas ni citas; solo una huella de cambios.
 *
 * Si el listado de tu CRM usa más filtros que estos, es necesario
 * aplicar esos mismos filtros aquí antes de usarlo en producción.
 */
class CRMRefreshController extends Controller
{
    public function version(Request $request): JsonResponse
    {
        $usuario = $request->user();
        abort_unless($usuario, 401);

        $rol = (int) $usuario->id_rol;
        $empresa = (int) $usuario->id_empresa;
        $usuarioId = (int) ($usuario->id_usuario ?? $usuario->getAuthIdentifier());
        $superAdmin = $rol === 1;
        $operario = $rol === 3;

        // Citas propias del CRM.
        $citas = DB::table('citas');
        if (! $superAdmin) {
            $citas->where('id_empresa', $empresa);
        }
        if ($operario) {
            $citas->where('id_usuario', $usuarioId);
        }

        // Citas de las agendas de COMI (separadas de la tabla 'citas').
        $comiCitas = DB::table('comi_citas_agenda');
        if (! $superAdmin) {
            $comiCitas->where('id_empresa', $empresa);
        }
        if ($operario) {
            $comiCitas->whereExists(function ($subconsulta) use ($usuarioId) {
                $subconsulta->selectRaw('1')
                    ->from('comi_agenda_usuarios')
                    ->whereColumn('comi_agenda_usuarios.id_agenda', 'comi_citas_agenda.id_agenda')
                    ->whereColumn('comi_agenda_usuarios.id_empresa', 'comi_citas_agenda.id_empresa')
                    ->where('comi_agenda_usuarios.id_usuario', $usuarioId)
                    ->where('comi_agenda_usuarios.puede_ver', true);
            });
        }

        // Leads / clientes de la empresa.
        $clientes = DB::table('clientes')->whereNull('deleted_at');
        if (! $superAdmin) {
            $clientes->where('id_empresa', $empresa);
        }
        if ($operario) {
            $clientes->where('id_usuario_responsable', $usuarioId);
        }

        // Citas del flujo standalone de COMI. Como citas_agenda no contiene
        // id_empresa, para usuarios no globales se filtra por conversación.
        // Las citas standalone SIN conversación en 'conversaciones' no se
        // pueden asignar con seguridad a un tenant con este esquema.
        $citasStandalone = DB::table('citas_agenda as ca');
        if (! $superAdmin) {
            $citasStandalone
                ->join('conversaciones as conv', 'conv.id_conversacion', '=', 'ca.conversation_id')
                ->where('conv.id_empresa', $empresa);
        }
        if ($operario) {
            $citasStandalone->whereExists(function ($subconsulta) use ($usuarioId) {
                $subconsulta->selectRaw('1')
                    ->from('clientes as cl')
                    ->whereColumn('cl.id_cliente', 'conv.id_cliente')
                    ->whereColumn('cl.id_empresa', 'conv.id_empresa')
                    ->where('cl.id_usuario_responsable', $usuarioId);
            });
        }

        $estado = [
            'citas_crm'       => $this->firma($citas, 'updated_at'),
            'citas_comi'      => $this->firma($comiCitas, 'updated_at'),
            'clientes'        => $this->firma($clientes, 'updated_at'),
            'citas_standalone'=> $this->firma($citasStandalone, 'ca.updated_at'),
        ];

        return response()
            ->json(['version' => hash('sha256', json_encode($estado, JSON_THROW_ON_ERROR))])
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate');
    }

    private function firma(Builder $consulta, string $columna): array
    {
        $fila = $consulta
            ->selectRaw('COUNT(*) AS total, MAX(' . $columna . ') AS ultima_actualizacion')
            ->first();

        return [
            'total' => (int) $fila->total,
            'ultima_actualizacion' => $fila->ultima_actualizacion,
        ];
    }
}
