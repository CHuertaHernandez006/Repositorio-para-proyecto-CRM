<?php

namespace App\Services\Clientes;

use Illuminate\Support\Facades\DB;

class HistorialImportaciones
{
    /** Insertar con RETURNING permite conocer el ID real sin adivinarlo por teléfono o secuencia. */
    public static function insertarCliente(array $registro): ?int
    {
        $conexion = DB::connection();
        $columnas = $conexion->getQueryGrammar()->columnize(array_keys($registro));
        $parametros = implode(', ', array_fill(0, count($registro), '?'));
        $filas = $conexion->select('INSERT INTO "clientes" ('.$columnas.') VALUES ('.$parametros.') '
            .'ON CONFLICT DO NOTHING RETURNING "id_cliente"', array_values($registro));
        return isset($filas[0]) ? (int) $filas[0]->id_cliente : null;
    }

    /** Se ejecuta dentro de la MISMA transacción que creó los clientes. Sin FK al lote temporal. */
    public static function guardar($lote, array $resultado, array $miembros): void
    {
        $resumen = PreparadorClientes::resumen($resultado);
        $ahora = now();
        DB::table('lotes_importacion_clientes')->insert([
            'id_lote' => $lote->id_importacion, 'id_empresa' => $lote->id_empresa,
            'id_usuario' => $lote->id_usuario, 'nombre_archivo' => $lote->nombre_archivo,
            'filas_leidas' => $resumen['total'], 'importados' => $resumen['importado'],
            'duplicados' => $resumen['duplicado'], 'errores' => $resumen['error'],
            'created_at' => $ahora, 'updated_at' => $ahora,
        ]);
        foreach (array_chunk($miembros, 200) as $grupo) {
            DB::table('lote_importacion_clientes')->insert(array_map(fn ($fila) => [
                'id_lote' => $lote->id_importacion, 'id_empresa' => $lote->id_empresa,
                'id_cliente' => $fila['id_cliente'], 'fila_archivo' => $fila['fila'],
                'excepcion' => false, 'created_at' => $ahora, 'updated_at' => $ahora,
            ], $grupo));
        }
    }
}
