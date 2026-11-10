@extends('clientes.lotes.base')
@section('lote-titulo', 'Importaciones de tu empresa')
@section('lote-contenido')
<div class="actions" style="margin-bottom:20px"><a class="btn primary" href="{{ route('clientes.importacion.index') }}">Importar clientes</a></div>
<div class="notice">Aquí aparecen los lotes confirmados desde que se habilitó este historial. Solo incluyen clientes nuevos incorporados; las filas duplicadas o rechazadas no se asignan. Las importaciones anteriores no se reconstruyen automáticamente.</div>
<div class="card">
    <form method="GET" action="{{ route('clientes.lotes.index') }}">
        <label for="buscar-lote">Buscar por nombre del archivo</label>
        <div class="actions"><input id="buscar-lote" name="buscar" type="search" maxlength="100" value="{{ $buscar }}" style="max-width:420px"><button class="btn">Buscar</button>@if($buscar !== '')<a class="btn" href="{{ route('clientes.lotes.index') }}">Limpiar</a>@endif</div>
    </form>
    <div class="scroll" style="margin-top:24px" role="region" aria-label="Historial de importaciones" tabindex="0"><table>
        <thead><tr><th scope="col">Archivo / fecha de confirmación</th><th scope="col">Filas leídas</th><th scope="col">Importados</th><th scope="col">Omitidos</th><th scope="col">Clientes disponibles</th><th scope="col">Acciones</th></tr></thead>
        <tbody>@forelse($lotes as $lote)
            <tr><td class="file-name"><strong>{{ $lote->nombre_archivo }}</strong><small>{{ \Carbon\Carbon::parse($lote->created_at)->timezone('America/Mexico_City')->format('Y-m-d H:i:s') }} · Ciudad de México</small></td>
            <td>{{ $lote->filas_leidas }}</td><td>{{ $lote->importados }}</td><td>{{ $lote->duplicados }} duplicados<small>{{ $lote->errores }} con errores</small></td><td>{{ $lote->disponibles }}<small>Excluye clientes eliminados.</small></td>
            <td><a class="btn" href="{{ route('clientes.lotes.mostrar', $lote->id_lote) }}">Ver y asignar</a></td></tr>
        @empty<tr><td colspan="6" class="empty">{{ $buscar !== '' ? 'No hay importaciones que coincidan.' : 'Todavía no hay importaciones registradas en este historial.' }}</td></tr>@endforelse</tbody>
    </table></div><div style="margin-top:20px">{{ $lotes->links() }}</div>
</div>
@endsection
