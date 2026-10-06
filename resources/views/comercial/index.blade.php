@extends('comercial.base')
@section('commercial-title', (int) Auth::user()->id_rol === 1 ? 'Prospectos COMI' : 'Mis prospectos')
@section('commercial-subtitle', 'Convierte cada conversación en un seguimiento personal.')
@section('commercial-content')
<div class="metrics">
    <div class="card metric"><span class="muted">Prospectos en tu ámbito</span><strong>{{ $resumen['total'] }}</strong></div>
    <div class="card metric"><span class="muted">Pendientes de atención o seguimiento</span><strong>{{ $resumen['pendientes'] }}</strong></div>
    <div class="card metric"><span class="muted">Cotizaciones enviadas</span><strong>{{ $resumen['cotizaciones'] }}</strong></div>
</div>
@if((int) Auth::user()->id_rol === 1)
<div class="head"><p class="muted">Incorpora las nuevas conversaciones captadas por COMI para la plataforma.</p><form method="POST" action="{{ route('comercial.incorporar') }}">@csrf<button class="btn primary">Incorporar prospectos COMI</button></form></div>
@endif
<section class="card">
    <form method="GET" action="{{ route('comercial.prospectos.index') }}" class="toolbar">
        <div class="field"><label for="buscar">Buscar prospecto</label><input id="buscar" name="buscar" type="search" maxlength="100" value="{{ $buscar }}" placeholder="Nombre, organización, correo o teléfono"></div>
        <div class="field"><label for="estado">Estado comercial</label><select id="estado" name="estado"><option value="">Todos los estados</option>@foreach(\App\Support\Comercial::ESTADOS as $valor => $etiqueta)<option value="{{ $valor }}" @selected($estado === $valor)>{{ $etiqueta }}</option>@endforeach</select></div>
        <button class="btn primary">Buscar</button><a class="btn" href="{{ route('comercial.prospectos.index') }}">Limpiar</a>
    </form>
    <p class="muted space" role="status">{{ $prospectos->total() }} resultados{{ $buscar !== '' ? ' para “'.$buscar.'”' : '' }}{{ $estado !== '' ? ' · '.\App\Support\Comercial::ESTADOS[$estado] : '' }}.</p>
    <div class="scroll space"><table><thead><tr><th>Prospecto</th><th>Contacto</th><th>Asesor</th><th>Estado</th><th>Detalle</th></tr></thead><tbody>
    @forelse($prospectos as $p)<tr><td><strong>{{ $p->organizacion ?: $p->nombre.' '.$p->apellido_paterno }}</strong><small>{{ $p->nombre }} {{ $p->apellido_paterno }}</small></td><td>{{ $p->telefono_principal }}<small>{{ $p->correo ?: 'Sin correo' }}</small></td><td>{{ $p->asesor_nombre ? $p->asesor_nombre.' '.$p->asesor_apellido : 'Sin asignar' }}</td><td><span class="badge {{ $p->estado }}">{{ \App\Support\Comercial::ESTADOS[$p->estado] }}</span></td><td><a href="{{ route('comercial.prospectos.show', $p->id) }}">Ver seguimiento →</a></td></tr>
    @empty<tr><td colspan="5" class="empty">No hay prospectos que coincidan con estos filtros.</td></tr>@endforelse
    </tbody></table></div>
    @include('comercial.paginacion', ['pagina' => $prospectos])
</section>
@endsection
