<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AccesoClientes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\Security\RequestValidator;
use Twilio\TwiML\VoiceResponse;

/**
 * Piloto AISLADO para salientes Twilio Voice SDK 2.x.
 * SIP.js, rutas internas Asterisk y configuración del DID entrante no se modifican.
 */
class TwilioVoicePilotoController extends Controller
{
    public function index(Request $request): View
    {
        $this->operario($request);
        return view('telefono.twilio-prueba', [
            'twilioPilotoActivo' => (bool) config('twilio_voice.pilot_enabled', false),
        ]);
    }

    public function token(Request $request): JsonResponse
    {
        $usuario = $this->operario($request);
        $this->configurado();

        $grant = new VoiceGrant();
        $grant->setOutgoingApplicationSid((string) config('twilio_voice.twiml_app_sid'));
        $grant->setIncomingAllow(false); // Las entrantes siguen en Asterisk durante el piloto.

        $jwt = new AccessToken(
            (string) config('twilio_voice.account_sid'),
            (string) config('twilio_voice.api_key_sid'),
            (string) config('twilio_voice.api_key_secret'),
            600,
            'comi-u' . (int) $usuario->id_usuario
        );
        $jwt->addGrant($grant);

        return $this->noCache([
            'token' => $jwt->toJWT(),
            'vence_en_segundos' => 600,
        ]);
    }

    public function preparar(Request $request): JsonResponse
    {
        $usuario = $this->operario($request);
        $this->configurado();
        $datos = $request->validate([
            'telefono' => ['required', 'string', 'max:30'],
        ]);

        // Sólo numeración nacional de 10 dígitos o +52 seguido de 10 dígitos.
        $digitos = preg_replace('/\D+/', '', $datos['telefono']) ?: '';
        if (!preg_match('/^(?:52)?(\d{10})$/', $digitos, $m)) {
            return $this->noCache(['message' => 'Usa un teléfono mexicano de 10 dígitos o +52 y 10 dígitos.'], 422);
        }
        $nacional = $m[1];
        $e164 = '+52' . $nacional;

        // Para evitar llamadas arbitrarias/costosas: lista explícita de números de prueba.
        $permitidos = array_values(array_filter(array_map('trim', explode(',', (string) config('twilio_voice.pilot_allowlist', '')))));
        if (!in_array($e164, $permitidos, true)) {
            return $this->noCache(['message' => 'Este número no está habilitado para el piloto Twilio.'], 403);
        }

        // Mismo alcance de "Mis clientes"; ningún número elegido por JS se confía directamente.
        $cliente = AccesoClientes::consulta($usuario)
            ->where('id_empresa', (int) $usuario->id_empresa)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($nacional) {
                foreach (['telefono_principal', 'telefono_secundario'] as $campo) {
                    $q->orWhereRaw(
                        "RIGHT(REGEXP_REPLACE(COALESCE({$campo}, ''), '[^0-9]', '', 'g'), 10) = ?",
                        [$nacional]
                    );
                }
            })
            ->first(['id_cliente', 'nombre', 'apellido_paterno', 'apellido_materno']);

        if (!$cliente) {
            return $this->noCache(['message' => 'El destino no pertenece a tus clientes autorizados.'], 403);
        }

