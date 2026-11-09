<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AsteriskWebRtcProvisioningService
{
    public function esGestionada(string $extension): bool
    {
        return in_array($extension, config('webrtc_provisioning.managed_extensions', []), true);
    }

    public function estaListo(): bool
    {
        return (bool) config('webrtc_provisioning.enabled', false)
            && (string) config('webrtc_provisioning.url', '') !== ''
            && strlen((string) config('webrtc_provisioning.key', '')) >= 32;
    }

    /** Llamar DENTRO de una transaccion DB; nunca contacta Asterisk aqui. */
    public function prepararProvision(int $idUsuario, int $idEmpresa, string $extension): int
    {
        if (!$this->esGestionada($extension)) {
            throw new RuntimeException('La extension no esta habilitada para aprovisionamiento WebRTC.');
        }

        // La clave no se coloca en logs, Git ni peticiones de administracion.
        $secreto = bin2hex(random_bytes(24));
        $uuid = (string) Str::uuid();

        DB::table('webrtc_credenciales')->updateOrInsert(
            ['id_usuario' => $idUsuario],
            [
                'extension_asterisk' => $extension,
                'secret_cifrado' => Crypt::encryptString($secreto),
                'estado' => 'pendiente',
                'solicitud_uuid' => $uuid,
                'provisionada_en' => null,
                'ultimo_error' => null,
                'updated_at' => now(),
            ]
        );

        return (int) DB::table('webrtc_operaciones')->insertGetId([
            'solicitud_uuid' => $uuid,
            'id_empresa' => $idEmpresa,
            'id_usuario' => $idUsuario,
            'extension_asterisk' => $extension,
            'tipo' => 'provisionar',
            'estado' => 'pendiente',
            'intentos' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Bloquear provision anterior en navegador y solicitar revocacion remota. */
    public function prepararRevocacion(int $idUsuario, int $idEmpresa, string $extension): ?int
    {
        DB::table('webrtc_credenciales')
            ->where('id_usuario', $idUsuario)
            ->where('extension_asterisk', $extension)
            ->delete();

        if (!$this->esGestionada($extension)) {
            return null;
        }

        // Las provisiones antiguas pendientes/erroneas ya no deben bloquear
        // la revocacion ni aplicar claves despues de liberar el operario.
        DB::table('webrtc_operaciones')
            ->where('extension_asterisk', $extension)
            ->where('tipo', 'provisionar')
            ->whereIn('estado', ['pendiente', 'error'])
            ->update(['estado' => 'cancelada', 'updated_at' => now()]);

        return (int) DB::table('webrtc_operaciones')->insertGetId([
            'solicitud_uuid' => (string) Str::uuid(),
            'id_empresa' => $idEmpresa,
            'id_usuario' => $idUsuario,
            'extension_asterisk' => $extension,
            'tipo' => 'revocar',
            'estado' => 'pendiente',
            'intentos' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** No reutilizar una extension cuya revocacion aun no confirma Asterisk. */
    public function validarRevocacionPrevia(string $extension): void
    {
        $pendiente = DB::table('webrtc_operaciones')
            ->where('extension_asterisk', $extension)
            ->where('tipo', 'revocar')
            ->whereNotIn('estado', ['completada', 'cancelada'])
            ->exists();

        if ($pendiente) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'extension' => 'Hay una revocacion WebRTC pendiente o fallida. Reintenta la sincronizacion antes de reasignar.',
            ]);
        }
    }

    /**
     * Solo el helper de infraestructura modifica la autenticacion PJSIP.
     * Contrato: JSON, HMAC SHA256 sobre "timestamp.body", UUID idempotente.
     */
    public function enviarOperacion(string $tipo, string $extension, string $uuid, ?string $password): void
    {
        if (!$this->estaListo()) {
            throw new RuntimeException('El servicio de aprovisionamiento no esta configurado por Infraestructura.');
        }

        if (!$this->esGestionada($extension) || !preg_match('/^[0-9]{3,6}$/D', $extension)) {
            throw new RuntimeException('Extension fuera de lista permitida para WebRTC.');
        }

        $url = (string) config('webrtc_provisioning.url');
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException('URL de aprovisionamiento invalida.');
        }

        $payload = [
            'request_id' => $uuid,
            'operation' => $tipo,
            'extension' => $extension,
        ];
        if ($tipo === 'provisionar') {
            if (!$password) {
                throw new RuntimeException('No existe credencial cifrada para aprovisionar.');
            }
            $payload['username'] = $extension;
            $payload['password'] = $password;
        }

        $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $body, (string) config('webrtc_provisioning.key'));

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-CRM-Timestamp' => $timestamp,
                    'X-CRM-Signature' => 'sha256=' . $signature,
                    'X-CRM-Request-ID' => $uuid,
                ])
                ->withBody($body, 'application/json')
                ->connectTimeout(3)
                ->timeout(max(3, min(15, (int) config('webrtc_provisioning.timeout', 6))))
                ->post($url);
        } catch (\Throwable $e) {
            // No registrar excepciones HTTP detalladas ni payloads con contrasena.
            throw new RuntimeException('No se pudo conectar con el helper privado de Asterisk.');
        }

        $ack = $response->json();
        if (!$response->successful()
            || !is_array($ack)
            || ($ack['ok'] ?? false) !== true
            || ($ack['request_id'] ?? null) !== $uuid
            || ($ack['extension'] ?? null) !== $extension
            || ($ack['operation'] ?? null) !== $tipo
        ) {
            throw new RuntimeException('Asterisk no confirmo el aprovisionamiento (HTTP ' . $response->status() . ').');
        }
    }
}
