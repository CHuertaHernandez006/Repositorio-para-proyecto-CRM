<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
        ]);

        $buscar = trim($datos['buscar'] ?? '');

        $consulta = Cliente::query();

        // Super Admin puede ver todos los clientes.
        // Los demás usuarios solamente ven los clientes
        // de su propia empresa.
        if (Auth::user()->id_rol != 1) {
            $consulta->where(
                'id_empresa',
                Auth::user()->id_empresa
            );
        }

        if ($buscar !== '') {
            $termino = '%' . mb_strtolower($buscar, 'UTF-8') . '%';

            $consulta->where(function ($query) use ($termino) {
                $query->whereRaw('LOWER(nombre) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(apellido_paterno) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(apellido_materno) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(empresa) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(correo) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(telefono_principal) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(telefono_secundario) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(pais) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(fuente) LIKE ?', [$termino]);
            });
        }

        $clientes = $consulta
            ->orderByDesc('id_cliente')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'buscar'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'empresa' => 'nullable|string|max:150',
           'telefono_principal' => [
            'required',
            'string',
            'regex:/^\+[1-9]\d{7,14}$/',
                ],

        'telefono_secundario' => [
            'nullable',
            'string',
            'regex:/^\+[1-9]\d{7,14}$/',
                ],

        'correo' => [
            'nullable',
            'email:rfc,dns',
            'max:150',
                ],
            'pais' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'fuente' => 'nullable|string|max:100',
            'id_tipo_cliente' => 'required|integer',
            'id_estado_lead' => 'required|integer',
        ]);

        // La empresa se asigna automáticamente
        // según el usuario que está creando el cliente.
        $datos['id_empresa'] = Auth::user()->id_empresa;

        Cliente::create($datos);

        return redirect()
            ->route('clientes.index')
            ->with('exito', 'Cliente registrado correctamente.');
    }

    public function edit($id_cliente)
    {
        $consulta = Cliente::query();

        // Solo puede editar clientes de su propia empresa.
        if (Auth::user()->id_rol != 1) {
            $consulta->where(
                'id_empresa',
                Auth::user()->id_empresa
            );
        }

        $cliente = $consulta->findOrFail($id_cliente);

        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $id_cliente)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'empresa' => 'nullable|string|max:150',
            'telefono_principal' => 'required|string|max:20',
            'telefono_secundario' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:150',
            'pais' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'fuente' => 'nullable|string|max:100',
            'id_tipo_cliente' => 'required|integer',
            'id_estado_lead' => 'required|integer',
        ]);

        $consulta = Cliente::query();

        // Solo puede actualizar clientes de su propia empresa.
        if (Auth::user()->id_rol != 1) {
            $consulta->where(
                'id_empresa',
                Auth::user()->id_empresa
            );
        }

        $cliente = $consulta->findOrFail($id_cliente);

        $cliente->update($datos);

        return redirect()
            ->route('clientes.index')
            ->with('exito', 'Cliente actualizado correctamente.');
    }

    public function destroy($id_cliente)
    {
        $consulta = Cliente::query();

        // Solo puede eliminar clientes de su propia empresa.
        if (Auth::user()->id_rol != 1) {
            $consulta->where(
                'id_empresa',
                Auth::user()->id_empresa
            );
        }

        $cliente = $consulta->findOrFail($id_cliente);

        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('exito', 'Cliente eliminado correctamente.');
    }
}