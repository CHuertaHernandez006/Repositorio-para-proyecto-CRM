<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/** Lectura de citas: no confirma, reasigna ni copia registros. */
class CitasProspecto
{
    public const ESTADOS = [
        'pendiente_aprobacion' => 'Pendiente de aprobación',
        'confirmada' => 'Confirmada', 'rechazada' => 'Rechazada',
        'cancelada' => 'Cancelada', 'reprogramada' => 'Reprogramada',
    ];

    public static function sqlRegistros(): string
    {
        return <<<'SQL'
SELECT ca.id_empresa, COALESCE(ca.id_cliente, co.id_cliente) AS id_cliente,
       ca.id_cita AS cita_id, 'comi'::text AS cita_fuente,
       ca.fecha_hora_inicio AS cita_fecha, ca.estado AS cita_estado,
       ag.nombre AS cita_agenda, ag.zona_horaria,
       asig.id_asesor AS cita_id_asesor,
       NULLIF(concat_ws(' ', u.nombre, u.apellido_paterno), '') AS cita_asesor
FROM comi_citas_agenda ca
JOIN comi_agendas ag ON ag.id_agenda = ca.id_agenda AND ag.id_empresa = ca.id_empresa
JOIN comi_proyectos pr ON pr.id_proyecto = ca.id_proyecto AND pr.id_empresa = ca.id_empresa
LEFT JOIN conversaciones co ON co.id_conversacion = ca.id_conversacion AND co.id_empresa = ca.id_empresa
LEFT JOIN comi_cita_asignaciones asig ON asig.id_cita = ca.id_cita
LEFT JOIN usuarios u ON u.id_usuario = asig.id_asesor
WHERE ag.activo = TRUE AND pr.activo = TRUE AND pr.tipo = 'comercial'
  AND COALESCE(ca.id_cliente, co.id_cliente) IS NOT NULL
UNION ALL
SELECT co.id_empresa, co.id_cliente, anterior.id::bigint, 'anterior'::text,
       anterior.fecha_hora_cita, anterior.estado, 'Agenda anterior'::text,
       'America/Mexico_City'::varchar, NULL::integer, NULL::text
FROM citas_agenda anterior
JOIN conversaciones co ON co.id_conversacion = anterior.conversation_id
WHERE co.id_cliente IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM comi_citas_agenda nueva
                  WHERE nueva.id_conversacion = anterior.conversation_id)
  AND (
      NOT EXISTS (SELECT 1 FROM comi_proyecto_conversaciones pc
                  WHERE pc.id_conversacion = co.id_conversacion)
      OR EXISTS (SELECT 1 FROM comi_proyecto_conversaciones pc
                 JOIN comi_proyectos pr ON pr.id_proyecto = pc.id_proyecto
                   AND pr.id_empresa = co.id_empresa
                 WHERE pc.id_conversacion = co.id_conversacion AND pr.tipo = 'comercial' AND pr.activo = TRUE)
  )
SQL;
    }

    public static function sqlResumen(): string
    {
        $registros = self::sqlRegistros();
        return <<<SQL
WITH registros AS ($registros), ordenadas AS (
    SELECT registros.*, COUNT(*) OVER (PARTITION BY id_empresa, id_cliente) AS cita_total,
           ROW_NUMBER() OVER (
               PARTITION BY id_empresa, id_cliente
               ORDER BY CASE
                   WHEN cita_estado = 'confirmada' AND cita_fecha >= CURRENT_TIMESTAMP THEN 0
                   WHEN cita_estado = 'pendiente_aprobacion' AND cita_fecha >= CURRENT_TIMESTAMP THEN 1
                   ELSE 2 END,
                   CASE WHEN cita_estado IN ('confirmada', 'pendiente_aprobacion')
                        AND cita_fecha >= CURRENT_TIMESTAMP THEN cita_fecha END ASC NULLS LAST,
                   cita_fecha DESC, cita_id DESC, cita_fuente ASC
           ) AS posicion
    FROM registros
)
SELECT *, (cita_fecha < CURRENT_TIMESTAMP) AS cita_pasada
FROM ordenadas WHERE posicion = 1
SQL;
    }

    public static function resumen()
    {
        return DB::query()->fromRaw('('.self::sqlResumen().') AS resumen_citas')
            ->whereIn('id_empresa', Comercial::empresasOrigen());
    }

    public static function registros($prospecto)
    {
        abort_unless(in_array((int) $prospecto->id_empresa_origen, Comercial::empresasOrigen(), true), 404);
        return DB::query()->fromRaw('('.self::sqlRegistros().') AS citas_prospecto')
            ->where('id_empresa', $prospecto->id_empresa_origen)
            ->where('id_cliente', $prospecto->id_cliente);
    }
}
