<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
        ]);

        $buscar = trim($datos['buscar'] ?? '');
        $consulta = Cliente::query();

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

        Cliente::create($datos);

        return redirect()->route('clientes.index')->with('exito', 'Cliente registrado correctamente.');
    }

    public function edit($id_cliente)
    {
        $cliente = Cliente::findOrFail($id_cliente);
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

        $cliente = Cliente::findOrFail($id_cliente);
        $cliente->update($datos);

        return redirect()->route('clientes.index')->with('exito', 'Cliente actualizado correctamente.');
    }

    public function destroy($id_cliente)
    {
        $cliente = Cliente::findOrFail($id_cliente);
        $cliente->delete();

        return redirect()->route('clientes.index')->with('exito', 'Cliente eliminado correctamente.');
    }
}