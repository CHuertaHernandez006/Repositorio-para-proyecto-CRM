<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmpresaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $this->autorizarSuperAdmin();

        $empresas = Empresa::query()
            ->orderByDesc('id_empresa')
            ->paginate(10);

        return view(
            'empresas.index',
            compact('empresas')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULARIO CREAR
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $this->autorizarSuperAdmin();

        return view('empresas.create');
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR EMPRESA + ADMIN CLIENTE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $this->autorizarSuperAdmin();

        $datos = $request->validate(
            [
                'tipo_cliente' => [
                    'required',
                    'in:empresa,independiente',
                ],

                'nombre_empresa' => [
                    'nullable',
                    'required_if:tipo_cliente,empresa',
                    'string',
                    'max:150',
                ],

                'nombre_admin' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'correo_admin' => [
                    'required',
                    'email',
                    'max:150',
                ],
            ],
            [
                'tipo_cliente.required' =>
                    'Selecciona el tipo de cliente.',

                'nombre_empresa.required_if' =>
                    'El nombre de la empresa es obligatorio.',

                'nombre_admin.required' =>
                    'El nombre del administrador es obligatorio.',

                'correo_admin.required' =>
                    'El correo del administrador es obligatorio.',

                'correo_admin.email' =>
                    'Ingresa un correo electrónico válido.',
            ]
        );

        $correoAdmin =
            mb_strtolower(
                trim($datos['correo_admin']),
                'UTF-8'
            );

        $this->validarCorreoDisponible(
            $correoAdmin
        );

        /*
        |--------------------------------------------------------------------------
        | EMPRESA VS INDEPENDIENTE
        |--------------------------------------------------------------------------
        */

        $nombreFinalEmpresa =
            $datos['tipo_cliente'] ===
            'independiente'
                ? 'Independiente - ' .
                    trim($datos['nombre_admin'])
                : trim(
                    $datos['nombre_empresa']
                );

        /*
        |--------------------------------------------------------------------------
        | CONTRASEÑA TEMPORAL
        |--------------------------------------------------------------------------
        */

        $passwordTemporal =
            $this->generarPasswordTemporal();

        DB::transaction(
            function () use (
                $datos,
                $correoAdmin,
                $nombreFinalEmpresa,
                $passwordTemporal
            ) {
                /*
                |--------------------------------------------------------------------------
                | CREAR EMPRESA
                |--------------------------------------------------------------------------
                */

                $empresa = Empresa::create([
                    'nombre' =>
                        $nombreFinalEmpresa,

                    'slug' =>
                        $this->generarSlugUnico(
                            $nombreFinalEmpresa
                        ),

                    'activo' =>
                        true,
                ]);

                /*
                |--------------------------------------------------------------------------
                | CREAR ADMIN CLIENTE
                |--------------------------------------------------------------------------
                |
                | User.php mantiene el alias "name" para distribuir el nombre
                | sobre los campos reales nombre/apellidos de usuarios.
                |--------------------------------------------------------------------------
                */

                $user = new User();

                $user->name =
                    trim($datos['nombre_admin']);

                $user->correo =
                    $correoAdmin;

                $user->password_hash =
                    Hash::make(
                        $passwordTemporal
                    );

                $user->id_rol = 2;
                $user->id_empresa =
                    $empresa->id_empresa;

                $user->id_tipo_operario =
                    null;

                $user->activo =
                    true;

                $user->save();
            }
        );

        return redirect()
            ->route('empresas.index')
            ->with(
                'success',
                'Empresa y Administrador creados con éxito. Contraseña temporal del cliente: '
                . $passwordTemporal
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDITAR EMPRESA
    |--------------------------------------------------------------------------
    */

    public function edit($id_empresa)
    {
        $this->autorizarSuperAdmin();

        $empresa =
            Empresa::findOrFail(
                $id_empresa
            );

        return view(
            'empresas.edit',
            compact('empresa')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR EMPRESA
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id_empresa
    ) {
        $this->autorizarSuperAdmin();

        $empresa =
            Empresa::findOrFail(
                $id_empresa
            );

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
        ]);

        $nombre =
            trim($datos['nombre']);

        $nombreDuplicado =
            Empresa::query()
                ->where(
                    'id_empresa',
                    '!=',
                    $empresa->id_empresa
                )
                ->whereRaw(
                    'LOWER(nombre) = LOWER(?)',
                    [$nombre]
                )
                ->exists();

        if ($nombreDuplicado) {
            throw ValidationException::withMessages([
                'nombre' =>
                    'Ya existe una empresa con este nombre.',
            ]);
        }

        $empresa->nombre =
            $nombre;

        $empresa->slug =
            $this->generarSlugUnico(
                $nombre,
                (int) $empresa->id_empresa
            );

        $empresa->save();

        return redirect()
            ->route('empresas.index')
            ->with(
                'success',
                'Empresa actualizada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESACTIVAR EMPRESA
    |--------------------------------------------------------------------------
    |
    | La nueva tabla empresas no tiene deleted_at y además es referenciada por
    | usuarios, clientes, campañas y citas. Por eso evitamos borrar físicamente
    | el tenant y lo desactivamos.
    |--------------------------------------------------------------------------
    */

    public function destroy($id_empresa)
    {
        $this->autorizarSuperAdmin();

        $empresa =
            Empresa::findOrFail(
                $id_empresa
            );

        DB::transaction(
            function () use ($empresa) {
                $empresa->activo = false;
                $empresa->save();

                /*
                |--------------------------------------------------------------------------
                | También impedimos acceso a los usuarios del tenant.
                |--------------------------------------------------------------------------
                */

                User::query()
                    ->where(
                        'id_empresa',
                        $empresa->id_empresa
                    )
                    ->update([
                        'activo' => false,
                    ]);
            }
        );

        return redirect()
            ->route('empresas.index')
            ->with(
                'success',
                'Empresa desactivada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDITAR ADMIN CLIENTE
    |--------------------------------------------------------------------------
    */

    public function editAdmin($id_empresa)
    {
        $this->autorizarSuperAdmin();

        $empresa =
            Empresa::findOrFail(
                $id_empresa
            );

        $admin = User::query()
            ->where(
                'id_empresa',
                $id_empresa
            )
            ->where(
                'id_rol',
                2
            )
            ->firstOrFail();

        return view(
            'empresas.edit_admin',
            compact(
                'empresa',
                'admin'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR ADMIN CLIENTE
    |--------------------------------------------------------------------------
    */

    public function updateAdmin(
        Request $request,
        $id_empresa
    ) {
        $this->autorizarSuperAdmin();

        $empresa =
            Empresa::findOrFail(
                $id_empresa
            );

        $admin = User::query()
            ->where(
                'id_empresa',
                $empresa->id_empresa
            )
            ->where(
                'id_rol',
                2
            )
            ->firstOrFail();

        $datos = $request->validate(
            [
                'nombre_admin' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'correo_admin' => [
                    'required',
                    'email',
                    'max:150',
                ],
            ],
            [
                'nombre_admin.required' =>
                    'El nombre del administrador es obligatorio.',

                'correo_admin.required' =>
                    'El correo del administrador es obligatorio.',

                'correo_admin.email' =>
                    'Ingresa un correo electrónico válido.',
            ]
        );

        $correo =
            mb_strtolower(
                trim($datos['correo_admin']),
                'UTF-8'
            );

        $this->validarCorreoDisponible(
            $correo,
            (int) $admin->id_usuario
        );

        $admin->name =
            trim($datos['nombre_admin']);

        $admin->correo =
            $correo;

        $admin->save();

        return redirect()
            ->route('empresas.index')
            ->with(
                'success',
                'Administrador actualizado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MÉTODOS INTERNOS
    |--------------------------------------------------------------------------
    */

    private function autorizarSuperAdmin(): void
    {
        $usuario = Auth::user();

        if (
            !$usuario ||
            (int) $usuario->id_rol !== 1
        ) {
            abort(403);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR CORREO ÚNICO
    |--------------------------------------------------------------------------
    |
    | PostgreSQL tiene índice único sobre LOWER(correo) para usuarios no
    | eliminados, así que hacemos la misma comparación aquí.
    |--------------------------------------------------------------------------
    */

    private function validarCorreoDisponible(
        string $correo,
        ?int $ignorarUsuario = null
    ): void {
        $consulta =
            User::query()
                ->whereRaw(
                    'LOWER(correo) = LOWER(?)',
                    [$correo]
                );

        if ($ignorarUsuario !== null) {
            $consulta->where(
                'id_usuario',
                '!=',
                $ignorarUsuario
            );
        }

        if ($consulta->exists()) {
            throw ValidationException::withMessages([
                'correo_admin' =>
                    'Ya existe un usuario registrado con este correo.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GENERAR SLUG ÚNICO
    |--------------------------------------------------------------------------
    */

    private function generarSlugUnico(
        string $nombre,
        ?int $ignorarEmpresa = null
    ): string {
        $base =
            Str::slug($nombre);

        if ($base === '') {
            $base = 'empresa';
        }

        /*
        |--------------------------------------------------------------------------
        | El esquema limita slug a 160 caracteres.
        |--------------------------------------------------------------------------
        */

        $base =
            mb_substr(
                $base,
                0,
                145,
                'UTF-8'
            );

        $slug = $base;
        $contador = 2;

        while (true) {
            $consulta =
                Empresa::query()
                    ->where(
                        'slug',
                        $slug
                    );

            if ($ignorarEmpresa !== null) {
                $consulta->where(
                    'id_empresa',
                    '!=',
                    $ignorarEmpresa
                );
            }

            if (!$consulta->exists()) {
                return $slug;
            }

            $slug =
                $base .
                '-' .
                $contador;

            $contador++;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CONTRASEÑA TEMPORAL
    |--------------------------------------------------------------------------
    |
    | Incluye mayúscula, minúscula, número y símbolo para cumplir las mismas
    | reglas utilizadas en el alta de operarios.
    |--------------------------------------------------------------------------
    */

    private function generarPasswordTemporal(): string
    {
        $mayusculas =
            'ABCDEFGHJKLMNPQRSTUVWXYZ';

        $minusculas =
            'abcdefghijkmnopqrstuvwxyz';

        $numeros =
            '23456789';

        $simbolos =
            '!@#$%&*?';

        $password =
            $mayusculas[
                random_int(
                    0,
                    strlen($mayusculas) - 1
                )
            ]
            .
            $minusculas[
                random_int(
                    0,
                    strlen($minusculas) - 1
                )
            ]
            .
            $numeros[
                random_int(
                    0,
                    strlen($numeros) - 1
                )
            ]
            .
            $simbolos[
                random_int(
                    0,
                    strlen($simbolos) - 1
                )
            ];

        $todos =
            $mayusculas .
            $minusculas .
            $numeros .
            $simbolos;

        while (strlen($password) < 10) {
            $password .=
                $todos[
                    random_int(
                        0,
                        strlen($todos) - 1
                    )
                ];
        }

        return str_shuffle($password);
    }
}
