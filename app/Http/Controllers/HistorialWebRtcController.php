<?php

namespace App\Http\Controllers;

use App\Support\AccesoClientes;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Diario PROVISIONAL de llamadas SIP.js, autenticado por sesión CRM.
 * No suplanta CDR/CEL ni confirma por sí mismo el hangup real de Asterisk.
 */
class HistorialWebRtcController extends Controller
{
    public function iniciar(Request $request): JsonResponse
    {
        $usuario = $this->operario($request);
        $datos = $request->validate([
            'sesion' => ['required', 'uuid'],
            'sentido' => ['required', 'in:entrante,saliente'],
            'destino' => ['nullable', 'string', 'max:40'],
            'origen' => ['nullable', 'string', 'max:40'],
        ]);

        $uuid = Str::lower((string) $datos['sesion']);
        $existente = DB::table('llamadas')->where('sesion_web', $uuid)->first();
        if ($existente) {
            $this->asegurarPropietario($existente, $usuario);
            return $this->sinCache(['id_llamada' => $existente->id_llamada, 'sesion' => $uuid, 'repetida' => true]);
        }

        $extension = trim((string) DB::table('usuarios')
            ->where('id_usuario', $usuario->id_usuario)
            ->where('id_empresa', $usuario->id_empresa)
            ->value('extension_asterisk'));
        abort_if($extension === '', 409, 'Tu usuario no tiene extensión SIP asignada.');
        abort_unless((bool) config('webrtc.enabled', false), 503, 'WebRTC está deshabilitado.');

        $sentido = $datos['sentido'];
        $clienteId = null;
        if ($sentido === 'saliente') {
            $destino = preg_replace('/\D/', '', (string) ($datos['destino'] ?? '')) ?? '';
            if (preg_match('/^\d{4}$/D', $destino)) {
                $this->validarExtensionInterna($usuario, $destino);
            } else {
                $destino = $this->normalizarExterno((string) ($datos['destino'] ?? ''));
                $clienteId = $this->clienteAutorizado($usuario, $destino, true);
            }
            $origen = $extension;
        } else {
            $destino = $extension; // Nunca confiar en una extensión de destino enviada por el navegador.
            $origen = preg_replace('/[^0-9+]/', '', (string) ($datos['origen'] ?? '')) ?? '';
            $origen = strlen($origen) <= 20 ? $origen : '';
            if (strlen(preg_replace('/\D/', '', $origen) ?? '') >= 10) {
                $normalizado = substr(preg_replace('/\D/', '', $origen), -10);
                $clienteId = $this->clienteAutorizado($usuario, $normalizado, false);
            }
        }

        $estado = $this->estado('En curso');
        $inicio = now();
        try {
            $id = DB::table('llamadas')->insertGetId([
                'id_empresa' => $usuario->id_empresa,
                'id_cliente' => $clienteId,
                'id_usuario' => $usuario->id_usuario,
                'id_estado_llamada' => $estado,
                'tipo_llamada' => $sentido,
                'numero_origen' => $origen,
                'numero_destino' => $destino,
                'fecha_inicio' => $inicio,
                'fecha_fin' => null,
                'duracion' => null,
                'sesion_web' => $uuid,
                'origen_registro' => 'webrtc',
                'ultimo_evento_web' => $inicio,
                'observaciones' => 'Registro provisional desde SIP.js; pendiente de corroboración con Asterisk.',
                'created_at' => $inicio,
                'updated_at' => $inicio,
            ], 'id_llamada');
        } catch (QueryException $e) {
            // Dos peticiones concurrentes con el mismo UUID: un solo registro.
            $existente = DB::table('llamadas')->where('sesion_web', $uuid)->first();
            if (!$existente) {
                throw $e;
            }
            $this->asegurarPropietario($existente, $usuario);
            $id = $existente->id_llamada;
        }

        return $this->sinCache(['id_llamada' => $id, 'sesion' => $uuid], 201);
    }

