<?php

namespace App\Http\Controllers;

use App\Services\Clientes\LectorClientes;
use App\Services\Clientes\PreparadorClientes;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ImportacionClientesController extends Controller
{
    /** @return \App\Models\User */
    private function actor(Request $request)
    {
        /** @var \App\Models\User|null $usuario */
        $usuario = $request->user();
        abort_unless($usuario && (int) $usuario->id_rol === 2 && $usuario->activo && !$usuario->deleted_at
            && (int) $usuario->id_empresa > 0, 403, 'Solo el administrador cliente activo puede importar clientes.');
        abort_unless(DB::table('empresas')->where('id_empresa', $usuario->id_empresa)->where('activo', true)->exists(), 403);
        abort_unless(DB::connection()->getDriverName() === 'pgsql', 503, 'La importación está preparada para PostgreSQL.');
        return $usuario;
    }

    private function propia(Request $request, string $id, bool $bloquear = false)
    {
        $usuario = $this->actor($request);
        $consulta = DB::table('importaciones_clientes')->where('id_importacion', $id)
            ->where('id_empresa', $usuario->id_empresa)->where('id_usuario', $usuario->getKey());
        if ($bloquear) {
            $consulta->lockForUpdate();
        }
        $lote = $consulta->first();
        abort_unless($lote, 404);
        abort_if(now()->greaterThanOrEqualTo(\Carbon\Carbon::parse($lote->vence_at)), 410,
            'La vista previa o el resultado ya expiró. Inicia una nueva importación.');
        return $lote;
    }

    public function index(Request $request)
    {
        $usuario = $this->actor($request);
        DB::table('importaciones_clientes')->where('vence_at', '<=', now())->delete();
        $catalogos = PreparadorClientes::catalogos();
        $excelDisponible = class_exists(Xlsx::class) && class_exists(\ZipArchive::class);
        $recientes = DB::table('importaciones_clientes')->where('id_empresa', $usuario->id_empresa)
            ->where('id_usuario', $usuario->getKey())->where('vence_at', '>', now())
            ->orderByDesc('created_at')->limit(5)->get();
        return view('clientes.importacion.index', compact('catalogos', 'excelDisponible', 'recientes'));
    }

    public function previsualizar(Request $request)
    {
        $usuario = $this->actor($request);
        $datos = $request->validate([
            'archivo' => 'required|file|max:5120',
            'tipo_cliente' => ['required', 'integer', Rule::exists('tipos_cliente', 'id_tipo_cliente')->where('activo', true)],
            'estado_lead' => ['required', 'integer', Rule::exists('estados_lead', 'id_estado_lead')->where('activo', true)],
            'fuente' => ['nullable', 'integer', Rule::exists('fuentes_lead', 'id_fuente')->where('activo', true)],
        ]);
        $archivo = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'xlsx'], true)) {
            throw ValidationException::withMessages(['archivo' => 'Selecciona un archivo CSV o Excel .xlsx.']);
        }
        $lectura = LectorClientes::leer($archivo->getRealPath(), $extension);
        $opciones = ['tipo_cliente' => (int) $datos['tipo_cliente'], 'estado_lead' => (int) $datos['estado_lead'],
            'fuente' => !empty($datos['fuente']) ? (int) $datos['fuente'] : null,
            'hoja' => $lectura['hoja'], 'formato' => $lectura['formato']];
        $resultado = PreparadorClientes::preparar($lectura['filas'], $opciones, (int) $usuario->id_empresa);
        $id = (string) Str::uuid();
        $ahora = now();
        DB::table('importaciones_clientes')->insert([
            'id_importacion' => $id, 'id_empresa' => $usuario->id_empresa, 'id_usuario' => $usuario->getKey(),
            'estado' => 'pendiente', 'nombre_archivo' => mb_substr(basename($archivo->getClientOriginalName()), 0, 180),
            'opciones' => json_encode($opciones, JSON_THROW_ON_ERROR),
            'datos' => json_encode($lectura['filas'], JSON_THROW_ON_ERROR),
            'resultado' => json_encode($resultado, JSON_THROW_ON_ERROR),
            'resumen' => json_encode(PreparadorClientes::resumen($resultado), JSON_THROW_ON_ERROR),
            'vence_at' => $ahora->copy()->addHour(), 'created_at' => $ahora, 'updated_at' => $ahora,
        ]);
        return redirect()->route('clientes.importacion.mostrar', $id);
    }

    public function mostrar(Request $request, string $importacion)
    {
        $lote = $this->propia($request, $importacion);
        $filtro = $request->validate(['estado' => ['nullable', Rule::in(['valido', 'duplicado', 'error', 'importado'])],
            'page' => 'nullable|integer|min:1|max:40']);
        $resultado = json_decode($lote->resultado, true, 512, JSON_THROW_ON_ERROR);
        $resumen = json_decode($lote->resumen, true, 512, JSON_THROW_ON_ERROR);
        $opciones = json_decode($lote->opciones, true, 512, JSON_THROW_ON_ERROR);
        $estado = $filtro['estado'] ?? '';
        if ($estado !== '') {
            $resultado = array_values(array_filter($resultado, fn ($fila) => $fila['estado'] === $estado));
        }
        $nombresCatalogo = PreparadorClientes::nombresCatalogo();
        $pagina = (int) ($filtro['page'] ?? 1);
        $filas = new LengthAwarePaginator(array_slice($resultado, ($pagina - 1) * 50, 50), count($resultado), 50, $pagina,
            ['path' => route('clientes.importacion.mostrar', $importacion), 'query' => $request->only('estado')]);
        return view('clientes.importacion.mostrar', compact('lote', 'resumen', 'opciones', 'filas', 'estado', 'nombresCatalogo'));
    }

    public function confirmar(Request $request, string $importacion)
    {
        $usuario = $this->actor($request);
        try {
            DB::transaction(function () use ($request, $importacion, $usuario) {
                $lote = $this->propia($request, $importacion, true);
                if ($lote->estado === 'completada') {
                    return; // El mismo lote jamás vuelve a insertar clientes.
                }
                abort_unless($lote->estado === 'pendiente', 422, 'Esta importación ya no se puede confirmar.');
                // Serializa las importaciones de la misma empresa, sin bloquear las de otro tenant.
                DB::select('SELECT pg_advisory_xact_lock(?, ?)', [29310, (int) $usuario->id_empresa]);
                $filas = json_decode($lote->datos, true, 512, JSON_THROW_ON_ERROR);
                $opciones = json_decode($lote->opciones, true, 512, JSON_THROW_ON_ERROR);
                // Revalidar catálogos y duplicados: pudieron cambiar desde la vista previa.
                $resultado = PreparadorClientes::preparar($filas, $opciones, (int) $usuario->id_empresa);
                foreach ($resultado as &$fila) {
                    if ($fila['estado'] !== 'valido') {
                        continue;
                    }
                    $registro = array_merge($fila['datos'], [
                        'id_empresa' => (int) $usuario->id_empresa,
                        'id_usuario_responsable' => null, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    // PostgreSQL conserva las restricciones de unicidad aunque otro proceso escriba simultáneamente.
                    $insertado = DB::table('clientes')->insertOrIgnore($registro);
                    if (!$insertado && !PreparadorClientes::hayDuplicado($registro, (int) $usuario->id_empresa)) {
                        throw ValidationException::withMessages(['importacion' => 'Hubo un conflicto de unicidad interna al guardar. Se revirtió todo este intento. Soporte debe revisar las secuencias e índices antes de reintentar.']);
                    }
                    $fila['estado'] = $insertado ? 'importado' : 'duplicado';
                    $fila['mensajes'] = $insertado ? ['Cliente creado sin operario asignado.']
                        : ['Se omitió por una restricción de unicidad al guardar. No se modificó el registro existente.'];
                }
                unset($fila);
                DB::table('importaciones_clientes')->where('id_importacion', $importacion)
                    ->where('id_empresa', $usuario->id_empresa)->where('id_usuario', $usuario->getKey())
                    ->update(['estado' => 'completada', 'datos' => null,
                        'resultado' => json_encode($resultado, JSON_THROW_ON_ERROR),
                        'resumen' => json_encode(PreparadorClientes::resumen($resultado), JSON_THROW_ON_ERROR),
                        'vence_at' => now()->addDay(), 'updated_at' => now()]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);
            throw ValidationException::withMessages(['importacion' => 'No se pudo guardar el lote. La transacción fue revertida y no se importó ningún registro de este intento. Revisa el log con soporte antes de reintentar.']);
        }
        return redirect()->route('clientes.importacion.mostrar', $importacion)
            ->with('exito', 'Importación procesada. Revisa el resumen y las filas omitidas.');
    }

    public function cancelar(Request $request, string $importacion)
    {
        $this->propia($request, $importacion);
        DB::transaction(function () use ($request, $importacion) {
            $lote = $this->propia($request, $importacion, true);
            abort_unless($lote->estado === 'pendiente', 422, 'La importación ya fue procesada.');
            DB::table('importaciones_clientes')->where('id_importacion', $importacion)->delete();
        });
        return redirect()->route('clientes.importacion.index')->with('exito', 'Vista previa descartada.');
    }

    public function reporte(Request $request, string $importacion)
    {
        $lote = $this->propia($request, $importacion);
        $resultado = json_decode($lote->resultado, true, 512, JSON_THROW_ON_ERROR);
        $nombres = PreparadorClientes::nombresCatalogo();
        return response()->streamDownload(function () use ($resultado, $nombres) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['fila', 'resultado', 'detalle', ...LectorClientes::COLUMNAS], ',', '"', '');
            foreach ($resultado as $fila) {
                $datos = $fila['datos'];
                foreach (PreparadorClientes::CATALOGOS as $campo => $meta) {
                    $datos[$campo] = $nombres[$meta['id']][(int) ($datos[$meta['id']] ?? 0)] ?? '';
                }
                $celdas = [$fila['fila'], $fila['estado'], implode(' | ', $fila['mensajes'])];
                foreach (LectorClientes::COLUMNAS as $campo) {
                    $celdas[] = $datos[$campo] ?? '';
                }
                fputcsv($salida, array_map([self::class, 'celdaSegura'], $celdas), ',', '"', '');
            }
            fclose($salida);
        }, 'resultado-importacion-clientes.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function celdaSegura($valor): string
    {
        $texto = (string) $valor;
        return preg_match('/^[\s]*[=+\-@]|^[\t\r\n]/u', $texto) ? "'".$texto : $texto;
    }

    public function plantilla(Request $request, string $formato)
    {
        $this->actor($request);
        abort_unless(in_array($formato, ['csv', 'xlsx'], true), 404);
        if ($formato === 'csv') {
            return response()->streamDownload(function () {
                $salida = fopen('php://output', 'w');
                fwrite($salida, "\xEF\xBB\xBF");
                fputcsv($salida, LectorClientes::COLUMNAS, ',', '"', '');
                fclose($salida);
            }, 'plantilla-clientes.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }
        abort_unless(class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class), 503, 'Excel no está habilitado en este entorno.');
        return response()->streamDownload(function () {
            $libro = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
            $hoja = $libro->getActiveSheet();
            $hoja->setTitle('Clientes');
            foreach (LectorClientes::COLUMNAS as $i => $campo) {
                $hoja->setCellValueExplicit([$i + 1, 1], $campo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $hoja->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1))->setWidth(24);
            }
            $hoja->getStyle('A:M')->getNumberFormat()->setFormatCode('@');
            $hoja->getStyle('A1:M1')->getFont()->setBold(true);
            $hoja->freezePane('A2');
            try {
                (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro))->save('php://output');
            } finally {
                $libro->disconnectWorksheets();
            }
        }, 'plantilla-clientes.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
