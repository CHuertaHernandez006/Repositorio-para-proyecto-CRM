<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OperarioController extends Controller
{
    /**
     * Mostrar la lista de operarios.
     */
    public function index()
    {
        $usuarioActual = auth()->user();

        // Solo Super Admin y Administrador Cliente
        if (!in_array((int) $usuarioActual->id_rol, [1, 2], true)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Puede consultar todos los operarios.
        |
        */

        if ((int) $usuarioActual->id_rol === 1) {

            $operarios = User::where('id_rol', 3)
                ->with('empresa')
                ->orderBy('name')
                ->get();

        }

        /*
        |--------------------------------------------------------------------------
        | Administrador Cliente
        |--------------------------------------------------------------------------
        |
        | Solo puede consultar los operarios pertenecientes
        | a su propia empresa.
        |
        */

        else {

            $operarios = User::where('id_rol', 3)
                ->where('id_empresa', $usuarioActual->id_empresa)
                ->with('empresa')
                ->orderBy('name')
                ->get();
        }

        return view('operarios.index', compact('operarios'));
    }


    /**
     * Mostrar formulario para crear un operario.
     */
    public function create()
    {
        $usuarioActual = auth()->user();

        // Solo Super Admin y Administrador Cliente
        if (!in_array((int) $usuarioActual->id_rol, [1, 2], true)) {
            abort(403, 'No tienes permiso para crear operarios.');
        }

        return view('operarios.create');
    }


    /**
     * Guardar un nuevo operario.
     */
    public function store(Request $request)
    {
        $usuarioActual = auth()->user();

        // Solo Super Admin y Administrador Cliente
        if (!in_array((int) $usuarioActual->id_rol, [1, 2], true)) {
            abort(403, 'No tienes permiso para crear operarios.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */
$datos = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:users,email'],
    'password' => [
        'required',
        'string',
        'min:8',
        'confirmed',
        'regex:/[A-Z]/',
        'regex:/[a-z]/',
        'regex:/[0-9]/',
        'regex:/[^A-Za-z0-9]/',
        'not_regex:/\s/',
    ],
    'id_empresa' => ['nullable', 'integer', 'exists:empresas,id_empresa'],
]);
        $datos = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Empresa
        |--------------------------------------------------------------------------
        |
        | El Administrador Cliente NO puede elegir libremente una empresa.
        | El operario se asigna automáticamente a la empresa del administrador.
        |
        */

        if ((int) $usuarioActual->id_rol === 2) {

            if (!$usuarioActual->id_empresa) {
                abort(
                    403,
                    'El administrador no tiene una empresa asignada.'
                );
            }

            $idEmpresa = $usuarioActual->id_empresa;

        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Si posteriormente queremos que el Super Admin pueda seleccionar
        | una empresa desde el formulario, aquí podremos modificarlo.
        |
        | Por ahora utilizamos el id_empresa enviado por el formulario.
        |
        */

        else {

            $request->validate([
                'id_empresa' => [
                    'required',
                    'integer',
                    'exists:empresas,id_empresa',
                ],
            ]);

            $idEmpresa = $request->id_empresa;
        }


        /*
        |--------------------------------------------------------------------------
        | Crear operario
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],

            // 3 = Operario
            'id_rol' => 3,

            // Empresa correspondiente
            'id_empresa' => $idEmpresa,
        ]);


        return redirect()
            ->route('operarios.index')
            ->with('success', 'Operario creado correctamente.');
    }


    /**
     * Mostrar información de un operario.
     */
    public function show(User $operario)
    {
        $usuarioActual = auth()->user();

        // Solo Super Admin y Administrador Cliente
        if (!in_array((int) $usuarioActual->id_rol, [1, 2], true)) {
            abort(403, 'No tienes permiso para consultar operarios.');
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar que el operario realmente sea un operario
        |--------------------------------------------------------------------------
        */

        if ((int) $operario->id_rol !== 3) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Administrador Cliente
        |--------------------------------------------------------------------------
        |
        | No puede consultar operarios de otra empresa.
        |
        */

        if (
            (int) $usuarioActual->id_rol === 2
            && $operario->id_empresa !== $usuarioActual->id_empresa
        ) {
            abort(403, 'No puedes consultar un operario de otra empresa.');
        }


        $operario->load('empresa');

        return view(
            'operarios.show',
            compact('operario')
        );
    }


    /**
     * Mostrar formulario para editar un operario.
     */
    public function edit(User $operario)
    {
        $usuarioActual = auth()->user();

        // Solo Super Admin y Administrador Cliente
        if (!in_array((int) $usuarioActual->id_rol, [1, 2], true)) {
            abort(403, 'No tienes permiso para editar operarios.');
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar que sea operario
        |--------------------------------------------------------------------------
        */

        if ((int) $operario->id_rol !== 3) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Restricción por empresa
        |--------------------------------------------------------------------------
        */

        if (
            (int) $usuarioActual->id_rol === 2
            && $operario->id_empresa !== $usuarioActual->id_empresa
        ) {
            abort(403, 'No puedes editar un operario de otra empresa.');
        }


        return view(
            'operarios.edit',
            compact('operario')
        );
    }


    /**
     * Actualizar un operario.
     */
    /**
 * Actualizar un operario.
 */
public function update(Request $request, User $operario)
{
    $usuarioActual = auth()->user();

    // Solo Super Admin y Administrador Cliente
    if (!in_array((int) $usuarioActual->id_rol, [1, 2], true)) {
        abort(403, 'No tienes permiso para actualizar operarios.');
    }


    // ==========================================================
    // Verificar que sea operario
    // ==========================================================

    if ((int) $operario->id_rol !== 3) {
        abort(404);
    }


    // ==========================================================
    // Restricción por empresa
    // ==========================================================

    if (
        (int) $usuarioActual->id_rol === 2
        && $operario->id_empresa !== $usuarioActual->id_empresa
    ) {
        abort(403, 'No puedes modificar un operario de otra empresa.');
    }


    // ==========================================================
    // Validación de datos generales
    // ==========================================================

    $datos = $request->validate([

        'name' => [
            'required',
            'string',
            'max:255',
        ],

        'email' => [
            'required',
            'email',
            'max:255',
            'unique:users,email,' . $operario->id,
        ],

        // Contraseña actual:
        // solo es obligatoria si se quiere cambiar la contraseña.
        'current_password' => [
            'nullable',
            'string',
        ],

        // Nueva contraseña
        'password' => [
            'nullable',
            'string',
            'min:8',
            'confirmed',
            'regex:/[A-Z]/',
            'regex:/[a-z]/',
            'regex:/[0-9]/',
            'regex:/[^A-Za-z0-9]/',
            'not_regex:/\s/',
        ],
    ], [

        'password.min' =>
            'La nueva contraseña debe tener al menos 8 caracteres.',

        'password.regex' =>
            'La nueva contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial.',

        'password.confirmed' =>
            'La confirmación de la nueva contraseña no coincide.',

        'password.not_regex' =>
            'La nueva contraseña no debe contener espacios.',
    ]);


    // ==========================================================
    // Actualizar información básica
    // ==========================================================

    $operario->name = $datos['name'];
    $operario->email = $datos['email'];


    // ==========================================================
    // CAMBIO DE CONTRASEÑA
    // ==========================================================

    if (!empty($datos['password'])) {

        // ------------------------------------------------------
        // La contraseña actual es obligatoria
        // ------------------------------------------------------

        if (empty($datos['current_password'])) {

            return back()
                ->withErrors([
                    'current_password' =>
                        'Debes ingresar la contraseña actual para poder cambiarla.',
                ])
                ->withInput();

        }


        // ------------------------------------------------------
        // Comprobar contraseña actual
        // ------------------------------------------------------

        if (!Hash::check(
            $datos['current_password'],
            $operario->password
        )) {

            return back()
                ->withErrors([
                    'current_password' =>
                        'La contraseña actual es incorrecta.',
                ])
                ->withInput();

        }


        // ------------------------------------------------------
        // Guardar nueva contraseña
        // ------------------------------------------------------

        $operario->password = Hash::make($datos['password']);
    }


    // ==========================================================
    // Asegurar que siga siendo Operario
    // ==========================================================

    $operario->id_rol = 3;


    // ==========================================================
    // El Administrador Cliente no puede cambiar la empresa
    // ==========================================================

    if ((int) $usuarioActual->id_rol === 2) {
        $operario->id_empresa = $usuarioActual->id_empresa;
    }


    // ==========================================================
    // Guardar cambios
    // ==========================================================

    $operario->save();


    return redirect()
        ->route('operarios.index')
        ->with(
            'success',
            'Operario actualizado correctamente.'
        );
}


    /**
     * Activar o desactivar un operario.
     */
    public function toggleEstado(User $operario)
    {
        $usuarioActual = auth()->user();

        // Solo Super Admin y Administrador Cliente
        if (!in_array((int) $usuarioActual->id_rol, [1, 2], true)) {
            abort(403, 'No tienes permiso para cambiar el estado de operarios.');
        }


        /*
        |--------------------------------------------------------------------------
        | Verificar que sea operario
        |--------------------------------------------------------------------------
        */

        if ((int) $operario->id_rol !== 3) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Restricción por empresa
        |--------------------------------------------------------------------------
        */

        if (
            (int) $usuarioActual->id_rol === 2
            && $operario->id_empresa !== $usuarioActual->id_empresa
        ) {
            abort(403, 'No puedes modificar un operario de otra empresa.');
        }


        /*
        |--------------------------------------------------------------------------
        | Cambiar estado
        |--------------------------------------------------------------------------
        |
        | En tu tabla users utilizamos el campo "estado" si existe.
        |
        */

        $operario->estado = !$operario->estado;

        $operario->save();


        return redirect()
            ->route('operarios.index')
            ->with(
                'success',
                $operario->estado
                    ? 'Operario activado correctamente.'
                    : 'Operario desactivado correctamente.'
            );
    }


    /**
     * Eliminar un operario.
     */
    public function destroy(User $operario)
    {
        $usuarioActual = auth()->user();

        // Solo Super Admin y Administrador Cliente
        if (!in_array((int) $usuarioActual->id_rol, [1, 2], true)) {
            abort(403, 'No tienes permiso para eliminar operarios.');
        }


        /*
        |--------------------------------------------------------------------------
        | Verificar que sea operario
        |--------------------------------------------------------------------------
        */

        if ((int) $operario->id_rol !== 3) {
            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Restricción por empresa
        |--------------------------------------------------------------------------
        */

        if (
            (int) $usuarioActual->id_rol === 2
            && $operario->id_empresa !== $usuarioActual->id_empresa
        ) {
            abort(403, 'No puedes eliminar un operario de otra empresa.');
        }


        $operario->delete();


        return redirect()
            ->route('operarios.index')
            ->with('success', 'Operario eliminado correctamente.');
    }
}