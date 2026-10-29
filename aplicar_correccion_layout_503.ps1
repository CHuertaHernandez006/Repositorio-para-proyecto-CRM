# Ejecutar desde la raiz del proyecto Laravel.
# Modifica exclusivamente tres expresiones de navegacion en layouts/app.blade.php.
# Crea una copia de respaldo antes de escribir.
$ErrorActionPreference = 'Stop'

$archivo = Join-Path (Get-Location).Path 'resources/views/layouts/app.blade.php'
if (-not (Test-Path -LiteralPath $archivo -PathType Leaf)) {
    throw "No se encontro $archivo. Ejecuta este script desde la carpeta raiz del CRM."
}

$utf8 = New-Object System.Text.UTF8Encoding($false)
$contenido = [System.IO.File]::ReadAllText($archivo, [System.Text.Encoding]::UTF8)
if ($contenido.Contains('$esGestorAgendaInterna = false;')) {
    Write-Host 'Este ajuste ya esta aplicado. No se modifico ningun archivo.' -ForegroundColor Yellow
    exit 0
}

$antiguoAsesor = @'
$esAsesorComercial = \App\Support\Comercial::asesor($usuarioActual);
'@
$nuevoAsesor = @'
$esAsesorComercial = false;
$esGestorAgendaInterna = false;
$empresasAgendaInterna = null;

// Una falla en una comprobacion del menu no debe inutilizar el Dashboard.
// Las rutas y controladores siguen aplicando su propia autorizacion.
try {
    $esAsesorComercial = (bool) \App\Support\Comercial::asesor($usuarioActual);
} catch (\Throwable $e) {
    \Illuminate\Support\Facades\Log::error('CRM menu: fallo Comercial::asesor', [
        'tipo' => get_class($e),
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine(),
        'traza' => $e->getTraceAsString(),
    ]);
}

try {
    $esGestorAgendaInterna = (bool) \App\Support\AgendaInterna::gestor($usuarioActual);
} catch (\Throwable $e) {
    \Illuminate\Support\Facades\Log::error('CRM menu: fallo AgendaInterna::gestor', [
        'tipo' => get_class($e),
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine(),
        'traza' => $e->getTraceAsString(),
    ]);
}

if (!$esGestorAgendaInterna) {
    try {
        $empresasAgendaInterna = \App\Support\AgendaInterna::empresas();
        if (!is_array($empresasAgendaInterna)) {
            throw new \UnexpectedValueException('AgendaInterna::empresas debe regresar un arreglo.');
        }
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('CRM menu: fallo AgendaInterna::empresas', [
            'tipo' => get_class($e),
            'mensaje' => $e->getMessage(),
            'archivo' => $e->getFile(),
            'linea' => $e->getLine(),
            'traza' => $e->getTraceAsString(),
        ]);
        $empresasAgendaInterna = null;
    }
}
'@

$antiguaAgendaComercial = @'
'visible' => $esAsesorComercial || \App\Support\AgendaInterna::gestor($usuarioActual),
'@
$nuevaAgendaComercial = @'
'visible' => $esAsesorComercial || $esGestorAgendaInterna,
'@

$antiguaAgendaComi = @'
'visible' => Auth::check() && (\App\Support\AgendaInterna::gestor($usuarioActual) || !in_array((int) $usuarioActual->id_empresa, \App\Support\AgendaInterna::empresas(), true)),
'@
$nuevaAgendaComi = @'
'visible' => Auth::check() && ($esGestorAgendaInterna || (is_array($empresasAgendaInterna) && !in_array((int) $usuarioActual->id_empresa, $empresasAgendaInterna, true))),
'@

$reemplazos = @(
    @{ Antes = $antiguoAsesor; Despues = $nuevoAsesor; Etiqueta = 'Asesor comercial' },
    @{ Antes = $antiguaAgendaComercial; Despues = $nuevaAgendaComercial; Etiqueta = 'Agenda comercial' },
    @{ Antes = $antiguaAgendaComi; Despues = $nuevaAgendaComi; Etiqueta = 'Agenda COMI' }
)

foreach ($r in $reemplazos) {
    $conteo = [regex]::Matches($contenido, [regex]::Escape($r.Antes)).Count
    if ($conteo -ne 1) {
        throw "No se aplico ningun cambio: la seccion '$($r.Etiqueta)' aparece $conteo veces (se esperaba exactamente 1)."
    }
}

$salto = if ($contenido.Contains("`r`n")) { "`r`n" } else { "`n" }
$nuevoAsesor = $nuevoAsesor.Replace("`n", $salto)
$reemplazos[0].Despues = $nuevoAsesor

$corregido = $contenido
foreach ($r in $reemplazos) {
    $corregido = $corregido.Replace($r.Antes, $r.Despues)
}

# El respaldo va a la carpeta temporal de Windows, fuera del repositorio Git.
$nombreCopia = 'COMI_app.blade.php.respaldo_' + (Get-Date -Format 'yyyyMMdd_HHmmss')
$respaldo = Join-Path ([System.IO.Path]::GetTempPath()) $nombreCopia
Copy-Item -LiteralPath $archivo -Destination $respaldo -ErrorAction Stop
[System.IO.File]::WriteAllText($archivo, $corregido, $utf8)

Write-Host 'Se ajustaron las comprobaciones del menu sin modificar los estilos ni otras vistas.' -ForegroundColor Green
Write-Host "Respaldo: $respaldo"
Write-Host 'Revisa storage/logs/laravel.log si vuelve a fallar una comprobacion del menu.'
