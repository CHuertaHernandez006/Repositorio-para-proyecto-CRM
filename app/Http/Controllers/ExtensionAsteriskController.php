<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExtensionAsteriskController extends Controller
{
    /**
     * Super Admin (rol 1): todas las empresas.
     * Admin Cliente (rol 2): exclusivamente su propia empresa.
     */
    private function empresaAutorizada(Request $request): int
    {
        $actor = $request->user();
        abort_unless($actor && in_array((int) $actor->id_rol, [1, 2], true), 403);

        $empresa = (int) $request->input('id_empresa', $actor->id_empresa);

        if ((int) $actor->id_rol === 2) {
            abort_unless($empresa === (int) $actor->id_empresa, 403);
        }

        abort_unless(DB::table('empresas')->where('id_empresa', $empresa)->exists(), 404);

        return $empresa;
    }

    public function index(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor && in_array((int) $actor->id_rol, [1, 2], true), 403);

        $empresas = (int) $actor->id_rol === 1
            ? DB::table('empresas')->orderBy('nombre')->get(['id_empresa', 'nombre'])
            : DB::table('empresas')->where('id_empresa', $actor->id_empresa)
                ->get(['id_empresa', 'nombre']);

        $empresaDefault = $empresas->firstWhere('id_empresa', 55) ?? $empresas->first();
        $idEmpresa = (int) $request->input(
            'id_empresa',
            (int) $actor->id_rol === 1 ? ($empresaDefault->id_empresa ?? 0) : $actor->id_empresa
        );

        // El selector en el GET también respeta la autorización de rol.
        abort_unless($empresas->contains('id_empresa', $idEmpresa), 403);

        $operarios = DB::table('usuarios')
            ->where('id_empresa', $idEmpresa)
            ->where('id_rol', 3)
            ->whereNull('deleted_at')
            ->orderBy('nombre')->orderBy('apellido_paterno')
            ->get(['id_usuario', 'nombre', 'apellido_paterno', 'extension_asterisk', 'activo']);

        $inventario = DB::table('extensiones_asterisk as e')
            ->leftJoin('usuarios as u', 'u.extension_asterisk', '=', 'e.extension')
            ->where('e.id_empresa', $idEmpresa)
            ->orderBy('e.extension')
            ->get([
                'e.extension', 'e.activa', 'u.id_usuario as asignado_a',
                'u.nombre as asignado_nombre', 'u.apellido_paterno as asignado_apellido',
            ]);

        return view('operarios.extensiones-sip', compact(
            'empresas', 'idEmpresa', 'operarios', 'inventario'
        ));
    }

    public function registrar(Request $request)
    {
        $idEmpresa = $this->empresaAutorizada($request);
        $datos = $request->validate(['extensiones' => ['required', 'string', 'max:5000']]);

        // Admite números separados por saltos de línea, espacios, comas o ;.
        $codigos = array_values(array_unique(array_filter(
            preg_split('/[\s,;]+/u', trim($datos['extensiones']))
        )));

        if (count($codigos) < 1 || count($codigos) > 100) {
            throw ValidationException::withMessages([
                'extensiones' => 'Registra entre 1 y 100 extensiones por operación.',
            ]);
        }

        foreach ($codigos as $codigo) {
            if (!preg_match('/^[0-9]{3,6}$/D', $codigo)) {
                throw ValidationException::withMessages([
                    'extensiones' => "La extensión {$codigo} no es válida (3 a 6 dígitos).",
                ]);
            }
        }

        $repetidos = DB::table('extensiones_asterisk')
            ->whereIn('extension', $codigos)->pluck('extension')->all();
        if ($repetidos) {
            throw ValidationException::withMessages([
                'extensiones' => 'Ya están registradas: ' . implode(', ', $repetidos) . '.',
            ]);
        }

        $fecha = now();
        $filas = array_map(fn ($codigo) => [
            'id_empresa' => $idEmpresa,
            'extension' => $codigo,
            'activa' => true,
            'created_at' => $fecha,
            'updated_at' => $fecha,
        ], $codigos);

        try {
            DB::table('extensiones_asterisk')->insert($filas);
        } catch (QueryException $e) {
            // Puede existir una inserción concurrente, protegida por UNIQUE.
            throw ValidationException::withMessages([
                'extensiones' => 'Una o más extensiones acaban de registrarse. Actualiza e inténtalo de nuevo.',
            ]);
        }

        return redirect()->route('extensiones-sip.index', ['id_empresa' => $idEmpresa])
            ->with('exito', count($codigos) . ' extensiones registradas en el catálogo CRM.');
    }

    public function asignar(Request $request)
    {
        $idEmpresa = $this->empresaAutorizada($request);
        $datos = $request->validate([
            'id_usuario' => ['required', 'integer'],
            'extension' => ['required', 'regex:/^[0-9]{3,6}$/D'],
        ]);

        try {
            DB::transaction(function () use ($idEmpresa, $datos) {
                // Primero se bloquea el código para evitar asignaciones simultáneas.
                $extension = DB::table('extensiones_asterisk')
                    ->where('id_empresa', $idEmpresa)
                    ->where('extension', $datos['extension'])
                    ->lockForUpdate()->first();

                if (!$extension || !$extension->activa) {
                    throw ValidationException::withMessages([
                        'extension' => 'La extensión no está disponible en esta empresa.',
                    ]);
                }

                $operario = DB::table('usuarios')
                    ->where('id_empresa', $idEmpresa)
                    ->where('id_usuario', $datos['id_usuario'])
                    ->where('id_rol', 3)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()->first();

                if (!$operario) {
                    throw ValidationException::withMessages([
                        'id_usuario' => 'El operario no pertenece a esta empresa.',
                    ]);
                }

                $ocupada = DB::table('usuarios')
                    ->where('extension_asterisk', $datos['extension'])
                    ->where('id_usuario', '<>', $operario->id_usuario)
                    ->exists();

                if ($ocupada) {
                    throw ValidationException::withMessages([
                        'extension' => 'La extensión ya está asignada a otro usuario.',
                    ]);
                }

                DB::table('usuarios')->where('id_usuario', $operario->id_usuario)
                    ->update([
                        'extension_asterisk' => $datos['extension'],
                        'updated_at' => now(),
                    ]);
            }, 3);
        } catch (QueryException $e) {
            throw ValidationException::withMessages([
                'extension' => 'No se pudo completar la asignación. Actualiza el listado e inténtalo de nuevo.',
            ]);
        }

        return redirect()->route('extensiones-sip.index', ['id_empresa' => $idEmpresa])
            ->with('exito', 'Extensión asignada correctamente.');
    }

    public function liberar(Request $request)
    {
        $idEmpresa = $this->empresaAutorizada($request);
        $datos = $request->validate(['id_usuario' => ['required', 'integer']]);

        DB::transaction(function () use ($idEmpresa, $datos) {
            $operario = DB::table('usuarios')
                ->where('id_empresa', $idEmpresa)
                ->where('id_usuario', $datos['id_usuario'])
                ->where('id_rol', 3)
                ->whereNull('deleted_at')
                ->lockForUpdate()->first();

            if (!$operario) {
                throw ValidationException::withMessages([
                    'id_usuario' => 'El operario no pertenece a esta empresa.',
                ]);
            }

            DB::table('usuarios')->where('id_usuario', $operario->id_usuario)
                ->update(['extension_asterisk' => null, 'updated_at' => now()]);
        });

        return redirect()->route('extensiones-sip.index', ['id_empresa' => $idEmpresa])
            ->with('exito', 'La extensión quedó libre para otro operario.');
    }
};
