@extends('comercial.base')
@section('commercial-title', 'Agenda comercial')
@section('commercial-subtitle', 'Próximos contactos y seguimientos pendientes, ordenados por fecha.')
@section('commercial-content')
<section class="card">
<p class="muted">Horario: {{ config('app.timezone') }} · {{ $prospectos->total() }} contactos programados</p>
<div class="scroll space"><table><thead><tr><th>Fecha y hora</th><th>Prospecto</th><th>Asesor</th><th>Acción</th></tr></thead><tbody>
@forelse($prospectos as $p)
@php($fecha = \Carbon\Carbon::parse($p->proximo_contacto)->timezone(config('app.timezone')))
<tr><td><strong>{{ $fecha->format('d/m/Y H:i') }}</strong>@if($fecha->isPast())<small class="danger">Seguimiento vencido</small>@endif</td><td>{{ $p->organizacion ?: $p->nombre.' '.$p->apellido_paterno }}<small>{{ $p->telefono_principal }}</small></td><td>{{ $p->asesor_nombre ?: 'Sin asignar' }} {{ $p->asesor_apellido }}</td><td><a href="{{ route('comercial.prospectos.show', $p->id) }}">Abrir prospecto →</a></td></tr>
@empty<tr><td colspan="4" class="empty">No tienes contactos programados. Agenda el próximo contacto desde el seguimiento de un prospecto.</td></tr>@endforelse
</tbody></table></div>
@include('comercial.paginacion', ['pagina' => $prospectos])
</section>
@endsection