    public function evento(Request $request): JsonResponse
    {
        $usuario = $this->operario($request);
        $datos = $request->validate([
            'sesion' => ['required', 'uuid'],
            'evento' => ['required', 'in:contestada,terminada,rechazada,fallida,interrumpida,latido'],
        ]);
        $uuid = Str::lower($datos['sesion']);

        $resultado = DB::transaction(function () use ($usuario, $uuid, $datos) {
            $llamada = DB::table('llamadas')->where('sesion_web', $uuid)->lockForUpdate()->first();
            if (!$llamada || $llamada->origen_registro !== 'webrtc') {
                abort(404, 'No existe la sesión WebRTC.');
            }
            $this->asegurarPropietario($llamada, $usuario);

            // No reabrir ni volver a finalizar por eventos duplicados/retrasados.
            if ($llamada->fecha_fin !== null) {
                return ['estado' => 'cerrada', 'id_llamada' => $llamada->id_llamada];
            }
            $evento = $datos['evento'];
            $ahora = now();
            $cambios = ['ultimo_evento_web' => $ahora, 'updated_at' => $ahora];
            if ($evento === 'contestada') {
                if ($llamada->fecha_contestacion === null) {
                    $cambios['fecha_contestacion'] = $ahora;
                }
            } elseif ($evento === 'interrumpida') {
                // Perder el navegador NO demuestra un hangup real.
                $cambios['id_estado_llamada'] = $this->estado('Sin confirmar');
                $cambios['observaciones'] = $this->agregarNota($llamada->observaciones,
                    'Sesión del navegador interrumpida; cierre no confirmado por Asterisk.');
            } elseif ($evento !== 'latido') {
                $contesto = $llamada->fecha_contestacion !== null;
                $cambios['id_estado_llamada'] = $this->estado($contesto ? 'Finalizada' : 'No contestada');
                $cambios['fecha_fin'] = $ahora;
                $cambios['duracion'] = $contesto
                    ? max(0, (int) Carbon::parse($llamada->fecha_contestacion)->diffInSeconds($ahora))
                    : 0;
                $cambios['observaciones'] = $this->agregarNota($llamada->observaciones,
                    'Cierre reportado por SIP.js: ' . $evento . '; no corroborado por CDR/CEL.');
            }
            DB::table('llamadas')->where('id_llamada', $llamada->id_llamada)->update($cambios);
            return ['estado' => $evento, 'id_llamada' => $llamada->id_llamada];
        }, 3);

        return $this->sinCache($resultado);
    }

    private function operario(Request $request): object
    {
        $usuario = $request->user();
        abort_unless($usuario && (int) $usuario->id_rol === 3, 403);
        abort_unless((bool) $usuario->activo && $usuario->deleted_at === null, 403);
        return $usuario;
    }

    private function asegurarPropietario(object $llamada, object $usuario): void
    {
        abort_unless((int) $llamada->id_usuario === (int) $usuario->id_usuario
            && (int) $llamada->id_empresa === (int) $usuario->id_empresa, 403);
    }

    private function normalizarExterno(string $numero): string
    {
        $digitos = preg_replace('/\D+/', '', $numero) ?? '';
        if (strlen($digitos) < 10 || strlen($digitos) > 15) {
            throw ValidationException::withMessages(['destino' => 'Número mexicano inválido.']);
        }
        return substr($digitos, -10);
    }

    private function validarExtensionInterna(object $usuario, string $extension): void
    {
        $numero = (int) $extension;
        $permitida = ($numero >= 1001 && $numero <= 1030) || $numero === 1099;
        $inventario = $permitida && DB::table('extensiones_asterisk')
            ->where('id_empresa', $usuario->id_empresa)
            ->where('extension', $extension)->where('activa', true)->exists();
        $asignada = $permitida && DB::table('usuarios')
            ->where('id_empresa', $usuario->id_empresa)
            ->where('extension_asterisk', $extension)->where('id_rol', 3)
            ->where('activo', true)->whereNull('deleted_at')->exists();
        if (!$inventario || !$asignada) {
            throw ValidationException::withMessages(['destino' => 'Extensión interna no autorizada o no asignada.']);
        }
    }

    private function clienteAutorizado(object $usuario, string $telefono, bool $obligatorio): ?int
    {
        $cliente = AccesoClientes::consulta($usuario)
            ->where('id_empresa', (int) $usuario->id_empresa)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($telefono) {
                $q->whereRaw("RIGHT(REGEXP_REPLACE(COALESCE(telefono_principal, ''), '[^0-9]', '', 'g'), 10) = ?", [$telefono])
                  ->orWhereRaw("RIGHT(REGEXP_REPLACE(COALESCE(telefono_secundario, ''), '[^0-9]', '', 'g'), 10) = ?", [$telefono]);
            })->first(['id_cliente']);
        if (!$cliente && $obligatorio) {
            throw ValidationException::withMessages(['destino' => 'El número no corresponde a un cliente autorizado.']);
        }
        return $cliente ? (int) $cliente->id_cliente : null;
    }

    private function estado(string $nombre): int
    {
        $id = DB::table('estados_llamada')->whereRaw('LOWER(nombre) = LOWER(?)', [$nombre])->value('id_estado_llamada');
        if (!$id) {
            throw ValidationException::withMessages(['estado' => 'Falta el estado de llamada: ' . $nombre]);
        }
        return (int) $id;
    }

    private function agregarNota(?string $original, string $nota): string
    {
        $original = trim((string) $original);
        return $original === '' ? $nota : $original . "\n" . $nota;
    }

    private function sinCache(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status)
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
