<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConfiguracionController extends Controller
{
    /**
     * Mostrar configuración de la cuenta actual.
     */
    public function index(Request $request)
    {
        $usuario = $request->user();

        $rolNombre = match ((int) $usuario->id_rol) {
            1 => 'Súper Admin',
            2 => 'Admin Cliente',
            3 => 'Operario / Agente',
            4 => 'Asesor comercial',
            default => 'Usuario',
        };

        return view(
            'configuracion.index',
            compact(
                'usuario',
                'rolNombre'
            )
        );
    }


    /**
     * Cambiar contraseña de la cuenta autenticada.
     */
    public function actualizarPassword(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validate(
            [
                'password_actual' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'password' => [
                    'required',
                    'string',

                    // Mínimo 8 caracteres
                    'min:8',

                    // Límite técnico razonable
                    'max:64',

                    // Debe coincidir con password_confirmation
                    'confirmed',

                    // Una mayúscula
                    'regex:/[A-Z]/',

                    // Una minúscula
                    'regex:/[a-z]/',

                    // Un número
                    'regex:/[0-9]/',

                    // Un carácter especial
                    'regex:/[^A-Za-z0-9\s]/',

                    // No se permiten espacios
                    'not_regex:/\s/',
                ],
            ],
            [
                'password_actual.required' =>
                    'Debes ingresar tu contraseña actual.',

                'password_actual.string' =>
                    'La contraseña actual no tiene un formato válido.',

                'password.required' =>
                    'Debes ingresar una nueva contraseña.',

                'password.string' =>
                    'La nueva contraseña no tiene un formato válido.',

                'password.min' =>
                    'La contraseña debe contener mínimo 8 caracteres.',

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
        | VALIDAR CONTRASEÑA ACTUAL
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                $datos['password_actual'],
                $usuario->password_hash
            )
        ) {
            return back()
                ->withErrors([
                    'password_actual' =>
                        'La contraseña actual es incorrecta.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EVITAR REUTILIZAR LA CONTRASEÑA ACTUAL
        |--------------------------------------------------------------------------
        */

        if (
            Hash::check(
                $datos['password'],
                $usuario->password_hash
            )
        ) {
            return back()
                ->withErrors([
                    'password' =>
                        'La nueva contraseña debe ser diferente a la actual.',
                ]);
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

            'remember_token' =>
                Str::random(60),
        ]);

        $usuario->save();


        /*
        |--------------------------------------------------------------------------
        | RENOVAR SESIÓN ACTUAL
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | AUDITORÍA
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Contraseña actualizada por el usuario',
            [
                'id_usuario' =>
                    $usuario->id_usuario,

                'ip' =>
                    $request->ip(),

                'user_agent' =>
                    $request->userAgent(),
            ]
        );


        return back()->with(
            'exito',
            'Tu contraseña se actualizó correctamente.'
        );
    }
}