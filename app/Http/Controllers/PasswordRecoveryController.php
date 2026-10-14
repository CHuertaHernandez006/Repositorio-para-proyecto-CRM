<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordRecoveryController extends Controller
{
    /**
     * |--------------------------------------------------------------------------
     * | MOSTRAR FORMULARIO DE RECUPERACIÓN
     * |--------------------------------------------------------------------------
     */
    public function mostrarSolicitud()
    {
        return view('auth.forgot-password');
    }


    /**
     * |--------------------------------------------------------------------------
     * | ENVIAR ENLACE DE RECUPERACIÓN
     * |--------------------------------------------------------------------------
     */
    public function enviarEnlace(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDAR CORREO
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate(
            [
                'correo' => [
                    'required',
                    'email',
                    'max:150',
                ],
            ],
            [
                'correo.required' =>
                    'Ingresa tu correo electrónico.',

                'correo.email' =>
                    'Ingresa un correo electrónico válido.',
            ]
        );


        $correo = mb_strtolower(
            trim($datos['correo']),
            'UTF-8'
        );


        /*
        |--------------------------------------------------------------------------
        | BUSCAR USUARIO
        |--------------------------------------------------------------------------
        |
        | El mensaje de respuesta será siempre el mismo,
        | exista o no la cuenta.
        |
        */

        $usuario = User::query()
            ->whereRaw(
                'LOWER(correo) = LOWER(?)',
                [$correo]
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | GENERAR RECUPERACIÓN
        |--------------------------------------------------------------------------
        */

        if (
            $usuario &&
            $usuario->activo
        ) {

            /*
            |--------------------------------------------------------------------------
            | INVALIDAR ENLACES ANTERIORES
            |--------------------------------------------------------------------------
            */

            DB::table('recuperacion_password')
                ->where(
                    'id_usuario',
                    $usuario->id_usuario
                )
                ->whereNull('used_at')
                ->update([
                    'used_at' => now(),
                    'updated_at' => now(),
                ]);


            /*
            |--------------------------------------------------------------------------
            | GENERAR TOKEN
            |--------------------------------------------------------------------------
            */

            $token = Str::random(64);


            /*
            |--------------------------------------------------------------------------
            | GUARDAR SOLO EL HASH DEL TOKEN
            |--------------------------------------------------------------------------
            */

            $tokenHash = hash(
                'sha256',
                $token
            );


            DB::table(
                'recuperacion_password'
            )->insert([
                'id_usuario' =>
                    $usuario->id_usuario,

                'token_hash' =>
                    $tokenHash,

                'expires_at' =>
                    now()->addMinutes(30),

                'used_at' =>
                    null,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | CONSTRUIR URL
            |--------------------------------------------------------------------------
            */

            $url = route(
                'password.reset.form',
                [
                    'token' => $token,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | ENVIAR CORREO
            |--------------------------------------------------------------------------
            */

            try {

                Mail::to(
                    $usuario->correo
                )->send(
                    new PasswordResetMail(
                        $usuario,
                        $url
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | AUDITORÍA
                |--------------------------------------------------------------------------
                */

                Log::info(
                    'Enlace de recuperación enviado',
                    [
                        'id_usuario' =>
                            $usuario->id_usuario,

                        'ip' =>
                            $request->ip(),

                        'user_agent' =>
                            $request->userAgent(),
                    ]
                );

            } catch (\Throwable $exception) {

                /*
                |--------------------------------------------------------------------------
                | INVALIDAR TOKEN SI FALLÓ EL ENVÍO
                |--------------------------------------------------------------------------
                */

                DB::table(
                    'recuperacion_password'
                )
                    ->where(
                        'token_hash',
                        $tokenHash
                    )
                    ->update([
                        'used_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);


                /*
                |--------------------------------------------------------------------------
                | REGISTRAR ERROR
                |--------------------------------------------------------------------------
                */

                Log::error(
                    'Error enviando correo de recuperación',
                    [
                        'id_usuario' =>
                            $usuario->id_usuario,

                        'mensaje' =>
                            $exception->getMessage(),
                    ]
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RESPUESTA NEUTRAL
        |--------------------------------------------------------------------------
        |
        | No indicamos si el correo existe o no para evitar
        | enumeración de cuentas.
        |
        */

        return back()->with(
            'status',
            'Si existe una cuenta asociada a ese correo, recibirás un enlace para restablecer tu contraseña.'
        );
    }


    /**
     * |--------------------------------------------------------------------------
     * | MOSTRAR FORMULARIO PARA NUEVA CONTRASEÑA
     * |--------------------------------------------------------------------------
     */
    public function mostrarRestablecer(
        string $token
    ) {
        /*
        |--------------------------------------------------------------------------
        | OBTENER HASH DEL TOKEN
        |--------------------------------------------------------------------------
        */

        $tokenHash = hash(
            'sha256',
            $token
        );


        /*
        |--------------------------------------------------------------------------
        | BUSCAR TOKEN
        |--------------------------------------------------------------------------
        */

        $registro = DB::table(
            'recuperacion_password'
        )
            ->where(
                'token_hash',
                $tokenHash
            )
            ->whereNull('used_at')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | VALIDAR TOKEN
        |--------------------------------------------------------------------------
        */

        if (
            !$registro ||
            now()->greaterThan(
                Carbon::parse(
                    $registro->expires_at
                )
            )
        ) {

            return redirect()
                ->route(
                    'password.request'
                )
                ->withErrors([
                    'correo' =>
                        'El enlace de recuperación es inválido o ya venció.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR FORMULARIO
        |--------------------------------------------------------------------------
        */

        return view(
            'auth.reset-password',
            [
                'token' => $token,
            ]
        );
    }


    /**
     * |--------------------------------------------------------------------------
     * | RESTABLECER CONTRASEÑA
     * |--------------------------------------------------------------------------
     */
    public function restablecer(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | VALIDAR FORMULARIO
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate(
            [
                'token' => [
                    'required',
                    'string',
                ],

                'password' => [
                    'required',
                    'string',

                    /*
                    |--------------------------------------------------------------------------
                    | MÍNIMO 8 CARACTERES
                    |--------------------------------------------------------------------------
                    */

                    'min:8',

                    /*
                    |--------------------------------------------------------------------------
                    | LÍMITE TÉCNICO
                    |--------------------------------------------------------------------------
                    */

                    'max:64',

                    /*
                    |--------------------------------------------------------------------------
                    | PASSWORD_CONFIRMATION
                    |--------------------------------------------------------------------------
                    */

                    'confirmed',

                    /*
                    |--------------------------------------------------------------------------
                    | AL MENOS UNA MAYÚSCULA
                    |--------------------------------------------------------------------------
                    */

                    'regex:/[A-Z]/',

                    /*
                    |--------------------------------------------------------------------------
                    | AL MENOS UNA MINÚSCULA
                    |--------------------------------------------------------------------------
                    */

                    'regex:/[a-z]/',

                    /*
                    |--------------------------------------------------------------------------
                    | AL MENOS UN NÚMERO
                    |--------------------------------------------------------------------------
                    */

                    'regex:/[0-9]/',

                    /*
                    |--------------------------------------------------------------------------
                    | AL MENOS UN CARÁCTER ESPECIAL
                    |--------------------------------------------------------------------------
                    */

                    'regex:/[^A-Za-z0-9\s]/',

                    /*
                    |--------------------------------------------------------------------------
                    | SIN ESPACIOS
                    |--------------------------------------------------------------------------
                    */

                    'not_regex:/\s/',
                ],
            ],
            [
                'token.required' =>
                    'El enlace de recuperación no es válido.',

                'password.required' =>
                    'Debes ingresar una nueva contraseña.',

                'password.string' =>
                    'La contraseña no tiene un formato válido.',

                'password.min' =>
                    'La contraseña debe tener al menos 8 caracteres.',

                'password.max' =>
                    'La contraseña es demasiado larga.',

                'password.confirmed' =>
                    'Las contraseñas no coinciden.',

                'password.regex' =>
                    'La contraseña no cumple con todos los requisitos de seguridad.',

                'password.not_regex' =>
                    'La contraseña no debe contener espacios.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | GENERAR HASH DEL TOKEN RECIBIDO
        |--------------------------------------------------------------------------
        */

        $tokenHash = hash(
            'sha256',
            $datos['token']
        );


        try {

            /*
            |--------------------------------------------------------------------------
            | TRANSACCIÓN
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $datos,
                    $tokenHash,
                    $request
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | OBTENER Y BLOQUEAR TOKEN
                    |--------------------------------------------------------------------------
                    |
                    | lockForUpdate evita que el mismo token pueda ser utilizado
                    | simultáneamente en dos solicitudes.
                    |
                    */

                    $registro = DB::table(
                        'recuperacion_password'
                    )
                        ->where(
                            'token_hash',
                            $tokenHash
                        )
                        ->whereNull('used_at')
                        ->lockForUpdate()
                        ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | VALIDAR EXISTENCIA Y VENCIMIENTO
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$registro ||
                        now()->greaterThan(
                            Carbon::parse(
                                $registro->expires_at
                            )
                        )
                    ) {

                        throw new \RuntimeException(
                            'TOKEN_INVALIDO'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | OBTENER USUARIO
                    |--------------------------------------------------------------------------
                    */

                    $usuario = User::find(
                        $registro->id_usuario
                    );


                    if (!$usuario) {

                        throw new \RuntimeException(
                            'TOKEN_INVALIDO'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | VALIDAR QUE LA CUENTA SIGA ACTIVA
                    |--------------------------------------------------------------------------
                    */

                    if (!$usuario->activo) {

                        throw new \RuntimeException(
                            'USUARIO_INACTIVO'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | EVITAR REUTILIZAR LA MISMA CONTRASEÑA
                    |--------------------------------------------------------------------------
                    */

                    if (
                        Hash::check(
                            $datos['password'],
                            $usuario->password_hash
                        )
                    ) {

                        throw new \RuntimeException(
                            'PASSWORD_REPETIDA'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ACTUALIZAR CONTRASEÑA
                    |--------------------------------------------------------------------------
                    */

                    $usuario->forceFill([
                        'password_hash' =>
                            Hash::make(
                                $datos['password']
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | INVALIDAR "RECORDARME"
                        |--------------------------------------------------------------------------
                        */

                        'remember_token' =>
                            Str::random(60),
                    ]);


                    $usuario->save();


                    /*
                    |--------------------------------------------------------------------------
                    | INVALIDAR TODOS LOS TOKENS DEL USUARIO
                    |--------------------------------------------------------------------------
                    */

                    DB::table(
                        'recuperacion_password'
                    )
                        ->where(
                            'id_usuario',
                            $usuario->id_usuario
                        )
                        ->whereNull('used_at')
                        ->update([
                            'used_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | AUDITORÍA
                    |--------------------------------------------------------------------------
                    */

                    Log::info(
                        'Contraseña restablecida mediante correo',
                        [
                            'id_usuario' =>
                                $usuario->id_usuario,

                            'ip' =>
                                $request->ip(),

                            'user_agent' =>
                                $request->userAgent(),
                        ]
                    );
                }
            );

        } catch (\RuntimeException $exception) {

            /*
            |--------------------------------------------------------------------------
            | CONTRASEÑA REPETIDA
            |--------------------------------------------------------------------------
            */

            if (
                $exception->getMessage()
                === 'PASSWORD_REPETIDA'
            ) {

                return back()
                    ->withErrors([
                        'password' =>
                            'La nueva contraseña debe ser diferente a la contraseña anterior.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | USUARIO INACTIVO
            |--------------------------------------------------------------------------
            */

            if (
                $exception->getMessage()
                === 'USUARIO_INACTIVO'
            ) {

                return redirect()
                    ->route(
                        'password.request'
                    )
                    ->withErrors([
                        'correo' =>
                            'No fue posible restablecer la contraseña de esta cuenta.',
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | TOKEN INVÁLIDO
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'password.request'
                )
                ->withErrors([
                    'correo' =>
                        'El enlace de recuperación es inválido, ya fue utilizado o venció.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ÉXITO
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('login')
            ->with(
                'exito',
                'Tu contraseña fue restablecida correctamente. Ya puedes iniciar sesión.'
            );
    }
}