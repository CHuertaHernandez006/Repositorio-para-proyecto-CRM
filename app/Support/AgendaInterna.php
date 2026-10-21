<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class AgendaInterna
{
    /** IDs explícitos de COMICENTER e InterAcom; no se unen datos de otros tenants. */
    public static function empresas(): array
    {
        $valor = config('comercial.agenda_empresa_ids', '');
        abort_unless(is_string($valor) && preg_match('/^\s*[1-9][0-9]*\s*,\s*[1-9][0-9]*\s*$/', $valor),
            503, 'Configura exactamente dos IDs positivos separados por coma en COMERCIAL_AGENDA_EMPRESA_IDS.');
        $ids = array_values(array_unique(array_map('intval', explode(',', $valor))));
        abort_unless(count($ids) === 2 && min($ids) > 0, 503,
            'Configura COMERCIAL_AGENDA_EMPRESA_IDS con los dos IDs de COMICENTER e InterAcom.');
        abort_unless(in_array(Comercial::empresa(), $ids, true), 503,
            'La empresa propietaria del equipo comercial debe estar entre las dos empresas internas.');
        abort_unless(DB::table('empresas')->whereIn('id_empresa', $ids)->where('activo', true)->count() === 2,
            503, 'Las dos empresas internas deben existir y estar activas.');
        return $ids;
    }

    public static function gestor($usuario): bool
    {
        return $usuario && $usuario->activo && !$usuario->deleted_at
            && ((int) $usuario->id_rol === 1 ||
                ((int) $usuario->id_rol === 2 && in_array((int) $usuario->id_empresa, self::empresas(), true)));
    }

    public static function autorizar($usuario): void
    {
        abort_unless($usuario && $usuario->activo && !$usuario->deleted_at &&
            (self::gestor($usuario) || Comercial::asesor($usuario)), 403);
    }

    public static function consulta($usuario)
    {
        self::autorizar($usuario);
        $q = DB::table('comi_citas_agenda as c')
            ->join('comi_agendas as ag', 'ag.id_agenda', '=', 'c.id_agenda')
            ->join('comi_proyectos as pr', 'pr.id_proyecto', '=', 'c.id_proyecto')
            ->leftJoin('comi_cita_asignaciones as asig', 'asig.id_cita', '=', 'c.id_cita')
            ->leftJoin('usuarios as asesor', 'asesor.id_usuario', '=', 'asig.id_asesor')
            ->leftJoin('clientes as cl', function ($join) {
                $join->on('cl.id_cliente', '=', 'c.id_cliente')->on('cl.id_empresa', '=', 'c.id_empresa');
            })
            ->leftJoin('citas_agenda as anterior', 'anterior.conversation_id', '=', 'c.id_conversacion')
            ->leftJoin('comercial_prospectos as prospecto', function ($join) {
                $join->on('prospecto.id_empresa_origen', '=', 'c.id_empresa')
                    ->on('prospecto.id_cliente', '=', 'c.id_cliente')
                    ->where('prospecto.id_empresa', Comercial::empresa());
            })
            ->whereIn('c.id_empresa', self::empresas())
            ->whereColumn('ag.id_empresa', 'c.id_empresa')
            ->whereColumn('pr.id_empresa', 'c.id_empresa')
            ->where('ag.activo', true)->where('pr.activo', true)
            ->where('pr.tipo', 'comercial');
        if (!self::gestor($usuario)) {
            $q->where('asig.id_asesor', $usuario->getKey());
        }
        return $q->select([
                'c.*', 'ag.nombre as agenda_nombre', 'ag.zona_horaria', 'ag.duracion_minutos',
                'pr.nombre as proyecto_nombre', 'asig.id_asesor', 'asig.correo_enviado_at',
                'asig.correo_error', 'asig.correo_destino', 'prospecto.id as prospecto_id', 'prospecto.id_asesor as prospecto_asesor',
            ])
            ->selectRaw("COALESCE(NULLIF(concat_ws(' ', cl.nombre, cl.apellido_paterno), ''), anterior.nombre_contacto, 'Contacto COMI') as contacto_nombre")
            ->selectRaw("COALESCE(NULLIF(cl.organizacion, ''), anterior.nombre_empresa, '') as empresa_nombre")
            ->selectRaw("COALESCE(NULLIF(cl.correo, ''), anterior.correo) as correo_contacto")
            ->selectRaw("COALESCE(NULLIF(cl.telefono_principal, ''), cl.telefono_secundario, anterior.telefono) as telefono_contacto")
            ->selectRaw("concat_ws(' ', asesor.nombre, asesor.apellido_paterno) as asesor_nombre");
    }
}
