<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AsteriskTelefoniaController extends Controller
{
    private function tokenValido(Request $request): bool
    {
        $esperado = trim((string) config('asterisk.webhook_token', ''));
        $recibido = trim((string) $request->header('X-Asterisk-Token', ''));

        return $esperado !== ''
            && $recibido !== ''
            && hash_equals($esperado, $recibido);
    }

    private function texto(string $contenido, int $estado = 200): Response
    {
        return response($contenido, $estado)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Cache-Control', 'no-store, no-cache, max-age=0')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Obtiene las asignaciones operativas de una extensión.
     * Se exige una asignación inequívoca.
     */
    private function asignacionesExtension(string $extension)
    {
        return DB::table('extensiones_asterisk as e')
            ->join('usuarios as u', function ($join) {
                $join->on('u.id_empresa', '=', 'e.id_empresa')
                    ->on('u.extension_asterisk', '=', 'e.extension');
            })
            ->join(
                'tipos_operario as t',
                't.id_tipo_operario',
                '=',
                'u.id_tipo_operario'
            )
            ->join('webrtc_credenciales as wc', function ($join) {
                $join->on('wc.id_usuario', '=', 'u.id_usuario')
                    ->on('wc.extension_asterisk', '=', 'e.extension');
            })
            ->where('wc.estado', 'provisionada')
            ->where('e.extension', $extension)
            ->where('e.activa', true)
            ->where('u.id_rol', 3)
            ->where('u.activo', true)
            ->whereNull('u.deleted_at')
            ->where('t.activo', true)
            ->whereRaw(
                'LOWER(TRIM(t.nombre)) IN (?, ?, ?, ?)',
                ['asignacion', 'asignación', 'prospeccion', 'prospección']
            )
            ->select([
                'e.extension',
                'e.id_empresa',
                'u.id_usuario',
            ])
            ->get();
    }

    /**
     * Autoriza llamadas internas únicamente entre extensiones
     * asignadas y operativas de la misma empresa.
     */
    public function autorizarInterna(Request $request): Response
    {
        if (!$this->tokenValido($request)) {
            return $this->texto('false', 401);
        }

        $origen = trim((string) $request->query('origen', ''));
        $destino = trim((string) $request->query('destino', ''));

        if (
            !preg_match('/^\d{4}$/D', $origen)
            || !preg_match('/^\d{4}$/D', $destino)
            || $origen === $destino
        ) {
            return $this->texto('false');
        }

        $origenes = $this->asignacionesExtension($origen);
        $destinos = $this->asignacionesExtension($destino);

        if ($origenes->count() !== 1 || $destinos->count() !== 1) {
            return $this->texto('false');
        }

        $permitida = (int) $origenes->first()->id_empresa
            === (int) $destinos->first()->id_empresa;

        return $this->texto($permitida ? 'true' : 'false');
    }

    /**
     * Devuelve el grupo general de extensiones entrantes.
     * Incluye usuarios activos, extensiones activas y credenciales
     * SIP efectivamente provisionadas, sin filtrar por empresa.
     */
    public function destinosEntrantes(Request $request): Response
    {
        if (!$this->tokenValido($request)) {
            return $this->texto('false', 401);
        }

        $extensiones = DB::table('extensiones_asterisk as e')
            ->join('usuarios as u', function ($join) {
                $join->on('u.id_empresa', '=', 'e.id_empresa')
                    ->on('u.extension_asterisk', '=', 'e.extension');
            })
            ->join(
                'tipos_operario as t',
                't.id_tipo_operario',
                '=',
                'u.id_tipo_operario'
            )
            ->join('webrtc_credenciales as wc', function ($join) {
                $join->on('wc.id_usuario', '=', 'u.id_usuario')
                    ->on('wc.extension_asterisk', '=', 'e.extension');
            })
            ->where('e.activa', true)
            ->where('u.id_rol', 3)
            ->where('u.activo', true)
            ->whereNull('u.deleted_at')
            ->where('t.activo', true)
            ->whereRaw(
                'LOWER(TRIM(t.nombre)) IN (?, ?, ?, ?)',
                ['asignacion', 'asignación', 'prospeccion', 'prospección']
            )
            ->where('wc.estado', 'provisionada')
            ->select('e.extension')
            ->groupBy('e.extension')
            ->havingRaw('COUNT(DISTINCT e.id_empresa) = 1')
            ->havingRaw('COUNT(DISTINCT u.id_usuario) = 1')
            ->orderBy('e.extension')
            ->pluck('e.extension');

        $destinos = $extensiones
            ->map(fn ($extension) => 'PJSIP/' . $extension)
            ->implode('&');

        return $this->texto($destinos);
    }
}
