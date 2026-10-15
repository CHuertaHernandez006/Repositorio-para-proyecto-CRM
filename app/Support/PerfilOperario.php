<?php

namespace App\Support;

/** Identifica el perfil por el catálogo; los IDs pueden variar entre instalaciones. */
class PerfilOperario
{
    public static function clave($tipo): ?string
    {
        if (!$tipo || !$tipo->activo) {
            return null;
        }
        $nombre = strtr(mb_strtolower(trim((string) $tipo->nombre), 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        ]);
        return in_array($nombre, ['asignacion', 'prospeccion'], true) ? $nombre : null;
    }

    public static function tipo($usuario): ?string
    {
        return $usuario && (int) $usuario->id_rol === 3
            ? self::clave($usuario->tipoOperario) : null;
    }

    public static function esAsignacion($usuario): bool
    {
        return self::tipo($usuario) === 'asignacion';
    }

    public static function esProspeccion($usuario): bool
    {
        return self::tipo($usuario) === 'prospeccion';
    }

    public static function filtrarAsignacion($consulta)
    {
        return $consulta->whereHas('tipoOperario', function ($tipo) {
            $tipo->where('activo', true)
                ->whereRaw('LOWER(TRIM(nombre)) IN (?, ?)', ['asignación', 'asignacion']);
        });
    }
}
