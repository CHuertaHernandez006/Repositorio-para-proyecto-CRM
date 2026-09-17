<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
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
        // Filtro de aislamiento Multi-Tenant
        // Si no es Súper Admin (Rol 1), solo puede ver clientes de su empresa
        if (Auth::user()->id_rol != 1) {
            $consulta->where('id_empresa', Auth::user()->id_empresa);
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
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'telefono_principal' => 'required|string|max:20',
            'correo' => 'nullable|email|max:150',
            'telefono_secundario' => 'nullable|string|max:20',
            'pais' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'fuente' => 'nullable|string|max:100',
        ]);

        $cliente = new Cliente();

        // Asignación automática por seguridad
        $cliente->id_empresa = Auth::user()->id_empresa;

        $cliente->nombre = $request->nombre;
        $cliente->apellido_paterno = $request->apellido_paterno;
        $cliente->apellido_materno = $request->apellido_materno;
        $cliente->empresa = $request->empresa;
        $cliente->telefono_principal = $request->telefono_principal;
        $cliente->telefono_secundario = $request->telefono_secundario;
        $cliente->correo = $request->correo;
        $cliente->pais = $request->pais;
        $cliente->estado = $request->estado;
        $cliente->ciudad = $request->ciudad;
        $cliente->fuente = $request->fuente;
        
        // Mantenemos el valor por defecto si no se envían los selectores
        $cliente->id_tipo_cliente = $request->id_tipo_cliente ?? 1;
        $cliente->id_estado_lead = $request->id_estado_lead ?? 1;

        $cliente->save();

        return redirect()->route('clientes.index');
    }

    public function edit($id_cliente)
    {
        $cliente = Cliente::findOrFail($id_cliente);
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $id_cliente)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'telefono_principal' => 'required|string|max:20',
            'correo' => 'nullable|email|max:150',
            'telefono_secundario' => 'nullable|string|max:20',
            'pais' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'fuente' => 'nullable|string|max:100',
            'id_estado_lead' => 'required|integer',
            'id_tipo_cliente' => 'required|integer',
        ]);

        $cliente = Cliente::findOrFail($id_cliente);

        $cliente->nombre = $request->nombre;
        $cliente->apellido_paterno = $request->apellido_paterno;
        $cliente->apellido_materno = $request->apellido_materno;
        $cliente->empresa = $request->empresa;
        $cliente->telefono_principal = $request->telefono_principal;
        $cliente->telefono_secundario = $request->telefono_secundario;
        $cliente->correo = $request->correo;
        $cliente->pais = $request->pais;
        $cliente->estado = $request->estado;
        $cliente->ciudad = $request->ciudad;
        $cliente->fuente = $request->fuente;
        $cliente->id_estado_lead = $request->id_estado_lead;
        $cliente->id_tipo_cliente = $request->id_tipo_cliente;

        $cliente->save();

        return redirect()->route('clientes.index');
    }

    public function destroy($id_cliente)
    {
        $cliente = Cliente::findOrFail($id_cliente);
        $cliente->delete();

        return redirect()->route('clientes.index');
    }
}