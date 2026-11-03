<?php

namespace App\Services\Clientes;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use ZipArchive;

class LectorClientes
{
    public const MAX_FILAS = 2000;
    public const MAX_BYTES = 5 * 1024 * 1024;
    public const COLUMNAS = [
        'nombre', 'apellido_paterno', 'apellido_materno', 'organizacion',
        'telefono_principal', 'telefono_secundario', 'correo', 'pais',
        'estado_region', 'ciudad', 'tipo_cliente', 'estado_lead', 'fuente',
    ];

    public static function clave(string $valor): string
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', Str::ascii(trim($valor))), '_'));
    }

    public static function encabezados(array $valores): array
    {
        $alias = ['empresa' => 'organizacion', 'nombre_empresa' => 'organizacion',
            'telefono' => 'telefono_principal', 'celular' => 'telefono_principal',
            'email' => 'correo', 'correo_electronico' => 'correo', 'estado' => 'estado_region'];
        $campos = [];
        foreach ($valores as $valor) {
            $clave = self::clave((string) $valor);
            $campo = $alias[$clave] ?? $clave;
            if (!in_array($campo, self::COLUMNAS, true)) {
                self::fallar('Columna no permitida: '.mb_substr((string) $valor, 0, 80).'. Usa la plantilla; no incluyas IDs, asignaciones ni fechas internas.');
            }
            if (in_array($campo, $campos, true)) {
                self::fallar('La columna '.$campo.' aparece más de una vez.');
            }
            $campos[] = $campo;
        }
        foreach (['nombre', 'apellido_paterno', 'telefono_principal'] as $requerida) {
            if (!in_array($requerida, $campos, true)) {
                self::fallar('Falta la columna obligatoria '.$requerida.'.');
            }
        }
        return $campos;
    }

    public static function leer(string $ruta, string $extension): array
    {
        if (!is_file($ruta) || filesize($ruta) > self::MAX_BYTES) {
            self::fallar('El archivo no está disponible o supera 5 MB.');
        }
        if ($extension === 'csv') {
            return self::csv($ruta);
        }
        if ($extension === 'xlsx') {
            return self::xlsx($ruta);
        }
        self::fallar('Solo se admiten archivos .csv y .xlsx.');
    }

    private static function csv(string $ruta): array
    {
        $contenido = file_get_contents($ruta);
        if ($contenido === false) {
            self::fallar('No se pudo leer el CSV.');
        }
        if (str_starts_with($contenido, "\xEF\xBB\xBF")) {
            $contenido = substr($contenido, 3);
        } elseif (str_starts_with($contenido, "\xFF\xFE")) {
            $contenido = mb_convert_encoding(substr($contenido, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($contenido, "\xFE\xFF")) {
            $contenido = mb_convert_encoding(substr($contenido, 2), 'UTF-8', 'UTF-16BE');
        } elseif (!mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'Windows-1252');
        }
        if (str_contains($contenido, "\0") || !mb_check_encoding($contenido, 'UTF-8')) {
            self::fallar('El CSV contiene una codificación no admitida. Guárdalo como CSV UTF-8.');
        }
        $primeraLinea = strtok($contenido, "\r\n");
        if ($primeraLinea === false) {
            self::fallar('El archivo está vacío.');
        }
        $separador = ',';
        $maximo = 0;
        foreach ([',', ';', "\t"] as $candidato) {
            $cantidad = count(str_getcsv($primeraLinea, $candidato, '"', ''));
            if ($cantidad > $maximo) {
                $maximo = $cantidad;
                $separador = $candidato;
            }
        }
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $contenido);
        rewind($stream);
        try {
            $campos = self::encabezados(fgetcsv($stream, 0, $separador, '"', '') ?: []);
            $filas = [];
            $numero = 1;
            while (($valores = fgetcsv($stream, 0, $separador, '"', '')) !== false) {
                $numero++;
                if ($numero > self::MAX_FILAS + 1) {
                    self::fallar('Máximo 2000 filas después del encabezado. Divide el archivo en partes.');
                }
                if (count(array_filter($valores, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }
                $errores = count($valores) === count($campos) ? [] : ['La cantidad de celdas no coincide con el encabezado.'];
                $datos = [];
                foreach ($campos as $i => $campo) {
                    $datos[$campo] = (string) ($valores[$i] ?? '');
                }
                $filas[] = ['fila' => $numero, 'datos' => $datos, 'errores_archivo' => $errores];
            }
        } finally {
            fclose($stream);
        }
        if (!$filas) {
            self::fallar('No hay clientes después del encabezado.');
        }
        return ['filas' => $filas, 'hoja' => null, 'formato' => 'csv'];
    }

    private static function xlsx(string $ruta): array
    {
        if (!class_exists(Xlsx::class) || !class_exists(ZipArchive::class)) {
            self::fallar('La lectura de Excel no está habilitada en este entorno. Puedes utilizar CSV o solicitar que se habilite Excel.');
        }
        $zip = new ZipArchive;
        if ($zip->open($ruta) !== true) {
            self::fallar('El archivo no es un libro .xlsx válido.');
        }
        try {
            $total = 0;
            if ($zip->numFiles > 512) {
                self::fallar('El libro tiene demasiados componentes. Copia los datos a la plantilla.');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (!$stat || $stat['size'] > 16 * 1024 * 1024) {
                    self::fallar('El contenido de Excel supera los límites de lectura.');
                }
                $total += $stat['size'];
                if ($total > 32 * 1024 * 1024) {
                    self::fallar('El libro es demasiado grande al descomprimirse. Divide los datos.');
                }
            }
        } finally {
            $zip->close();
        }
        try {
            $reader = new Xlsx;
            if (!$reader->canRead($ruta)) {
                self::fallar('El archivo no es un .xlsx compatible.');
            }
            $info = $reader->listWorksheetInfo($ruta);
            if (!$info || $info[0]['totalRows'] > self::MAX_FILAS + 1 || $info[0]['totalColumns'] > count(self::COLUMNAS)) {
                self::fallar('Usa la primera hoja, hasta 2000 filas y las 13 columnas de la plantilla. Elimina filas o columnas sobrantes con formato.');
            }
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $reader->setLoadSheetsOnly($info[0]['worksheetName']);
            $libro = $reader->load($ruta);
            try {
                $hoja = $libro->getSheet(0);
                $cabecera = [];
                for ($c = 1; $c <= $info[0]['totalColumns']; $c++) {
                    $celda = $hoja->getCell([$c, 1]);
                    if ($celda->getDataType() === DataType::TYPE_FORMULA) {
                        self::fallar('Los encabezados no pueden ser fórmulas.');
                    }
                    $cabecera[] = (string) $celda->getValue();
                }
                $campos = self::encabezados($cabecera);
                $filas = [];
                for ($r = 2; $r <= $info[0]['totalRows']; $r++) {
                    $datos = [];
                    $errores = [];
                    foreach ($campos as $i => $campo) {
                        $celda = $hoja->getCell([$i + 1, $r]);
                        $valor = $celda->getValue();
                        if ($celda->getDataType() === DataType::TYPE_FORMULA) {
                            $errores[] = 'No se admiten fórmulas; pega valores en '.$campo.'.';
                        } elseif (str_starts_with($campo, 'telefono') && $valor !== null && $valor !== '' && $celda->getDataType() !== DataType::TYPE_STRING && $celda->getDataType() !== DataType::TYPE_INLINE) {
                            $errores[] = 'Guarda '.$campo.' como texto para conservar sus dígitos y ceros iniciales.';
                        }
                        if (is_object($valor) && method_exists($valor, 'getPlainText')) {
                            $valor = $valor->getPlainText();
                        }
                        $datos[$campo] = is_scalar($valor) ? (string) $valor : '';
                    }
                    if (count(array_filter($datos, fn ($v) => trim($v) !== '')) > 0) {
                        $filas[] = ['fila' => $r, 'datos' => $datos, 'errores_archivo' => array_values(array_unique($errores))];
                    }
                }
            } finally {
                $libro->disconnectWorksheets();
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            self::fallar('No se pudo leer el libro. Usa la plantilla .xlsx o guarda una copia como CSV UTF-8.');
        }
        if (!$filas) {
            self::fallar('No hay clientes después del encabezado.');
        }
        return ['filas' => $filas, 'hoja' => $info[0]['worksheetName'], 'formato' => 'xlsx'];
    }

    private static function fallar(string $mensaje): never
    {
        throw ValidationException::withMessages(['archivo' => $mensaje]);
    }
}
