<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Llamada;
use App\Services\AsteriskAmiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class LlamadaSalienteController extends Controller
{
    public function store(
        Request $request,
        Cliente $cliente,
        AsteriskAmiService $asterisk
    ): RedirectResponse {
        $usuario = $request->user();

        if (!$usuario || (int) $usuario->id_rol !== 3) {
            abort(403, 'Solo los operarios pueden iniciar llamadas.');
        }

        if (!$usuario->activo) {
            abort(403, 'Tu cuenta no está activa.');
        }

        if ((int) $cliente->id_empresa !== (int) $usuario->id_empresa) {
            abort(403, 'El cliente no pertenece a tu empresa.');
        }

        if ($cliente->deleted_at !== null) {
            abort(404);
        }

        $extension = trim((string) ($usuario->extension_asterisk ?? ''));

        if ($extension === '') {
            return back()->with(
                'error',
                'Tu usuario todavía no tiene una extensión de Asterisk asignada.'
            );
        }

        $telefono = $this->normalizarTelefono(
            $cliente->telefono_principal
                ?: $cliente->telefono_secundario
        );

        if ($telefono === null) {
            return back()->with(
                'error',
                'El cliente no tiene un teléfono válido para realizar la llamada.'
            );
        }

        $estado = DB::table('estados_llamada')
            ->where('activo', true)
            ->whereRaw('LOWER(nombre) = LOWER(?)', ['En curso'])
            ->first();

        if (!$estado) {
            $estado = DB::table('estados_llamada')
                ->where('activo', true)
                ->orderBy('id_estado_llamada')
                ->first();
        }

        if (!$estado) {
            return back()->with(
                'error',
                'No existe un estado de llamada activo en el catálogo.'
            );
        }

        $actionId = 'CRM-'
            . $usuario->id_usuario
            . '-'
            . $cliente->id_cliente
            . '-'
            . now()->format('YmdHis')
            . '-'
            . Str::lower(Str::random(6));

        try {
            $response = $asterisk->originate(
                $extension,
                $telefono,
                $actionId,
                [
                    'CRM_ID_EMPRESA' => $usuario->id_empresa,
                    'CRM_ID_USUARIO' => $usuario->id_usuario,
                    'CRM_ID_CLIENTE' => $cliente->id_cliente,
                    'CRM_ACTION_ID'  => $actionId,
                ]
            );

            $llamada = Llamada::create([
                'id_empresa'             => $usuario->id_empresa,
                'id_cliente'             => $cliente->id_cliente,
                'id_usuario'             => $usuario->id_usuario,
                'id_campana'             => null,
                'id_resultado'            => null,
                'id_estado_llamada'      => $estado->id_estado_llamada,
                'tipo_llamada'           => 'saliente',
                'fecha_inicio'            => now(),
                'fecha_fin'               => null,
                'duracion'                => null,
                'numero_origen'           => $extension,
                'numero_destino'          => $telefono,
                'identificador_asterisk'  => $actionId,
                'grabacion_url'           => null,
                'observaciones'           => 'Llamada saliente solicitada desde el CRM. '
                    . ($response['Message'] ?? 'Originate aceptado por Asterisk.'),
            ]);

            return redirect()
                ->route('llamadas.show', $llamada->id_llamada)
                ->with(
                    'success',
                    'Asterisk aceptó la llamada. Primero sonará la extensión '
                    . $extension
                    . ' del operario.'
                );
        } catch (RuntimeException $e) {
            report($e);

            return back()->with(
                'error',
                'No se pudo iniciar la llamada: ' . $e->getMessage()
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Ocurrió un error al registrar o iniciar la llamada.'
            );
        }
    }

    private function normalizarTelefono(?string $telefono): ?string
    {
        if (!$telefono) {
            return null;
        }

        $telefono = trim($telefono);

        $prefijo = str_starts_with($telefono, '+') ? '+' : '';
        $digitos = preg_replace('/\D+/', '', $telefono);

        if (!$digitos) {
            return null;
        }

        if (strlen($digitos) < 7 || strlen($digitos) > 15) {
            return null;
        }

        return $prefijo . $digitos;
    }
}
