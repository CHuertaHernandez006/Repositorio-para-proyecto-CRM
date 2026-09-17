<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpresaController extends Controller
{
    public function index()
    {
        // Traemos todas las empresas ordenadas por la más reciente
        $empresas = Empresa::orderByDesc('id_empresa')->paginate(10);
        return view('empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        // 1. Validamos los datos de ambas secciones
        $request->validate([
            'tipo_cliente' => 'required|in:empresa,independiente',
            'nombre_empresa' => 'nullable|required_if:tipo_cliente,empresa|string|max:150',
            'nombre_admin' => 'required|string|max:100',
            'correo_admin' => 'required|email|unique:users,email',
        ]);

        // 2. Lógica para Independiente vs Empresa
        // Si es independiente, usamos su nombre personal como nombre de empresa
        $nombre_final_empresa = $request->tipo_cliente === 'independiente' 
            ? 'Independiente - ' . $request->nombre_admin 
            : $request->nombre_empresa;

        // 3. Generamos una contraseña temporal segura de 8 caracteres
        $password_temporal = Str::random(8);

        // 4. INICIA LA TRANSACCIÓN SEGURA
        DB::transaction(function () use ($request, $nombre_final_empresa, $password_temporal) {
            
            // A) Crear la Empresa
            $empresa = new Empresa();
            $empresa->nombre = $nombre_final_empresa;
            $empresa->slug = Str::slug($nombre_final_empresa);
            $empresa->estado = true;
            $empresa->save();

            // B) Crear el Usuario (Admin Cliente)
            $user = new User();
            $user->name = $request->nombre_admin;
            $user->email = $request->correo_admin;
            $user->password = Hash::make($password_temporal); // Hash seguro
            
            // ¡SEGURIDAD MÁXIMA! Estos datos se asignan en el servidor, no en la vista
            $user->id_rol = 2; // SIEMPRE será Admin Cliente (2)
            $user->id_empresa = $empresa->id_empresa; // Se amarra a la empresa recién creada
            
            $user->save();

            // TODO: Aquí en el futuro agregaremos la línea de código para mandar el correo real
            // Mail::to($user->email)->send(new WelcomeEmail($user->email, $password_temporal));
        });

        // 5. Redirigimos con un mensaje de éxito mostrando la contraseña temporal
        // (Mientras configuramos los correos después, te la muestro en pantalla para que no se pierda)
        return redirect()->route('empresas.index')->with('success', "Empresa y Administrador creados con éxito. Contraseña temporal del cliente: $password_temporal");
    }

    public function edit($id_empresa)
    {
        $empresa = Empresa::findOrFail($id_empresa);
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, $id_empresa)
    {
        $request->validate([
            'nombre' => 'required|string|max:150|unique:empresas,nombre,' . $id_empresa . ',id_empresa',
        ]);

        $empresa = Empresa::findOrFail($id_empresa);
        $empresa->nombre = $request->nombre;
        $empresa->slug = Str::slug($request->nombre);
        $empresa->save();

        return redirect()->route('empresas.index');
    }

    public function destroy($id_empresa)
    {
        $empresa = Empresa::findOrFail($id_empresa);
        $empresa->delete();

        return redirect()->route('empresas.index');
    }
}