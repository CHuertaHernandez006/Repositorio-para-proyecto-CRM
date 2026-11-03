@extends('clientes.importacion.base')
@section('import-title', 'Importar clientes')
@section('import-content')
<div class="split">
    <section class="card">
        <p class="eyebrow">Nueva importación</p>
        <h2>Prepara tu archivo</h2>
        <p class="muted">Descarga la plantilla o adapta tu archivo con los mismos encabezados. Máximo 2000 filas y 5 MB por archivo.</p>
        <div class="actions">
            <a class="btn" href="{{ route('clientes.importacion.plantilla', 'csv') }}">Descargar plantilla CSV</a>
            @if($excelDisponible)<a class="btn" href="{{ route('clientes.importacion.plantilla', 'xlsx') }}">Descargar plantilla Excel</a>@endif
        </div>
        @if(!$excelDisponible)<p class="notice">CSV está disponible. La lectura de Excel aún no está habilitada en este entorno.</p>@endif
        <form method="POST" action="{{ route('clientes.importacion.previsualizar') }}" enctype="multipart/form-data" class="space">
            @csrf
            <div class="field">
                <label for="import-file">Archivo de clientes</label>
                <input id="import-file" type="file" name="archivo" accept="{{ $excelDisponible ? '.csv,.xlsx' : '.csv' }}" required>
                <small>CSV separado por coma, punto y coma o tabulador. En Excel se lee únicamente la primera hoja; usa teléfonos guardados como texto.</small>
            </div>
            <h3>Valores predeterminados</h3>
            <p class="muted">Se usarán si la columna correspondiente está vacía o no está en el archivo. Si escribes un valor en el archivo, utiliza el nombre del catálogo.</p>
            <div class="grid">
                @foreach(['tipo_cliente' => 'Tipo de cliente', 'estado_lead' => 'Estado del lead', 'fuente' => 'Fuente (opcional)'] as $campo => $etiqueta)
                    <div class="field">
                        <label for="default-{{ $campo }}">{{ $etiqueta }}</label>
                        <select id="default-{{ $campo }}" name="{{ $campo }}" @required($campo !== 'fuente')>
                            <option value="">{{ $campo === 'fuente' ? 'Sin fuente predeterminada' : 'Selecciona una opción' }}</option>
                            @foreach($catalogos[$campo] as $opcion)
                                <option value="{{ $opcion->{\App\Services\Clientes\PreparadorClientes::CATALOGOS[$campo]['id']} }}" @selected((string) old($campo) === (string) $opcion->{\App\Services\Clientes\PreparadorClientes::CATALOGOS[$campo]['id']})>{{ $opcion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            <div class="notice">En este paso no se crean clientes. Primero verás qué filas se pueden importar y cuáles necesitan corrección.</div>
            <button class="btn primary" type="submit">Revisar archivo</button>
        </form>
    </section>
    <aside>
        <section class="card">
            <h2>Cómo preparar los datos</h2>
            <ul>
                <li>Obligatorios: nombre, apellido paterno y teléfono principal.</li>
                <li>Teléfonos: 7 a 15 dígitos, con + opcional. Se eliminan espacios, guiones y paréntesis; no se adivina el código de país.</li>
                <li>En Excel, escribe los teléfonos en celdas de texto para conservar ceros iniciales.</li>
                <li>No incluyas fórmulas, IDs internos, fechas de registro ni asignaciones.</li>
                <li>Los clientes se guardarán en tu empresa y sin operario asignado.</li>
                <li>Los posibles duplicados de tu empresa o del archivo se omiten. Los registros existentes se conservan.</li>
            </ul>
        </section>
        <section class="card">
            <h2>Catálogos disponibles</h2>
            @foreach(['tipo_cliente' => 'Tipos de cliente', 'estado_lead' => 'Estados del lead', 'fuente' => 'Fuentes'] as $campo => $titulo)
                <details class="space"><summary>{{ $titulo }}</summary><ul>@foreach($catalogos[$campo] as $opcion)<li>{{ $opcion->nombre }}</li>@endforeach</ul></details>
            @endforeach
        </section>
    </aside>
</div>
@if($recientes->isNotEmpty())
<section class="card">
    <h2>Mis importaciones recientes</h2>
    <p class="muted">Vistas previas disponibles durante una hora; resultados durante 24 horas.</p>
    <div class="scroll"><table><thead><tr><th>Archivo</th><th>Fecha</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
        @foreach($recientes as $lote)
            <tr><td class="filename">{{ $lote->nombre_archivo }}</td><td>{{ \Carbon\Carbon::parse($lote->created_at)->timezone('America/Mexico_City')->format('d/m/Y H:i') }}</td><td><span class="tag {{ $lote->estado }}">{{ $lote->estado === 'completada' ? 'Procesada' : 'Por confirmar' }}</span></td><td><a href="{{ route('clientes.importacion.mostrar', $lote->id_importacion) }}">Abrir →</a></td></tr>
        @endforeach
    </tbody></table></div>
</section>
@endif
@endsection
