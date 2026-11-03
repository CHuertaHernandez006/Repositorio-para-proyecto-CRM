<?php

namespace App\Services\Clientes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PreparadorClientes
{
    public const CATALOGOS = [
        'tipo_cliente' => ['tabla' => 'tipos_cliente', 'id' => 'id_tipo_cliente'],
        'estado_lead' => ['tabla' => 'estados_lead', 'id' => 'id_estado_lead'],
        'fuente' => ['tabla' => 'fuentes_lead', 'id' => 'id_fuente'],
    ];

    public static function catalogos(): array
    {
        $salida = [];
        foreach (self::CATALOGOS as $campo => $meta) {
            $salida[$campo] = DB::table($meta['tabla'])->where('activo', true)->orderBy('nombre')
                ->get([$meta['id'], 'nombre'])->all();
        }
        return $salida;
    }

    public static function nombresCatalogo(): array
    {
        $nombres = [];
        foreach (self::CATALOGOS as $meta) {
            foreach (DB::table($meta['tabla'])->get([$meta['id'], 'nombre']) as $opcion) {
                $nombres[$meta['id']][(int) $opcion->{$meta['id']}] = $opcion->nombre;
            }
        }
        return $nombres;
    }

    public static function preparar(array $filas, array $predeterminados, int $empresa): array
    {
        $catalogos = self::catalogos();
        $resultados = [];
        foreach ($filas as $fila) {
            $datos = [];
            foreach (LectorClientes::COLUMNAS as $campo) {
                $valor = trim((string) ($fila['datos'][$campo] ?? ''));
                $datos[$campo] = $valor === '' ? null : $valor;
            }
            foreach (['telefono_principal', 'telefono_secundario'] as $campo) {
                if ($datos[$campo] !== null) {
                    $datos[$campo] = preg_replace('/[\s()\-]+/u', '', $datos[$campo]);
                }
            }
            if ($datos['correo'] !== null) {
                $datos['correo'] = mb_strtolower($datos['correo'], 'UTF-8');
            }
            $errores = $fila['errores_archivo'] ?? [];
            foreach (self::CATALOGOS as $campo => $meta) {
                $texto = $datos[$campo];
                $id = null;
                $coincidencias = [];
                foreach ($catalogos[$campo] as $opcion) {
                    if ($texto !== null && LectorClientes::clave($opcion->nombre) === LectorClientes::clave($texto)) {
                        $coincidencias[] = (int) $opcion->{$meta['id']};
                    }
                    if ($texto === null && (int) ($predeterminados[$campo] ?? 0) === (int) $opcion->{$meta['id']}) {
                        $id = (int) $opcion->{$meta['id']};
                    }
                }
                if ($texto !== null) {
                    if (count($coincidencias) === 1) {
                        $id = $coincidencias[0];
                    } else {
                        $errores[] = 'El valor “'.mb_substr($texto, 0, 100).'” de '.$campo.' no coincide con un nombre único y activo del catálogo.';
                    }
                } elseif ($campo !== 'fuente' && $id === null) {
                    $errores[] = 'Selecciona un valor predeterminado activo para '.$campo.'.';
                } elseif ($campo === 'fuente' && !empty($predeterminados[$campo]) && $id === null) {
                    $errores[] = 'La fuente predeterminada ya no está activa.';
                }
                $datos[$meta['id']] = $id;
                unset($datos[$campo]);
            }
            $validador = Validator::make($datos, [
                'nombre' => 'required|string|max:100',
                'apellido_paterno' => 'required|string|max:100',
                'apellido_materno' => 'nullable|string|max:100',
                'organizacion' => 'nullable|string|max:150',
                'telefono_principal' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
                'telefono_secundario' => ['nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'different:telefono_principal'],
                'correo' => ['nullable', 'email:rfc', 'max:150', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/u'],
                'pais' => 'nullable|string|max:100',
                'estado_region' => 'nullable|string|max:100',
                'ciudad' => 'nullable|string|max:100',
            ], [
                'required' => 'El campo :attribute es obligatorio.',
                'max' => 'El campo :attribute supera :max caracteres.',
                'regex' => 'El formato de :attribute no es válido.',
                'email' => 'El correo no tiene un formato válido.',
                'different' => 'El teléfono secundario debe ser distinto del principal.',
            ]);
            $errores = array_values(array_unique(array_merge($errores, $validador->errors()->all())));
            $resultados[] = ['fila' => (int) $fila['fila'], 'datos' => $datos,
                'estado' => $errores ? 'error' : 'valido', 'mensajes' => $errores];
        }
        $telefonos = [];
        $correos = [];
        foreach ($resultados as $fila) {
            if ($fila['estado'] !== 'valido') {
                continue;
            }
            foreach (self::telefonos($fila['datos']) as $telefono) {
                $telefonos[$telefono] = true;
            }
            if ($fila['datos']['correo']) {
                $correos[$fila['datos']['correo']] = true;
            }
        }
        [$existentesTelefono, $existentesCorreo] = self::existentes($empresa, array_keys($telefonos), array_keys($correos));
        $vistosTelefono = [];
        $vistosCorreo = [];
        foreach ($resultados as &$fila) {
            if ($fila['estado'] !== 'valido') {
                continue;
            }
            $mensajes = [];
            $clavesTelefono = self::telefonos($fila['datos']);
            foreach ($clavesTelefono as $telefono) {
                if (isset($existentesTelefono[$telefono])) {
                    $mensajes[] = 'El teléfono coincide con un cliente activo de tu empresa.';
                }
                if (isset($vistosTelefono[$telefono])) {
                    $mensajes[] = 'El teléfono se repite con la fila '.$vistosTelefono[$telefono].' del archivo.';
                }
            }
            $correo = $fila['datos']['correo'];
            if ($correo && isset($existentesCorreo[$correo])) {
                $mensajes[] = 'El correo coincide con un cliente activo de tu empresa.';
            }
            if ($correo && isset($vistosCorreo[$correo])) {
                $mensajes[] = 'El correo se repite con la fila '.$vistosCorreo[$correo].' del archivo.';
            }
            if ($mensajes) {
                $fila['estado'] = 'duplicado';
                $fila['mensajes'] = array_values(array_unique($mensajes));
            } else {
                foreach ($clavesTelefono as $telefono) {
                    $vistosTelefono[$telefono] = $fila['fila'];
                }
                if ($correo) {
                    $vistosCorreo[$correo] = $fila['fila'];
                }
            }
        }
        unset($fila);
        return $resultados;
    }

    public static function hayDuplicado(array $datos, int $empresa): bool
    {
        [$telefonos, $correos] = self::existentes($empresa, self::telefonos($datos),
            empty($datos['correo']) ? [] : [$datos['correo']]);
        foreach (self::telefonos($datos) as $telefono) {
            if (isset($telefonos[$telefono])) {
                return true;
            }
        }
        return !empty($datos['correo']) && isset($correos[$datos['correo']]);
    }

    public static function resumen(array $filas): array
    {
        $resumen = ['total' => count($filas), 'valido' => 0, 'duplicado' => 0, 'error' => 0, 'importado' => 0];
        foreach ($filas as $fila) {
            $resumen[$fila['estado']]++;
        }
        return $resumen;
    }

    public static function telefonos(array $datos): array
    {
        $valores = [];
        foreach (['telefono_principal', 'telefono_secundario'] as $campo) {
            if (!empty($datos[$campo])) {
                $valores[] = preg_replace('/[^0-9]/', '', $datos[$campo]);
            }
        }
        return array_unique($valores);
    }

    private static function existentes(int $empresa, array $telefonos, array $correos): array
    {
        $porTelefono = [];
        $porCorreo = [];
        // Lotes acotados de parámetros; nunca se consultan clientes de otro tenant.
        $partesTelefonos = array_chunk($telefonos, 400);
        $partesCorreos = array_chunk($correos, 400);
        $cantidad = max(count($partesTelefonos), count($partesCorreos));
        for ($i = 0; $i < $cantidad; $i++) {
            $t = $partesTelefonos[$i] ?? [];
            $c = $partesCorreos[$i] ?? [];
            $consulta = DB::table('clientes')->where('id_empresa', $empresa)->whereNull('deleted_at')
                ->where(function ($q) use ($t, $c) {
                    $q->whereRaw('FALSE');
                    if ($t) {
                        $q->orWhereIn(DB::raw("regexp_replace(COALESCE(telefono_principal, ''), '[^0-9]', '', 'g')"), $t)
                            ->orWhereIn(DB::raw("regexp_replace(COALESCE(telefono_secundario, ''), '[^0-9]', '', 'g')"), $t);
                    }
                    if ($c) {
                        $q->orWhereIn(DB::raw('lower(trim(correo))'), $c);
                    }
                });
            foreach ($consulta->get(['telefono_principal', 'telefono_secundario', 'correo']) as $cliente) {
                foreach (self::telefonos((array) $cliente) as $telefono) {
                    $porTelefono[$telefono] = true;
                }
                if ($cliente->correo) {
                    $porCorreo[mb_strtolower(trim($cliente->correo), 'UTF-8')] = true;
                }
            }
        }
        return [$porTelefono, $porCorreo];
    }
}
