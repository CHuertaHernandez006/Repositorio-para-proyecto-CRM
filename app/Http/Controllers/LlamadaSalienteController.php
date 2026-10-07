<?php

namespace App\Http\Controllers;

use App\Models\Cliente;

use App\Models\Llamada;

use App\Services\AsteriskAmiService;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

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

        /**

**        |--------------------------------------------------------------------------**

**        | VALIDACIÓN DEL OPERARIO**

**        |--------------------------------------------------------------------------**

**/

        if (!$usuario || (int) $usuario->id_rol !== 3) {

            abort(403, 'Solo los operarios pueden iniciar llamadas.');

        }

        if (!$usuario->activo) {

            abort(403, 'Tu cuenta no está activa.');

        }

        /**

**        |--------------------------------------------------------------------------**

**        | VALIDACIÓN DEL CLIENTE**

**        |--------------------------------------------------------------------------**

**/

        if ((int) $cliente->id_empresa !== (int) $usuario->id_empresa) {

            abort(403, 'El cliente no pertenece a tu empresa.');

        }

        if ($cliente->deleted_at !== null) {

            abort(404);

        }

        /**

**        |--------------------------------------------------------------------------**

**        | CITA / CONTACTO PROGRAMADO**

**        |--------------------------------------------------------------------------**

**        | id_cita es opcional:**

**        | - Si la llamada se hace desde Clientes, puede venir vacío.**

**        | - Si la llamada se hace desde Agenda, debe pertenecer al mismo**

**        |   cliente, empresa y operario autenticado.**

**        |--------------------------------------------------------------------------**

**/

        $datos = $request->validate([

            'id_cita' => ['nullable', 'integer', 'min:1'],

        ]);

        $cita = null;

        if (!empty($datos['id_cita'])) {

            $cita = DB::table('citas')

                ->leftJoin(

                    'estados_cita',

                    'estados_cita.id_estado_cita',

                    '=',

                    'citas.id_estado_cita'

                )

                ->where(

                    'citas.id_cita',

                    $datos['id_cita']

                )

                ->where(

                    'citas.id_empresa',

                    $usuario->id_empresa

                )

                ->where(

                    'citas.id_cliente',

                    $cliente->id_cliente

                )

                ->where(

                    'citas.id_usuario',

                    $usuario->id_usuario

                )

                ->select([

                    'citas.\\*',

                    'estados_cita.nombre as estado_cita_nombre',

                ])

                ->first();

            if (!$cita) {

                return back()->with(

                    'error',

                    'El contacto programado no existe o no está asignado a tu usuario.'

                );

            }

            $estadoCita = mb_strtolower(

                trim((string) ($cita->estado_cita_nombre ?? '')),

                'UTF-8'

            );

            if (in_array(

                $estadoCita,

                ['realizada', 'cancelada'],

                true

            )) {

                return back()->with(

                    'error',

                    'Este contacto ya está cerrado y no puede iniciar una nueva llamada desde la agenda.'

                );

            }

        }

        /**

**        |--------------------------------------------------------------------------**

**        | EXTENSIÓN DEL OPERARIO**

**        |--------------------------------------------------------------------------**

**/

        $extension = trim(

            (string) ($usuario->extension_asterisk ?? '')

        );

        if ($extension === '') {

            return back()->with(

                'error',

                'Tu usuario todavía no tiene una extensión de Asterisk asignada.'

            );

        }

        /**

**        |--------------------------------------------------------------------------**

**        | TELÉFONO DEL CLIENTE**

**        |--------------------------------------------------------------------------**

**/

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

        /**

**        |--------------------------------------------------------------------------**

**        | ESTADO INICIAL DE LA LLAMADA**

**        |--------------------------------------------------------------------------**

**/

        $estado = DB::table('estados_llamada')

            ->where('activo', true)

            ->whereRaw(

                'LOWER(nombre) = LOWER(?)',

                ['En curso']

            )

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

        /**

**        |--------------------------------------------------------------------------**

**        | IDENTIFICADOR DE ASTERISK / AMI**

**        |--------------------------------------------------------------------------**

**/

        $actionId = 'CRM-'

            . $usuario->id_usuario

            . '-'

            . $cliente->id_cliente

            . '-'

            . now()->format('YmdHis')

            . '-'

            . Str::lower(

                Str::random(6)

            );

        /**

**        |--------------------------------------------------------------------------**

**        | VARIABLES PARA ASTERISK**

**        |--------------------------------------------------------------------------**

**/

        $variablesAsterisk = [

            'CRM_ID_EMPRESA' => $usuario->id_empresa,

            'CRM_ID_USUARIO' => $usuario->id_usuario,

            'CRM_ID_CLIENTE' => $cliente->id_cliente,

            'CRM_ACTION_ID'  => $actionId,

            /**

*            |--------------------------------------------------------------------------*

*            | NOMBRE DE LA GRABACIÓN*

*            |--------------------------------------------------------------------------*

*            |*

*            | Infraestructura/Asterisk usa esta variable en MixMonitor para*

*            | guardar el WAV con el mismo identificador que conserva el CRM.*

*            |--------------------------------------------------------------------------*

*            */

            'RECORD_FILENAME' => $actionId,

        ];

        if ($cita) {

            $variablesAsterisk['CRM_ID_CITA'] = $cita->id_cita;

        }

        /**

*        |--------------------------------------------------------------------------*

*        | URL PÚBLICA ESPERADA DE LA GRABACIÓN*

*        |--------------------------------------------------------------------------*

*        |*

*        | Nginx publicará /recordings/ y Asterisk guardará:*

*        | {identificador_asterisk}.wav*

*        |--------------------------------------------------------------------------*

*        */

        $grabacionUrl = '/recordings/'

            . rawurlencode($actionId)

            . '.wav';

        try {

            /**

**            |--------------------------------------------------------------------------**

**            | SOLICITAR ORIGINATE A ASTERISK**

**            |--------------------------------------------------------------------------**

**/

            $response = $asterisk->originate(

                $extension,

                $telefono,

                $actionId,

                $variablesAsterisk

            );

            /**

**            |--------------------------------------------------------------------------**

**            | REGISTRAR LLAMADA Y VINCULARLA CON LA AGENDA**

**            |--------------------------------------------------------------------------**

**            | Un "Originate aceptado" significa que Asterisk aceptó intentar**

**            | la llamada. Todavía NO se marca la cita como Realizada.**

**            |--------------------------------------------------------------------------**

**/

            $llamada = DB::transaction(

                function () use (

                    $usuario,

                    $cliente,

                    $estado,

                    $extension,

                    $telefono,

                    $actionId,

                    $grabacionUrl,

                    $response,

                    $cita

                ) {

                    $observaciones = 'Llamada saliente solicitada desde el CRM. '

                        . ($response['Message']

                            ?? 'Originate aceptado por Asterisk.');

                    if ($cita) {

                        $observaciones .= ' Contacto programado #'

                            . $cita->id_cita

                            . '.';

                    }

                    $llamada = Llamada::create([

                        'id_empresa'            => $usuario->id_empresa,

                        'id_cliente'            => $cliente->id_cliente,

                        'id_usuario'            => $usuario->id_usuario,

                        'id_campana'            => null,

                        'id_resultado'          => null,

                        'id_estado_llamada'     => $estado->id_estado_llamada,

                        'tipo_llamada'          => 'saliente',

                        'fecha_inicio'           => now(),

                        'fecha_fin'              => null,

                        'duracion'               => null,

                        'numero_origen'          => $extension,

                        'numero_destino'         => $telefono,

                        'identificador_asterisk' => $actionId,

                        'grabacion_url'             => $grabacionUrl,

                        'observaciones'          => $observaciones,

                    ]);

                    /**

**                    |--------------------------------------------------------------------------**

**                    | ENLACE CITA -> LLAMADA**

**                    |--------------------------------------------------------------------------**

**                    | Solo se actualiza id_llamada.**

**                    | El estado de la cita sigue Pendiente/Confirmada hasta conocer**

**                    | el resultado real de la llamada.**

**                    |--------------------------------------------------------------------------**

**/

                    if ($cita) {

                        DB::table('citas')

                            ->where(

                                'id_cita',

                                $cita->id_cita

                            )

                            ->where(

                                'id_empresa',

                                $usuario->id_empresa

                            )

                            ->where(

                                'id_cliente',

                                $cliente->id_cliente

                            )

                            ->where(

                                'id_usuario',

                                $usuario->id_usuario

                            )

                            ->update([

                                'id_llamada' => $llamada->id_llamada,

                                'updated_at' => now(),

                            ]);

                    }

                    return $llamada;

                }

            );

            /**

**            |--------------------------------------------------------------------------**

**            | RESPUESTA**

**            |--------------------------------------------------------------------------**

**/

            $mensaje = 'Asterisk aceptó la llamada. Primero sonará la extensión '

                . $extension

                . ' del operario.';

            if ($cita) {

                $mensaje .= ' La llamada quedó vinculada con el contacto programado de la agenda.';

            }

            return redirect()

                ->route(

                    'llamadas.show',

                    $llamada->id_llamada

                )

                ->with(

                    'success',

                    $mensaje

                );

        } catch (RuntimeException $e) {

            report($e);

            return back()->with(

                'error',

                'No se pudo iniciar la llamada: '

                . $e->getMessage()

            );

        } catch (Throwable $e) {

            report($e);

            return back()->with(

                'error',

                'Ocurrió un error al registrar o iniciar la llamada.'

            );

        }

    }


    /**
     * Recibe el aviso de Asterisk cuando una llamada termina.
     *
     * Asterisk debe enviar el mismo identificador que Laravel colocó en:
     * - CRM_ACTION_ID
     * - RECORD_FILENAME
     * - llamadas.identificador_asterisk
     *
     * Se aceptan "action_id" o "record_filename" para facilitar la integración.
     */
    public function finalizarLlamada(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | AUTENTICACIÓN DEL WEBHOOK
        |--------------------------------------------------------------------------
        |
        | El endpoint no usa la sesión de un operario porque lo invoca Asterisk.
        | Se protege con un token compartido enviado en X-Asterisk-Token.
        |--------------------------------------------------------------------------
        */

        $tokenEsperado = trim(
            (string) config(
                'asterisk.webhook_token',
                ''
            )
        );

        $tokenRecibido = trim(
            (string) (
                $request->header(
                    'X-Asterisk-Token'
                )
                ?: $request->input(
                    'token',
                    ''
                )
            )
        );

        if ($tokenEsperado === '') {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'ASTERISK_WEBHOOK_TOKEN no está configurado en el CRM.',
            ], 503);
        }

        if (
            $tokenRecibido === ''
            || !hash_equals(
                $tokenEsperado,
                $tokenRecibido
            )
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de Asterisk inválido.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | IDENTIFICADOR DE LA LLAMADA
        |--------------------------------------------------------------------------
        */

        $actionId = trim(
            (string) (
                $request->input(
                    'action_id'
                )
                ?: $request->input(
                    'record_filename'
                )
                ?: ''
            )
        );

        if ($actionId === '') {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'action_id o record_filename es requerido.',
            ], 422);
        }

        $llamada = DB::table('llamadas')
            ->where(
                'identificador_asterisk',
                $actionId
            )
            ->first();

        if (!$llamada) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'No se encontró una llamada con ese identificador.',
                'identificador_asterisk' =>
                    $actionId,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADO FINALIZADA
        |--------------------------------------------------------------------------
        */

        $estadoFinalizada = DB::table(
            'estados_llamada'
        )
            ->where('activo', true)
            ->whereRaw(
                'LOWER(nombre) = LOWER(?)',
                ['Finalizada']
            )
            ->first();

        if (!$estadoFinalizada) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'No existe el estado Finalizada en estados_llamada.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | FECHA DE FIN Y DURACIÓN
        |--------------------------------------------------------------------------
        |
        | duracion continúa siendo INTEGER porque almacena SEGUNDOS.
        |--------------------------------------------------------------------------
        */

        $fin = $llamada->fecha_fin
            ? \Carbon\Carbon::parse(
                $llamada->fecha_fin
            )
            : now();

        $inicio = \Carbon\Carbon::parse(
            $llamada->fecha_inicio
        );

        $duracion = $llamada->duracion !== null
            ? max(
                0,
                (int) $llamada->duracion
            )
            : max(
                0,
                (int) round(
                    $inicio->diffInSeconds(
                        $fin
                    )
                )
            );

        $grabacionUrl =
            $llamada->grabacion_url
            ?: '/recordings/'
                . rawurlencode($actionId)
                . '.wav';

        /*
        |--------------------------------------------------------------------------
        | ACTUALIZACIÓN IDÉMPOTENTE
        |--------------------------------------------------------------------------
        |
        | Si Asterisk manda el aviso dos veces, la llamada no se duplica:
        | simplemente queda correctamente cerrada.
        |--------------------------------------------------------------------------
        */

        DB::table('llamadas')
            ->where(
                'id_llamada',
                $llamada->id_llamada
            )
            ->update([
                'id_estado_llamada' =>
                    $estadoFinalizada->id_estado_llamada,

                'fecha_fin' =>
                    $llamada->fecha_fin
                        ?: $fin,

                'duracion' =>
                    $duracion,

                'grabacion_url' =>
                    $grabacionUrl,

                'updated_at' =>
                    now(),
            ]);

        return response()->json([
            'status' => 'success',
            'id_llamada' =>
                $llamada->id_llamada,
            'identificador_asterisk' =>
                $actionId,
            'estado' =>
                'Finalizada',
            'duracion' =>
                $duracion,
            'grabacion_url' =>
                $grabacionUrl,
        ]);
    }


    private function normalizarTelefono(

        ?string $telefono

    ): ?string {

        if (!$telefono) {

            return null;

        }

        $telefono = trim($telefono);

        $prefijo = str_starts_with(

            $telefono,

            '+'

        )

            ? '+'

            : '';

        $digitos = preg_replace(

            '/\D+/',

            '',

            $telefono

        );

        if (!$digitos) {

            return null;

        }

        if (

            strlen($digitos) < 7

            || strlen($digitos) > 15

        ) {

            return null;

        }

        return $prefijo . $digitos;

    }

}
