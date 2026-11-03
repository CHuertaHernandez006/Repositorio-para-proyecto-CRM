@extends('clientes.importacion.base')
@section('import-title', $lote->estado === 'completada' ? 'Resultado de la importación' : 'Revisa antes de importar')
@section('import-content')
@php
    $procesada = $lote->estado === 'completada';
    $etiquetas = ['valido' => 'Lista para importar', 'duplicado' => 'Omitida: posible duplicado', 'error' => 'Requiere corrección', 'importado' => 'Importada'];
@endphp
<div class="metrics">
    <div class="metric"><small>Filas leídas</small><strong>{{ $resumen['total'] }}</strong></div>
    <div class="metric"><small>{{ $procesada ? 'Clientes importados' : 'Listas para importar' }}</small><strong>{{ $procesada ? $resumen['importado'] : $resumen['valido'] }}</strong></div>
    <div class="metric"><small>Posibles duplicados</small><strong>{{ $resumen['duplicado'] }}</strong></div>
    <div class="metric"><small>Filas con errores</small><strong>{{ $resumen['error'] }}</strong></div>
</div>
<section class="card">
    <div class="head">
        <div><h2 class="filename">{{ $lote->nombre_archivo }}</h2><p class="muted">{{ $opciones['hoja'] ? 'Primera hoja: '.$opciones['hoja'].' · ' : '' }}Disponible hasta {{ \Carbon\Carbon::parse($lote->vence_at)->timezone('America/Mexico_City')->format('d/m/Y H:i') }} · Ciudad de México.</p></div>
        <div class="actions"><a class="btn" href="{{ route('clientes.importacion.reporte', $lote->id_importacion) }}">Descargar reporte CSV</a><a class="btn" href="{{ route('clientes.importacion.index') }}">Otra importación</a></div>
    </div>
    @if(!$procesada)
        <div class="notice">Se importarán únicamente las filas válidas. Las filas con errores o posibles duplicados se omitirán. Al confirmar se comprobarán otra vez los catálogos y los registros existentes; el resultado final puede cambiar.</div>
        <div class="actions" style="margin-bottom:24px">
            <form method="POST" action="{{ route('clientes.importacion.confirmar', $lote->id_importacion) }}" onsubmit="return confirm('¿Importar las filas válidas a tu empresa? Las demás se omitirán.');">
                @csrf
                <button type="submit" class="btn primary" @disabled($resumen['valido'] === 0)>Confirmar importación de {{ $resumen['valido'] }} clientes</button>
            </form>
            <form method="POST" action="{{ route('clientes.importacion.cancelar', $lote->id_importacion) }}">
                @csrf @method('DELETE')
                <button class="btn danger" type="submit">Descartar vista previa</button>
            </form>
        </div>
    @else
        <div class="notice alert">Lote procesado. Los clientes creados están disponibles en Clientes y pueden distribuirse desde Asignaciones. Confirmar de nuevo este mismo lote no vuelve a crearlos.</div>
        <div class="actions" style="margin-bottom:24px"><a class="btn primary" href="{{ route('clientes.index') }}">Ver clientes</a><a class="btn" href="{{ route('clientes.asignaciones.index') }}">Asignar clientes</a></div>
    @endif
    <form method="GET" action="{{ route('clientes.importacion.mostrar', $lote->id_importacion) }}" class="actions" style="margin-bottom:20px">
        <div><label for="row-state">Mostrar filas</label><select id="row-state" name="estado"><option value="">Todas</option>@foreach($etiquetas as $valor => $texto)<option value="{{ $valor }}" @selected($estado === $valor)>{{ $texto }}</option>@endforeach</select></div>
        <button class="btn" type="submit">Filtrar</button>
    </form>
    <div class="scroll"><table><thead><tr><th>Fila</th><th>Cliente</th><th>Contacto</th><th>Resultado</th><th>Detalle</th></tr></thead><tbody>
        @forelse($filas as $fila)
            <tr><td>{{ $fila['fila'] }}</td><td><strong>{{ \Illuminate\Support\Str::limit($fila['datos']['nombre'] ?? '', 100) }} {{ \Illuminate\Support\Str::limit($fila['datos']['apellido_paterno'] ?? '', 100) }}</strong><small>{{ \Illuminate\Support\Str::limit($fila['datos']['organizacion'] ?? '', 150) }}</small>
                <details class="space"><summary>Ver ficha</summary>
                    @foreach(['apellido_materno' => 'Apellido materno', 'telefono_secundario' => 'Teléfono secundario', 'pais' => 'País', 'estado_region' => 'Estado / región', 'ciudad' => 'Ciudad'] as $campo => $titulo)
                        <small>{{ $titulo }}: {{ \Illuminate\Support\Str::limit($fila['datos'][$campo] ?? 'Sin dato', 150) }}</small>
                    @endforeach
                    @foreach(['id_tipo_cliente' => 'Tipo', 'id_estado_lead' => 'Estado del lead', 'id_fuente' => 'Fuente'] as $campo => $titulo)
                        <small>{{ $titulo }}: {{ $nombresCatalogo[$campo][(int) ($fila['datos'][$campo] ?? 0)] ?? 'Sin dato válido' }}</small>
                    @endforeach
                </details></td><td>{{ $fila['datos']['telefono_principal'] }}<small>{{ $fila['datos']['correo'] }}</small></td><td><span class="tag {{ $fila['estado'] }}">{{ $etiquetas[$fila['estado']] }}</span></td><td>@if($fila['mensajes'])<ul>@foreach($fila['mensajes'] as $mensaje)<li>{{ $mensaje }}</li>@endforeach</ul>@else<span class="muted">Validación correcta. Sin cambios en la base todavía.</span>@endif</td></tr>
        @empty
            <tr><td colspan="5" class="empty">No hay filas para este filtro.</td></tr>
        @endforelse
    </tbody></table></div>
    <nav class="paging" aria-label="Paginación de filas">
        @if(!$filas->onFirstPage())<a class="btn" href="{{ $filas->previousPageUrl() }}">← Anterior</a>@else<span></span>@endif
        <span class="muted">Página {{ $filas->currentPage() }} de {{ $filas->lastPage() }} · {{ $filas->total() }} filas en este filtro</span>
        @if($filas->hasMorePages())<a class="btn" href="{{ $filas->nextPageUrl() }}">Siguiente →</a>@endif
    </nav>
</section>
@endsection