        $id = (string) Str::uuid();
        DB::table('twilio_intentos')->insert([
            'id' => $id,
            'id_usuario' => (int) $usuario->id_usuario,
            'id_empresa' => (int) $usuario->id_empresa,
            'id_cliente' => (int) $cliente->id_cliente,
            'destino_e164' => $e164,
            'estado' => 'preparado',
            'expira_en' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nombre = trim(implode(' ', array_filter([
            $cliente->nombre, $cliente->apellido_paterno, $cliente->apellido_materno,
        ])));
        return $this->noCache(['intento' => $id, 'destino' => $e164, 'cliente' => $nombre]);
    }

    public function consultar(Request $request, string $id): JsonResponse
    {
        $usuario = $this->operario($request);
        $this->configurado();

        $intento = DB::table('twilio_intentos')
            ->where('id', $id)
            ->where('id_usuario', (int) $usuario->id_usuario)
            ->where('id_empresa', (int) $usuario->id_empresa)
            ->first(['estado', 'fecha_contestacion', 'fecha_fin', 'id_llamada']);

        abort_unless($intento, 404);
        return $this->noCache([
            'estado' => $intento->estado,
            'contestada' => $intento->fecha_contestacion !== null,
            'finalizada' => $intento->fecha_fin !== null,
            'id_llamada' => $intento->id_llamada,
        ]);
    }

    /** Twilio consulta esta Voice URL de la TwiML App al originar desde el navegador. */
    public function voice(Request $request)
    {
        $this->verificarWebhook($request);
        $this->configurado();
        $intentoId = (string) $request->input('Intento', '');
        $from = (string) $request->input('From', '');
        $sid = (string) $request->input('CallSid', '');
        if (!Str::isUuid($intentoId) || !preg_match('/^client:comi-u(\d+)$/', $from, $m)
            || !preg_match('/^CA[a-f0-9]{32}$/i', $sid)) {
            return $this->twimlDenegado();
        }

        $usuarioId = (int) $m[1];
        $resultado = DB::transaction(function () use ($intentoId, $usuarioId, $sid) {
            $intento = DB::table('twilio_intentos')->where('id', $intentoId)->lockForUpdate()->first();
            if (!$intento || (int) $intento->id_usuario !== $usuarioId
                || ($intento->estado === 'preparado' && now()->greaterThan($intento->expira_en))
                || ($intento->twilio_parent_sid !== null && $intento->twilio_parent_sid !== $sid)
                || ($intento->twilio_parent_sid === null && $intento->estado !== 'preparado')) {
                return null;
            }
            $operario = User::query()
                ->where('id_usuario', $usuarioId)
                ->where('id_empresa', (int) $intento->id_empresa)
                ->where('id_rol', 3)
                ->where('activo', true)
                ->whereNull('deleted_at')
                ->first();
            if (!$operario) return null;

            // Comprobar de nuevo exactamente el alcance de "Mis clientes":
            // una asignación revocada entre preparar y marcar NO debe poder llamar.
            $autorizado = AccesoClientes::consulta($operario)
                ->where('id_empresa', (int) $intento->id_empresa)
                ->whereNull('deleted_at')
                ->where('id_cliente', (int) $intento->id_cliente)
                ->exists();
            if (!$autorizado) return null;

            if ($intento->id_llamada === null) {
                $enCurso = DB::table('estados_llamada')->where('nombre', 'En curso')->value('id_estado_llamada');
                if (!$enCurso) return null;
                $llamadaId = DB::table('llamadas')->insertGetId([
                    'id_empresa' => (int) $intento->id_empresa,
                    'id_cliente' => (int) $intento->id_cliente,
                    'id_usuario' => $usuarioId,
                    'id_estado_llamada' => (int) $enCurso,
                    'tipo_llamada' => 'saliente',
                    'fecha_inicio' => now(),
                    'numero_origen' => (string) config('twilio_voice.caller_id'),
                    'numero_destino' => $intento->destino_e164,
                    'identificador_asterisk' => null,
                    'observaciones' => 'Piloto saliente Twilio Voice SDK. Parent SID: ' . $sid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'id_llamada');
                DB::table('twilio_intentos')->where('id', $intentoId)->update([
                    'id_llamada' => $llamadaId,
                    'twilio_parent_sid' => $sid,
                    'estado' => 'marcando',
                    'updated_at' => now(),
                ]);
            }
            return $intento;
        });

        if (!$resultado) return $this->twimlDenegado();
        $xml = new VoiceResponse();
        $base = $this->webhookBase();
        $dial = $xml->dial(null, [
            'callerId' => (string) config('twilio_voice.caller_id'),
            'answerOnBridge' => 'true',
            'timeout' => 30,
            'action' => $base . '/telefono/twilio/webhook/fin?intento=' . $intentoId,
            'method' => 'POST',
        ]);
        $dial->number($resultado->destino_e164, [
            'statusCallback' => $base . '/telefono/twilio/webhook/estado?intento=' . $intentoId,
            'statusCallbackMethod' => 'POST',
            'statusCallbackEvent' => 'initiated ringing answered completed',
        ]);
        return $this->xml($xml);
    }

    /** Callbacks de <Dial><Number>, enviados y firmados por Twilio. */
    public function estado(Request $request)
    {
        $this->verificarWebhook($request);
        $id = (string) $request->query('intento', '');
        if (!Str::isUuid($id)) abort(422);
        $evento = (string) $request->input('StatusCallbackEvent', '');
        $estado = (string) $request->input('CallStatus', '');
        $childSid = (string) $request->input('CallSid', '');
        if ($childSid !== '' && !preg_match('/^CA[a-f0-9]{32}$/i', $childSid)) abort(422);

        DB::transaction(function () use ($id, $evento, $estado, $childSid, $request) {
            $intento = DB::table('twilio_intentos')->where('id', $id)->lockForUpdate()->first();
            if (!$intento || $intento->twilio_parent_sid === null) return;
            if ($intento->twilio_child_sid && $childSid && $intento->twilio_child_sid !== $childSid) return;
            $cambios = ['updated_at' => now()];
            if ($childSid && !$intento->twilio_child_sid) $cambios['twilio_child_sid'] = $childSid;
            if ($intento->fecha_fin !== null) {
                DB::table('twilio_intentos')->where('id', $id)->update($cambios);
                return;
            }
            if ($evento === 'answered' || $estado === 'in-progress') {
                $cambios['estado'] = 'contestada';
                $cambios['fecha_contestacion'] = $intento->fecha_contestacion ?: now();
            } elseif ($evento === 'ringing' && !$intento->fecha_contestacion) {
                $cambios['estado'] = 'timbrando';
            }
            if ($evento === 'completed' || in_array($estado, ['completed', 'busy', 'no-answer', 'failed', 'canceled'], true)) {
                $cambios['estado'] = $this->estadoFinal($estado);
                $cambios['fecha_fin'] = now();
                $this->cerrarLlamadaCRM($intento, $cambios['estado'], $request->input('CallDuration'));
            }
            DB::table('twilio_intentos')->where('id', $id)->update($cambios);
        });
        return response('', 204);
    }

    /** Twilio ejecuta esta action al terminar <Dial>, incluso si no hubo respuesta. */
    public function fin(Request $request)
    {
        $this->verificarWebhook($request);
        $id = (string) $request->query('intento', '');
        if (!Str::isUuid($id)) abort(422);
        DB::transaction(function () use ($id, $request) {
            $intento = DB::table('twilio_intentos')->where('id', $id)->lockForUpdate()->first();
            if (!$intento || $intento->twilio_parent_sid === null || $intento->fecha_fin !== null) return;
            $estado = (string) $request->input('DialCallStatus', 'failed');
            $final = $this->estadoFinal($estado);
            $cambios = ['estado' => $final, 'fecha_fin' => now(), 'updated_at' => now()];
            $this->cerrarLlamadaCRM($intento, $final, $request->input('DialCallDuration'));
            DB::table('twilio_intentos')->where('id', $id)->update($cambios);
        });
        $xml = new VoiceResponse();
        $xml->hangup();
        return $this->xml($xml);
    }

    private function cerrarLlamadaCRM(object $intento, string $estado, mixed $segundos): void
    {
        if (!$intento->id_llamada) return;
        $finalizadaId = DB::table('estados_llamada')->where('nombre', 'Finalizada')->value('id_estado_llamada');
        $duracion = is_scalar($segundos) && preg_match('/^\d{1,7}$/', (string) $segundos)
            ? (int) $segundos : null;
        $cambios = [
            'fecha_fin' => now(), 'updated_at' => now(),
            'observaciones' => 'Piloto Twilio Voice SDK. Resultado Twilio: ' . $estado
                . '. Parent SID: ' . $intento->twilio_parent_sid,
        ];
        if ($finalizadaId) $cambios['id_estado_llamada'] = (int) $finalizadaId;
        if ($duracion !== null) $cambios['duracion'] = $duracion;
        // Los webhooks prevalecen sobre la información provisional del navegador.
        DB::table('llamadas')->where('id_llamada', $intento->id_llamada)->whereNull('fecha_fin')->update($cambios);
    }

    private function estadoFinal(string $estado): string
    {
        return match ($estado) {
            'completed' => 'finalizada',
            'busy' => 'ocupado',
            'no-answer' => 'sin_respuesta',
            'canceled' => 'cancelada',
            default => 'fallida',
        };
    }

    private function verificarWebhook(Request $request): void
    {
        $auth = (string) config('twilio_voice.auth_token', '');
        $cuenta = (string) config('twilio_voice.account_sid', '');
        $firma = (string) $request->header('X-Twilio-Signature', '');
        abort_unless((bool) config('twilio_voice.pilot_enabled', false) && $auth !== ''
            && $cuenta !== '' && $firma !== ''
            && hash_equals($cuenta, (string) $request->input('AccountSid', '')), 403);

        $url = $this->webhookBase() . $request->getRequestUri();
        $validador = new RequestValidator($auth);
        abort_unless($validador->validate($firma, $url, $request->post()), 403);
    }

    private function webhookBase(): string
    {
        $base = rtrim((string) config('twilio_voice.webhook_base_url', ''), '/');
        abort_unless((bool) preg_match('#^https://[a-z0-9.-]+(?::\d+)?$#i', $base), 503,
            'Configura TWILIO_WEBHOOK_BASE_URL con la URL pública HTTPS exacta.');
        return $base;
    }

    private function configurado(): void
    {
        abort_unless((bool) config('twilio_voice.pilot_enabled', false), 503, 'El piloto Twilio está deshabilitado.');
        foreach (['account_sid', 'api_key_sid', 'api_key_secret', 'auth_token', 'twiml_app_sid', 'caller_id', 'webhook_base_url', 'pilot_allowlist'] as $campo) {
            abort_if(trim((string) config('twilio_voice.' . $campo, '')) === '', 503,
                'Falta configuración del piloto Twilio: ' . $campo);
        }
        abort_unless((bool) preg_match('/^AC[a-f0-9]{32}$/i', (string) config('twilio_voice.account_sid'))
            && (bool) preg_match('/^SK[a-f0-9]{32}$/i', (string) config('twilio_voice.api_key_sid'))
            && (bool) preg_match('/^AP[a-f0-9]{32}$/i', (string) config('twilio_voice.twiml_app_sid'))
            && (bool) preg_match('/^\+52\d{10}$/', (string) config('twilio_voice.caller_id')), 503,
            'Verifica los SID y el Caller ID de Twilio.');
        $this->webhookBase();
        abort_unless(class_exists(AccessToken::class) && class_exists(RequestValidator::class), 503,
            'Falta composer require twilio/sdk.');
        abort_unless(Schema::hasTable('twilio_intentos'), 503, 'Falta crear la tabla twilio_intentos.');
    }

    private function operario(Request $request): object
    {
        $usuario = $request->user();
        abort_unless($usuario && (int) $usuario->id_rol === 3
            && (bool) $usuario->activo && (int) $usuario->id_empresa > 0, 403);
        return $usuario;
    }

    private function twimlDenegado()
    {
        $xml = new VoiceResponse();
        $xml->say('Llamada no autorizada.');
        $xml->hangup();
        return $this->xml($xml);
    }

    private function xml(VoiceResponse $response)
    {
        return response((string) $response, 200)->header('Content-Type', 'text/xml; charset=utf-8')
            ->header('Cache-Control', 'no-store');
    }

    private function noCache(array $datos, int $codigo = 200): JsonResponse
    {
        return response()->json($datos, $codigo)->header('Cache-Control', 'private, no-store');
    }
}
