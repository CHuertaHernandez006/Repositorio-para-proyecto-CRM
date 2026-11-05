<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class TelefonoWebRtcController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $this->validarOperario($usuario);

        // Leer siempre de PostgreSQL: la extensión pudo cambiar durante la sesión.
        $extension = trim((string) DB::table('usuarios')
            ->where('id_usuario', $usuario->id_usuario)
            ->value('extension_asterisk'));

        return view('telefono.index', [
            'extensionWebRtc' => $extension,
            'wssWebRtc' => (string) config('webrtc.wss_url', ''),
            'dominioWebRtc' => (string) config('webrtc.sip_domain', ''),
            'habilitadoWebRtc' => (bool) config('webrtc.enabled', false),
        ]);
    }

    /**
     * La validación de salida evita que la interfaz del CRM llame a clientes
     * ajenos. Asterisk DEBE aplicar sus propias restricciones de marcado.
     */
    public function validarDestino(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $this->validarOperario($usuario);

        $datos = $request->validate([
            'telefono' => ['required', 'string', 'max:40'],
        ]);

        $telefono = $this->ultimosDiezDigitos($datos['telefono']);
        if (!$telefono) {
            return response()->json([
                'message' => 'Ingresa un número mexicano válido de al menos 10 dígitos.',
            ], 422);
        }

        $cliente = $this->clienteDeEmpresa((int) $usuario->id_empresa, $telefono);
        if (!$cliente) {
            return response()->json([
                'message' => 'El número no corresponde a un cliente registrado de tu empresa.',
            ], 404);
        }

        try {
            \App\Support\AccesoClientes::autorizarCliente($usuario, $cliente->id_cliente);
        } catch (HttpExceptionInterface $e) {
            return response()->json([
                'message' => 'Este cliente no está autorizado para tu operario.',
            ], 403);
        }

        return response()->json([
            'telefono_marcacion' => $telefono,
            'id_cliente' => $cliente->id_cliente,
            'cliente' => trim(implode(' ', array_filter([
                $cliente->nombre ?? '',
                $cliente->apellido_paterno ?? '',
                $cliente->apellido_materno ?? '',
            ]))),
        ]);
    }

    /** Consulta informativa: no revela datos de clientes ajenos al operario. */
    public function buscarEntrante(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $this->validarOperario($usuario);

        $telefono = $this->ultimosDiezDigitos((string) $request->query('telefono', ''));
        if (!$telefono) {
            return response()->json(['encontrado' => false]);
        }

        $cliente = $this->clienteDeEmpresa((int) $usuario->id_empresa, $telefono);
        if (!$cliente) {
            return response()->json(['encontrado' => false]);
        }

        try {
            \App\Support\AccesoClientes::autorizarCliente($usuario, $cliente->id_cliente);
        } catch (HttpExceptionInterface $e) {
            return response()->json(['encontrado' => false]);
        }

        return response()->json([
            'encontrado' => true,
            'cliente' => trim(implode(' ', array_filter([
                $cliente->nombre ?? '',
                $cliente->apellido_paterno ?? '',
                $cliente->apellido_materno ?? '',
            ]))),
        ]);
    }

    private function validarOperario($usuario): void
    {
        abort_unless($usuario && (int) $usuario->id_rol === 3, 403);
        abort_if(!$usuario->activo, 403, 'Tu cuenta no está activa.');
    }

    private function ultimosDiezDigitos(string $valor): ?string
    {
        $digitos = preg_replace('/\D+/', '', $valor) ?? '';
        return strlen($digitos) >= 10 && strlen($digitos) <= 15
            ? substr($digitos, -10)
            : null;
    }

    private function clienteDeEmpresa(int $idEmpresa, string $telefono): ?object
    {
        return DB::table('clientes')
            ->where('id_empresa', $idEmpresa)
            ->whereNull('deleted_at')
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
}
