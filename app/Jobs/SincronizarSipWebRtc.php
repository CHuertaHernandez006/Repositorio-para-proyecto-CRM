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

class SincronizarSipWebRtc implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;
    public int $tries = 1; // El reintento es explicito y auditable desde administracion.

    public function __construct(public int $operacionId)
    {
        $this->onQueue('webrtc-provisioning');
    }

    public function handle(AsteriskWebRtcProvisioningService $servicio): void
    {
        // El lock DB serializa operaciones sobre una misma extension. No enviar
        // peticiones remotas dentro de la transaccion de ASIGNACION del usuario.
        DB::transaction(function () use ($servicio) {
            $original = DB::table('webrtc_operaciones')->where('id', $this->operacionId)->first();
            if (!$original || in_array($original->estado, ['completada', 'cancelada'], true)) {
                return;
            }

            DB::table('extensiones_asterisk')
                ->where('extension', $original->extension_asterisk)
                ->lockForUpdate()
                ->first();

            $op = DB::table('webrtc_operaciones')->where('id', $this->operacionId)
                ->lockForUpdate()->first();
            if (!$op || $op->estado !== 'pendiente') {
                return;
            }

            $anterior = DB::table('webrtc_operaciones')
                ->where('extension_asterisk', $op->extension_asterisk)
                ->where('id', '<', $op->id)
                ->whereNotIn('estado', ['completada', 'cancelada'])
                ->exists();
            if ($anterior) {
                return; // El siguiente intento se despacha al completar el anterior.
            }

            $password = null;
            if ($op->tipo === 'provisionar') {
                $credencial = DB::table('webrtc_credenciales')
                    ->where('id_usuario', $op->id_usuario)
                    ->where('extension_asterisk', $op->extension_asterisk)
                    ->first();
                $asignacion = DB::table('usuarios')
                    ->where('id_usuario', $op->id_usuario)
                    ->value('extension_asterisk');
                if (!$credencial
                    || $credencial->solicitud_uuid !== $op->solicitud_uuid
                    || $asignacion !== $op->extension_asterisk
                ) {
                    DB::table('webrtc_operaciones')->where('id', $op->id)->update([
                        'estado' => 'cancelada', 'updated_at' => now(),
                    ]);
                    return;
                }
                $password = Crypt::decryptString($credencial->secret_cifrado);
            }

            try {
                $servicio->enviarOperacion(
                    $op->tipo, $op->extension_asterisk, $op->solicitud_uuid, $password
                );
                DB::table('webrtc_operaciones')->where('id', $op->id)->update([
                    'estado' => 'completada',
                    'intentos' => $op->intentos + 1,
                    'ultimo_error' => null,
                    'procesada_en' => now(),
                    'updated_at' => now(),
                ]);
                if ($op->tipo === 'provisionar') {
                    DB::table('webrtc_credenciales')
                        ->where('id_usuario', $op->id_usuario)
                        ->where('solicitud_uuid', $op->solicitud_uuid)
                        ->update([
                            'estado' => 'provisionada',
                            'provisionada_en' => now(),
                            'ultimo_error' => null,
                            'updated_at' => now(),
                        ]);
                }
                // Si otra operacion espero a esta para la misma extension,
                // despertarla al confirmar, sin exigir accion manual.
                $siguiente = DB::table('webrtc_operaciones')
                    ->where('extension_asterisk', $op->extension_asterisk)
                    ->where('id', '>', $op->id)
                    ->where('estado', 'pendiente')
                    ->orderBy('id')->value('id');
                if ($siguiente) {
                    DB::afterCommit(function () use ($siguiente) {
                        self::dispatch((int) $siguiente);
                    });
                }
            } catch (\Throwable $e) {
                // Nunca registrar contrasenas ni respuestas completas del helper.
                $mensaje = mb_substr($e->getMessage(), 0, 250);
                DB::table('webrtc_operaciones')->where('id', $op->id)->update([
                    'estado' => 'error',
                    'intentos' => $op->intentos + 1,
                    'ultimo_error' => $mensaje,
                    'updated_at' => now(),
                ]);
                if ($op->tipo === 'provisionar') {
                    DB::table('webrtc_credenciales')
                        ->where('solicitud_uuid', $op->solicitud_uuid)
                        ->update(['estado' => 'error', 'ultimo_error' => $mensaje, 'updated_at' => now()]);
                }
            }
        }, 3);
    }
}
