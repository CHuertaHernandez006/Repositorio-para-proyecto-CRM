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

    public static function asesores()
    {
        return DB::table('usuarios')->where('id_empresa', self::empresa())
            ->where('id_rol', self::rol())->whereNull('deleted_at');
    }
}
