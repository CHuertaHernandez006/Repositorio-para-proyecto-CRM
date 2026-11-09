<?php

namespace App\Jobs;

use App\Services\AsteriskWebRtcProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Una operación se reserva mediante una transacción BREVE; la llamada HTTP
 * ocurre fuera de cualquier transacción PostgreSQL.
 *
 * El helper debe ser idempotente por solicitud_uuid porque un timeout puede
 * suceder después de que Asterisk haya aplicado el cambio.
 */
class SincronizarSipWebRtc implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;
    public int $tries = 1;

    public function __construct(public int $operacionId)
    {
        $this->onQueue('webrtc-provisioning');
    }

    public function handle(AsteriskWebRtcProvisioningService $servicio): void
    {
        $reserva = $this->reservar();
        if ($reserva === null) {
            return;
        }

        $error = null;
        try {
            $password = null;
            if ($reserva['tipo'] === 'provisionar') {
                // No incluir nunca el secreto en logs, excepciones ni payload de Jobs.
                $cifrado = DB::table('webrtc_credenciales')
                    ->where('id_usuario', $reserva['id_usuario'])
                    ->where('extension_asterisk', $reserva['extension'])
                    ->where('solicitud_uuid', $reserva['uuid'])
                    ->value('secret_cifrado');
                if (!is_string($cifrado) || $cifrado === '') {
                    throw new RuntimeException('Credencial no disponible para la operación.');
                }
                $password = Crypt::decryptString($cifrado);
            }

            // CRÍTICO: sin transacción ni locks de PostgreSQL durante HTTP.
            $servicio->enviarOperacion(
                $reserva['tipo'], $reserva['extension'], $reserva['uuid'], $password
            );
            unset($password);
        } catch (Throwable $e) {
            // Las excepciones del helper no deben incluir cuerpos JSON ni secretos.
            $error = $e instanceof RuntimeException
                ? mb_substr($e->getMessage(), 0, 240)
                : 'Fallo interno de sincronización; revisar el servicio auxiliar.';
        }

        $siguiente = $this->finalizar($reserva, $error);
        if ($siguiente !== null) {
            self::dispatch($siguiente);
        }
    }

    private function reservar(): ?array
    {
        return DB::transaction(function () {
            $original = DB::table('webrtc_operaciones')
                ->where('id', $this->operacionId)->first();
            if (!$original || $original->estado !== 'pendiente') {
                return null;
            }

            // Serializa las reservas por extensión; se suelta ANTES del HTTP.
            DB::table('extensiones_asterisk')
                ->where('extension', $original->extension_asterisk)
                ->lockForUpdate()->first();

            $op = DB::table('webrtc_operaciones')
                ->where('id', $this->operacionId)
                ->lockForUpdate()->first();
            if (!$op || $op->estado !== 'pendiente') {
                return null;
            }

            // Una operación previa pendiente, ejecutándose o fallida bloquea
            // las posteriores para no pisar una revocación/rotación.
            $bloqueada = DB::table('webrtc_operaciones')
                ->where('extension_asterisk', $op->extension_asterisk)
                ->where('id', '<', $op->id)
                ->whereIn('estado', ['pendiente', 'procesando', 'error'])
                ->exists();
            if ($bloqueada) {
                return null;
            }

            if ($op->tipo === 'provisionar') {
                $credencial = DB::table('webrtc_credenciales')
                    ->where('id_usuario', $op->id_usuario)
                    ->where('extension_asterisk', $op->extension_asterisk)
                    ->where('solicitud_uuid', $op->solicitud_uuid)
                    ->first();
                $usuario = DB::table('usuarios')
                    ->where('id_usuario', $op->id_usuario)
                    ->first(['extension_asterisk', 'id_empresa', 'activo', 'deleted_at']);
                if (!$credencial || !$usuario
                    || (string) $usuario->extension_asterisk !== (string) $op->extension_asterisk
                    || (int) $usuario->id_empresa !== (int) $op->id_empresa
                    || !$usuario->activo || $usuario->deleted_at !== null
                ) {
                    DB::table('webrtc_operaciones')->where('id', $op->id)
                        ->update(['estado' => 'cancelada', 'updated_at' => now()]);
                    return null;
                }
            }

            if (!in_array($op->tipo, ['provisionar', 'revocar'], true)) {
                DB::table('webrtc_operaciones')->where('id', $op->id)
                    ->update(['estado' => 'error', 'ultimo_error' => 'Operación no admitida.', 'updated_at' => now()]);
                return null;
            }

            $token = (string) Str::uuid();
            DB::table('webrtc_operaciones')->where('id', $op->id)->update([
                'estado' => 'procesando',
                'lease_token' => $token,
                'iniciada_en' => now(),
                'intentos' => $op->intentos + 1,
                'ultimo_error' => null,
                'updated_at' => now(),
            ]);

            return [
                'id' => (int) $op->id,
                'id_usuario' => (int) $op->id_usuario,
                'extension' => (string) $op->extension_asterisk,
                'tipo' => (string) $op->tipo,
                'uuid' => (string) $op->solicitud_uuid,
                'token' => $token,
            ];
        }, 3);
    }

    private function finalizar(array $reserva, ?string $error): ?int
    {
        return DB::transaction(function () use ($reserva, $error) {
            DB::table('extensiones_asterisk')
                ->where('extension', $reserva['extension'])
                ->lockForUpdate()->first();

            $op = DB::table('webrtc_operaciones')
                ->where('id', $reserva['id'])->lockForUpdate()->first();
            if (!$op || $op->estado !== 'procesando'
                || $op->lease_token !== $reserva['token']) {
                return null; // Otro proceso recuperó este lease; no sobrescribirlo.
            }

            DB::table('webrtc_operaciones')->where('id', $op->id)->update([
                'estado' => $error === null ? 'completada' : 'error',
                'ultimo_error' => $error,
                'procesada_en' => $error === null ? now() : null,
                'lease_token' => null,
                'iniciada_en' => null,
                'updated_at' => now(),
            ]);

            if ($op->tipo === 'provisionar') {
                DB::table('webrtc_credenciales')
                    ->where('id_usuario', $op->id_usuario)
                    ->where('extension_asterisk', $op->extension_asterisk)
                    ->where('solicitud_uuid', $op->solicitud_uuid)
                    ->update([
                        'estado' => $error === null ? 'provisionada' : 'error',
                        'provisionada_en' => $error === null ? now() : null,
                        'ultimo_error' => $error,
                        'updated_at' => now(),
                    ]);
            }

            if ($error !== null) {
                return null; // Reintento explícito, para evitar bucles en infraestructura.
            }

            $siguiente = DB::table('webrtc_operaciones')
                ->where('extension_asterisk', $reserva['extension'])
                ->where('id', '>', $reserva['id'])
                ->where('estado', 'pendiente')
                ->orderBy('id')->value('id');
            return $siguiente === null ? null : (int) $siguiente;
        }, 3);
    }
}
