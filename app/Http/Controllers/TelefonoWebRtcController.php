<?php

namespace App\Http\Controllers;

use App\Support\AccesoClientes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TelefonoWebRtcController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $this->validarOperario($usuario);

        // La asignacion SIP pudo cambiar durante la sesion web.
        $extension = $this->extensionActual((int) $usuario->id_usuario);

        return view('telefono.index', [
            'extensionWebRtc' => $extension,
            'wssWebRtc' => (string) config('webrtc.wss_url', ''),
            'dominioWebRtc' => (string) config('webrtc.sip_domain', ''),
            'habilitadoWebRtc' => (bool) config('webrtc.enabled', false),
        ]);
    }

    /**
     * Entrega la credencial SIP SOLO al operario autenticado y SOLO para su
     * extension actual. El secreto se cifra al almacenarse, no se inserta
     * en Blade y nunca se escribe en localStorage.
     *
     * Nota: SIP.js necesariamente usa la clave en memoria del navegador.
     * Redes debe implementar la rotacion y las restricciones del dialplan.
     */
    public function credenciales(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $this->validarOperario($usuario);

        if (!(bool) config('webrtc.enabled', false)) {
            return $this->sinCache(['message' => 'WebRTC no esta habilitado.'], 503);
        }

        $extension = $this->extensionActual((int) $usuario->id_usuario);
        if ($extension === '') {
            return $this->sinCache(['message' => 'Tu cuenta no tiene extension SIP asignada.'], 409);
        }

        $credencial = DB::table('webrtc_credenciales')
            ->where('id_usuario', $usuario->id_usuario)
            ->where('extension_asterisk', $extension)
            ->first(['secret_cifrado']);

        if (!$credencial) {
            return $this->sinCache([
                'message' => 'Redes debe provisionar la extension SIP ' . $extension . ' para el telefono web.',
            ], 409);
        }

        try {
            $password = Crypt::decryptString((string) $credencial->secret_cifrado);
        } catch (DecryptException $e) {
            report($e); // El error no incluye el contenido de la credencial.
            return $this->sinCache(['message' => 'No se pudo leer la credencial SIP. Contacta a redes.'], 503);
        }

        return $this->sinCache([
            'extension' => $extension,
            'password' => $password,
        ]);
    }

    /** Listado paginado basado en EXACTAMENTE el alcance de Mis clientes. */
    public function clientesDisponibles(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $this->validarOperario($usuario);

        $datos = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        $buscar = trim((string) ($datos['buscar'] ?? ''));

        $consulta = $this->consultaClientesAutorizados($usuario);
        if ($buscar !== '') {
            $texto = '%' . $this->escaparLike($buscar) . '%';
            $digitos = preg_replace('/\D+/', '', $buscar) ?? '';
            $consulta->where(function ($q) use ($texto, $digitos) {
                $q->whereRaw(
                    "CONCAT_WS(' ', COALESCE(nombre, ''), COALESCE(apellido_paterno, ''), COALESCE(apellido_materno, '')) ILIKE ?",
                    [$texto]
                )->orWhereRaw("COALESCE(organizacion, '') ILIKE ?", [$texto]);

                if ($digitos !== '') {
                    $numero = '%' . $digitos . '%';
                    $q->orWhereRaw(
                        "REGEXP_REPLACE(COALESCE(telefono_principal, ''), '[^0-9]', '', 'g') LIKE ?",
                        [$numero]
                    )->orWhereRaw(
                        "REGEXP_REPLACE(COALESCE(telefono_secundario, ''), '[^0-9]', '', 'g') LIKE ?",
                        [$numero]
                    );
                }
            });
        }

        $pagina = $consulta
            ->orderBy('id_cliente')
            ->paginate(12, ['id_cliente', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono_principal', 'telefono_secundario']);

        $items = $pagina->getCollection()->map(function ($cliente) {
            return [
                'id_cliente' => (int) $cliente->id_cliente,
                'nombre' => $this->nombreCliente($cliente),
                'telefono_principal' => (string) ($cliente->telefono_principal ?? ''),
                'telefono_secundario' => (string) ($cliente->telefono_secundario ?? ''),
            ];
        })->values();

        return response()->json([
            'clientes' => $items,
            'total' => $pagina->total(),
            'pagina' => $pagina->currentPage(),
            'ultima_pagina' => $pagina->lastPage(),
        ])->header('Cache-Control', 'private, no-store');
    }

    /** La interfaz SOLO puede llamar a clientes del conjunto autorizado. */
    public function validarDestino(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $this->validarOperario($usuario);

        $datos = $request->validate(['telefono' => ['required', 'string', 'max:40']]);
        $telefono = $this->ultimosDiezDigitos($datos['telefono']);
        if (!$telefono) {
            return response()->json([
                'message' => 'Ingresa un numero mexicano valido de al menos 10 digitos.',
            ], 422);
        }

        $cliente = $this->clienteAutorizadoPorTelefono($usuario, $telefono);
        if (!$cliente) {
            return response()->json([
                'message' => 'El numero no corresponde a un cliente autorizado para tu operario.',
            ], 404);
        }

        return response()->json([
            'telefono_marcacion' => $telefono,
            'id_cliente' => $cliente->id_cliente,
            'cliente' => $this->nombreCliente($cliente),
        ]);
    }

    /** Identificacion informativa de entrantes, sin exponer otras empresas. */
    public function buscarEntrante(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $this->validarOperario($usuario);
        $telefono = $this->ultimosDiezDigitos((string) $request->query('telefono', ''));
        if (!$telefono) {
            return response()->json(['encontrado' => false]);
        }

        $cliente = $this->clienteAutorizadoPorTelefono($usuario, $telefono);
        return response()->json($cliente ? [
            'encontrado' => true,
            'cliente' => $this->nombreCliente($cliente),
        ] : ['encontrado' => false]);
    }

    private function consultaClientesAutorizados($usuario)
    {
        return AccesoClientes::consulta($usuario)
            ->where('id_empresa', (int) $usuario->id_empresa)
            ->whereNull('deleted_at');
    }

    private function clienteAutorizadoPorTelefono($usuario, string $telefono): ?object
    {
        return $this->consultaClientesAutorizados($usuario)
            ->where(function ($query) use ($telefono) {
                $query->whereRaw(
                    "RIGHT(REGEXP_REPLACE(COALESCE(telefono_principal, ''), '[^0-9]', '', 'g'), 10) = ?",
                    [$telefono]
                )->orWhereRaw(
                    "RIGHT(REGEXP_REPLACE(COALESCE(telefono_secundario, ''), '[^0-9]', '', 'g'), 10) = ?",
                    [$telefono]
                );
            })
            ->first();
    }

    private function extensionActual(int $idUsuario): string
    {
        return trim((string) DB::table('usuarios')
            ->where('id_usuario', $idUsuario)
            ->value('extension_asterisk'));
    }

    private function validarOperario($usuario): void
    {
        abort_unless($usuario && (int) $usuario->id_rol === 3, 403);
        abort_if(!$usuario->activo, 403, 'Tu cuenta no esta activa.');
    }

    private function ultimosDiezDigitos(string $valor): ?string
    {
        $digitos = preg_replace('/\D+/', '', $valor) ?? '';
        return strlen($digitos) >= 10 && strlen($digitos) <= 15
            ? substr($digitos, -10)
            : null;
    }

    private function nombreCliente(object $cliente): string
    {
        $nombre = trim(implode(' ', array_filter([
            $cliente->nombre ?? '',
            $cliente->apellido_paterno ?? '',
            $cliente->apellido_materno ?? '',
        ])));
        return $nombre !== '' ? $nombre : 'Cliente #' . $cliente->id_cliente;
    }

    private function escaparLike(string $valor): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $valor);
    }

    private function sinCache(array $datos, int $estado = 200): JsonResponse
    {
        return response()->json($datos, $estado)
            ->header('Cache-Control', 'private, no-store, no-cache, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
