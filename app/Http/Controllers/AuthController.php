<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | VERIFICAR ACCESO
    |--------------------------------------------------------------------------
    */

    public function verificarAcceso(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. VALIDAR FORMULARIO
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate([
            'correo' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | 2. BUSCAR USUARIO
        |--------------------------------------------------------------------------
        |
        | Nueva BD:
        |
        | users.email
        |       ↓
        | usuarios.correo
        |
        */

        $usuario = User::with('aprobacionActual')
            ->whereRaw(
                'LOWER(correo) = LOWER(?)',
                [$datos['correo']]
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | 3. VALIDAR CREDENCIALES
        |--------------------------------------------------------------------------
        |
        | Nueva BD:
        |
        | users.password
        |       ↓
        | usuarios.password_hash
        |
        */

        if (
            !$usuario ||
            !Hash::check(
                $datos['password'],
                $usuario->password_hash
            )
        ) {
            return back()
                ->withErrors([
                    'correo' =>
                        'Las credenciales no coinciden con nuestros registros.',
                ])
                ->withInput(
                    $request->only('correo')
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 4. VALIDAR ESTADO GENERAL DEL USUARIO
        |--------------------------------------------------------------------------
        */

        if (!$usuario->activo) {
            return back()
                ->withErrors([
                    'correo' =>
                        'Tu cuenta se encuentra inactiva. Contacta al administrador.',
                ])
                ->withInput(
                    $request->only('correo')
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 5. VALIDACIONES ESPECIALES PARA OPERARIOS
        |--------------------------------------------------------------------------
        */

        if ($usuario->esOperario()) {

            /*
            |--------------------------------------------------------------------------
            | OPERARIO SIN TIPO ASIGNADO
            |--------------------------------------------------------------------------
            */

            if (!$usuario->tieneTipoOperario()) {
                return back()
                    ->withErrors([
                        'correo' =>
                            'Tu cuenta de operario no tiene un tipo asignado. Contacta al administrador.',
                    ])
                    ->withInput(
                        $request->only('correo')
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | APROBACIÓN PENDIENTE
            |--------------------------------------------------------------------------
            */

            if ($usuario->aprobacionPendiente()) {
                return back()
                    ->withErrors([
                        'correo' =>
                            'Tu solicitud de acceso todavía está pendiente de aprobación.',
                    ])
                    ->withInput(
                        $request->only('correo')
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | SOLICITUD RECHAZADA
            |--------------------------------------------------------------------------
            */

            if ($usuario->fueRechazado()) {
                return back()
                    ->withErrors([
                        'correo' =>
                            'Tu solicitud de acceso fue rechazada por el administrador.',
                    ])
                    ->withInput(
                        $request->only('correo')
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | SIN APROBACIÓN VÁLIDA
            |--------------------------------------------------------------------------
            */

            if (!$usuario->estaAprobado()) {
                return back()
                    ->withErrors([
                        'correo' =>
                            'Tu cuenta de operario todavía no cuenta con una aprobación válida.',
                    ])
                    ->withInput(
                        $request->only('correo')
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 6. INICIAR SESIÓN
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $usuario,
            $request->boolean('remember')
        );

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | 7. REGISTRAR ÚLTIMO ACCESO
        |--------------------------------------------------------------------------
        */

        $usuario->ultimo_acceso = now();
        $usuario->save();


        /*
        |--------------------------------------------------------------------------
        | 8. REDIRECCIÓN
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->intended(
                route('dashboard')
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function mostrarExito()
    {
        if (!Auth::check()) {
            return redirect()
                ->route('login');
        }

        $usuario = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | PROTECCIÓN ADICIONAL
        |--------------------------------------------------------------------------
        |
        | Si un usuario fue desactivado después de iniciar sesión,
        | se cierra su sesión al volver a acceder.
        |
        */

        if (!$usuario->activo) {
            Auth::logout();

            request()
                ->session()
                ->invalidate();

            request()
                ->session()
                ->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'correo' =>
                        'Tu cuenta se encuentra inactiva.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | PROTECCIÓN PARA OPERARIOS
        |--------------------------------------------------------------------------
        */

        if (
            $usuario->esOperario() &&
            !$usuario->puedeOperar()
        ) {
            Auth::logout();

            request()
                ->session()
                ->invalidate();

            request()
                ->session()
                ->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'correo' =>
                        'Tu cuenta de operario no cuenta con autorización para acceder.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | MÉTRICAS
        |--------------------------------------------------------------------------
        */

        $metricasData = [];

        if ((int) $usuario->id_rol === 3) {
            /*
            |--------------------------------------------------------------------------
            | Más adelante aquí podremos cargar:
            |--------------------------------------------------------------------------
            |
            | llamadas realizadas
            | objetivo vigente
            | progreso
            | citas
            | clientes asignados
            |
            */
        }


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard',
            compact(
                'usuario',
                'metricasData'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR SESIÓN
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        $request
            ->session()
            ->invalidate();

        $request
            ->session()
            ->regenerateToken();

        return redirect()
            ->route('login');
    }
}