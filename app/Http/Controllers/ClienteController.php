<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO DE CLIENTES
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
        ]);

        $buscar = trim($datos['buscar'] ?? '');

        $consulta = Cliente::query();

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR EMPRESA
        |--------------------------------------------------------------------------
        |
        | Super Admin puede ver todos.
        | Los demás solamente los clientes de su empresa.
        |
        */

        if ((int) Auth::user()->id_rol !== 1) {
            $consulta->where(
                'id_empresa',
                Auth::user()->id_empresa
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA
        |--------------------------------------------------------------------------
        */

        if ($buscar !== '') {
            $termino =
                '%' .
                mb_strtolower($buscar, 'UTF-8') .
                '%';

            $consulta->where(
                function ($query) use ($termino) {

                    $query
                        ->whereRaw(
                            'LOWER(nombre) LIKE ?',
                            [$termino]
                        )

                        ->orWhereRaw(
                            'LOWER(apellido_paterno) LIKE ?',
                            [$termino]
                        )

                        ->orWhereRaw(
                            'LOWER(COALESCE(apellido_materno, \'\')) LIKE ?',
                            [$termino]
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Antes: empresa
                        | Ahora: organizacion
                        |--------------------------------------------------------------------------
                        */

                        ->orWhereRaw(
                            'LOWER(COALESCE(organizacion, \'\')) LIKE ?',
                            [$termino]
                        )

                        ->orWhereRaw(
                            'LOWER(COALESCE(correo, \'\')) LIKE ?',
                            [$termino]
                        )

                        ->orWhereRaw(
                            'LOWER(telefono_principal) LIKE ?',
                            [$termino]
                        )

                        ->orWhereRaw(
                            'LOWER(COALESCE(telefono_secundario, \'\')) LIKE ?',
                            [$termino]
                        )

                        ->orWhereRaw(
                            'LOWER(COALESCE(pais, \'\')) LIKE ?',
                            [$termino]
                        )

                        ->orWhereRaw(
                            'LOWER(COALESCE(estado_region, \'\')) LIKE ?',
                            [$termino]
                        )

                        ->orWhereRaw(
                            'LOWER(COALESCE(ciudad, \'\')) LIKE ?',
                            [$termino]
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | FUENTE
                        |--------------------------------------------------------------------------
                        |
                        | Antes estaba guardada como texto en clientes.fuente.
                        |
                        | Ahora clientes guarda id_fuente y el nombre está en
                        | fuentes_lead.
                        |
                        */

                        ->orWhereExists(
                            function ($subconsulta) use ($termino) {

                                $subconsulta
                                    ->selectRaw('1')
                                    ->from('fuentes_lead')
                                    ->whereColumn(
                                        'fuentes_lead.id_fuente',
                                        'clientes.id_fuente'
                                    )
                                    ->whereRaw(
                                        'LOWER(fuentes_lead.nombre) LIKE ?',
                                        [$termino]
                                    );
                            }
                        );
                }
            );
        }

        $clientes = $consulta
            ->orderByDesc('id_cliente')
            ->paginate(10)
            ->withQueryString();

        return view(
            'clientes.index',
            compact(
                'clientes',
                'buscar'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULARIO DE ALTA
    |--------------------------------------------------------------------------
    */

    public function create()
{
    $tiposCliente = DB::table('tipos_cliente')
        ->where('activo', true)
        ->orderBy('nombre')
        ->get();

    $estadosLead = DB::table('estados_lead')
        ->where('activo', true)
        ->orderBy('id_estado_lead')
        ->get();

    $fuentes = DB::table('fuentes_lead')
        ->where('activo', true)
        ->orderBy('nombre')
        ->get();

    return view(
        'clientes.create',
        compact(
            'tiposCliente',
            'estadosLead',
            'fuentes'
        )
    );
}


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR CLIENTE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $usuario = Auth::user();

        $idEmpresa = (int) $usuario->id_empresa;

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_paterno' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_materno' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | El formulario todavía se llama "empresa".
            |
            | En PostgreSQL se guardará como "organizacion".
            |--------------------------------------------------------------------------
            */

            'empresa' => [
                'nullable',
                'string',
                'max:150',
            ],

            'telefono_principal' => [
                'required',
                'string',

                /*
                |--------------------------------------------------------------------------
                | PostgreSQL permite:
                |
                | 525512345678
                | +525512345678
                |--------------------------------------------------------------------------
                */

                'regex:/^\+?[0-9]{7,15}$/',

                /*
                |--------------------------------------------------------------------------
                | El esquema nuevo no permite repetir el mismo teléfono dentro
                | de una misma empresa.
                |--------------------------------------------------------------------------
                */

                Rule::unique(
                    'clientes',
                    'telefono_principal'
                )
                    ->where(
                        function ($query) use ($idEmpresa) {
                            return $query
                                ->where(
                                    'id_empresa',
                                    $idEmpresa
                                )
                                ->whereNull(
                                    'deleted_at'
                                );
                        }
                    ),
            ],

            'telefono_secundario' => [
                'nullable',
                'string',
                'regex:/^\+?[0-9]{7,15}$/',
            ],

            'correo' => [
                'nullable',
                'email:rfc',
                'max:150',
            ],

            'pais' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | El formulario conserva "estado".
            |
            | PostgreSQL usa estado_region.
            |--------------------------------------------------------------------------
            */

            'estado' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ciudad' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | El formulario todavía puede enviar la fuente como texto.
            |
            | El controller la transformará en id_fuente.
            |--------------------------------------------------------------------------
            */

            'fuente' => [
                'nullable',
                'string',
                'max:100',
            ],

            'id_tipo_cliente' => [
                'required',
                'integer',
                'exists:tipos_cliente,id_tipo_cliente',
            ],

            'id_estado_lead' => [
                'required',
                'integer',
                'exists:estados_lead,id_estado_lead',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CONVERTIR CAMPOS DEL FORMULARIO AL NUEVO ESQUEMA
        |--------------------------------------------------------------------------
        */

        $fuenteTexto = $datos['fuente'] ?? null;

        $datos['id_empresa'] = $idEmpresa;

        $datos['organizacion'] =
            $datos['empresa'] ?? null;

        $datos['estado_region'] =
            $datos['estado'] ?? null;

        $datos['id_fuente'] =
            $this->resolverFuenteId(
                $fuenteTexto
            );


        /*
        |--------------------------------------------------------------------------
        | ELIMINAR NOMBRES DE COLUMNAS ANTIGUAS
        |--------------------------------------------------------------------------
        */

        unset(
            $datos['empresa'],
            $datos['estado'],
            $datos['fuente']
        );


        /*
        |--------------------------------------------------------------------------
        | CREAR CLIENTE
        |--------------------------------------------------------------------------
        */

        Cliente::create($datos);


        return redirect()
            ->route('clientes.index')
            ->with(
                'exito',
                'Cliente registrado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDITAR CLIENTE
    |--------------------------------------------------------------------------
    */

    public function edit($id_cliente)
    {
        $consulta = Cliente::query();

        /*
        |--------------------------------------------------------------------------
        | SEGURIDAD POR EMPRESA
        |--------------------------------------------------------------------------
        */

        if ((int) Auth::user()->id_rol !== 1) {
            $consulta->where(
                'id_empresa',
                Auth::user()->id_empresa
            );
        }

        $cliente = $consulta
            ->findOrFail($id_cliente);


        return view(
            'clientes.edit',
            compact('cliente')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR CLIENTE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id_cliente
    ) {
        $usuario = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | BUSCAR CLIENTE
        |--------------------------------------------------------------------------
        */

        $consulta = Cliente::query();

        if ((int) $usuario->id_rol !== 1) {
            $consulta->where(
                'id_empresa',
                $usuario->id_empresa
            );
        }

        $cliente = $consulta
            ->findOrFail($id_cliente);

        $idEmpresa =
            (int) $cliente->id_empresa;


        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_paterno' => [
                'required',
                'string',
                'max:100',
            ],

            'apellido_materno' => [
                'nullable',
                'string',
                'max:100',
            ],

            'empresa' => [
                'nullable',
                'string',
                'max:150',
            ],

            'telefono_principal' => [
                'required',
                'string',
                'regex:/^\+?[0-9]{7,15}$/',

                Rule::unique(
                    'clientes',
                    'telefono_principal'
                )
                    ->where(
                        function ($query) use ($idEmpresa) {
                            return $query
                                ->where(
                                    'id_empresa',
                                    $idEmpresa
                                )
                                ->whereNull(
                                    'deleted_at'
                                );
                        }
                    )
                    ->ignore(
                        $cliente->id_cliente,
                        'id_cliente'
                    ),
            ],

            'telefono_secundario' => [
                'nullable',
                'string',
                'regex:/^\+?[0-9]{7,15}$/',
            ],

            'correo' => [
                'nullable',
                'email:rfc',
                'max:150',
            ],

            'pais' => [
                'nullable',
                'string',
                'max:100',
            ],

            'estado' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ciudad' => [
                'nullable',
                'string',
                'max:100',
            ],

            'fuente' => [
                'nullable',
                'string',
                'max:100',
            ],

            'id_tipo_cliente' => [
                'required',
                'integer',
                'exists:tipos_cliente,id_tipo_cliente',
            ],

            'id_estado_lead' => [
                'required',
                'integer',
                'exists:estados_lead,id_estado_lead',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSFORMAR CAMPOS
        |--------------------------------------------------------------------------
        */

        $fuenteTexto =
            $datos['fuente'] ?? null;

        $datos['organizacion'] =
            $datos['empresa'] ?? null;

        $datos['estado_region'] =
            $datos['estado'] ?? null;

        $datos['id_fuente'] =
            $this->resolverFuenteId(
                $fuenteTexto
            );


        /*
        |--------------------------------------------------------------------------
        | ELIMINAR COLUMNAS DEL ESQUEMA ANTERIOR
        |--------------------------------------------------------------------------
        */

        unset(
            $datos['empresa'],
            $datos['estado'],
            $datos['fuente']
        );


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR
        |--------------------------------------------------------------------------
        */

        $cliente->update($datos);


        return redirect()
            ->route('clientes.index')
            ->with(
                'exito',
                'Cliente actualizado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR CLIENTE
    |--------------------------------------------------------------------------
    |
    | El nuevo esquema utiliza Soft Deletes.
    |
    */

    public function destroy($id_cliente)
    {
        $consulta = Cliente::query();

        if ((int) Auth::user()->id_rol !== 1) {
            $consulta->where(
                'id_empresa',
                Auth::user()->id_empresa
            );
        }

        $cliente = $consulta
            ->findOrFail($id_cliente);

        $cliente->delete();


        return redirect()
            ->route('clientes.index')
            ->with(
                'exito',
                'Cliente eliminado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVER FUENTE
    |--------------------------------------------------------------------------
    |
    | El formulario viejo maneja una fuente como texto.
    |
    | Ejemplos:
    |
    | Comi
    | Importación CSV Demo
    | Referencia
    |
    | La nueva BD normaliza esa información:
    |
    | clientes.id_fuente
    |        ↓
    | fuentes_lead.id_fuente
    |
    | Para no tener que rehacer inmediatamente el formulario:
    |
    | 1. Busca la fuente ignorando mayúsculas/minúsculas.
    | 2. Si existe, utiliza su ID.
    | 3. Si no existe, la registra en el catálogo.
    |--------------------------------------------------------------------------
    */

    private function resolverFuenteId(
        ?string $fuente
    ): ?int {
        $fuente = trim(
            (string) $fuente
        );

        if ($fuente === '') {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCAR EXISTENTE
        |--------------------------------------------------------------------------
        */

        $idFuente = DB::table(
            'fuentes_lead'
        )
            ->whereRaw(
                'LOWER(nombre) = LOWER(?)',
                [$fuente]
            )
            ->value(
                'id_fuente'
            );


        if ($idFuente !== null) {
            return (int) $idFuente;
        }


        /*
        |--------------------------------------------------------------------------
        | CREAR NUEVA FUENTE
        |--------------------------------------------------------------------------
        */

        return (int) DB::table(
            'fuentes_lead'
        )
            ->insertGetId(
                [
                    'nombre' =>
                        $fuente,

                    'descripcion' =>
                        'Fuente registrada desde el módulo de clientes.',

                    'activo' =>
                        true,
                ],
                'id_fuente'
            );
    }
}