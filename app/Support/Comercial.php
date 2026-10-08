<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Comercial
{
    public const ESTADOS = [
        'nuevo' => 'Nuevo', 'asignado' => 'Asignado',
        'en_seguimiento' => 'En seguimiento', 'cotizacion_enviada' => 'Cotización enviada',
        'contratado' => 'Contratado', 'no_interesado' => 'No interesado',
    ];

    public static function rol(): ?int
    {
        $id = DB::table('roles')->where('nombre', config('comercial.rol_nombre'))
            ->where('activo', true)->value('id_rol');
        return $id === null ? null : (int) $id;
    }

    public static function asesor($usuario): bool
    {
        $rol = self::rol();
        return $usuario && $rol !== null && (int) $usuario->id_rol === $rol
            && (int) $usuario->id_empresa === (int) config('comercial.empresa_id');
    }

    public static function empresa(): int
    {
        $id = (int) config('comercial.empresa_id');
        abort_unless($id > 0 && DB::table('empresas')->where('id_empresa', $id)->where('activo', true)->exists(),
            503, 'Configura COMERCIAL_EMPRESA_ID con la empresa interna activa de la plataforma.');
        return $id;
    }

    /** Empresas de origen; nunca determina la empresa de los asesores. */
    public static function empresasOrigen(): array
    {
        self::empresa();
        $valor = config('comercial.comi_empresa_ids', config('comercial.empresa_id'));
        abort_unless(is_string($valor) || is_int($valor), 503,
            'Configura COMI_EMPRESA_IDS con IDs positivos separados por comas.');

        $ids = [];
        foreach (explode(',', (string) $valor) as $parte) {
            $id = filter_var(trim($parte), FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 2147483647],
            ]);
            abort_if($id === false, 503,
                'Configura COMI_EMPRESA_IDS con IDs positivos separados por comas.');
            $ids[] = $id;
        }
        $ids = array_values(array_unique($ids));
        $existentes = DB::table('empresas')->whereIn('id_empresa', $ids)
            ->where('activo', true)->count();
        abort_unless($existentes === count($ids), 503,
            'Cada ID de COMI_EMPRESA_IDS debe corresponder a una empresa activa.');

        return $ids;
    }

    public static function asesores()
    {
        return DB::table('usuarios')->where('id_empresa', self::empresa())
            ->where('id_rol', self::rol())->whereNull('deleted_at');
    }
}
