@extends('comercial.base')
@section('commercial-title', 'Equipo comercial')
@section('commercial-subtitle', 'Asesores internos de la plataforma y su carga de seguimiento.')
@section('commercial-content')
<div class="split"><section class="card"><h2>Asesores de COMICenter</h2><div class="scroll space"><table><thead><tr><th>Asesor</th><th>Abiertos</th><th>Último seguimiento</th><th>Acceso</th></tr></thead><tbody>
@forelse($asesores as $a)<tr><td><strong>{{ $a->nombre }} {{ $a->apellido_paterno }}</strong><small>{{ $a->correo }}</small></td><td>{{ $cargas[$a->id_usuario] ?? 0 }}</td><td>{{ isset($actividad[$a->id_usuario]) ? \Carbon\Carbon::parse($actividad[$a->id_usuario])->timezone(config('app.timezone'))->format('d/m/Y H:i') : 'Sin actividad' }}</td><td><form method="POST" action="{{ route('comercial.equipo.estado', $a->id_usuario) }}">@csrf @method('PUT')<input type="hidden" name="activo" value="{{ $a->activo ? '0' : '1' }}"><button class="btn">{{ $a->activo ? 'Desactivar' : 'Activar' }}</button></form><small>{{ $a->activo ? 'Activo' : 'Inactivo' }}</small></td></tr>@empty<tr><td colspan="4" class="empty">Crea el primer asesor para comenzar a asignar prospectos.</td></tr>@endforelse
</tbody></table></div>@include('comercial.paginacion', ['pagina' => $asesores])</section>
<section class="card"><h2>Nuevo asesor interno</h2><p class="muted">Tendrá acceso exclusivamente a sus prospectos asignados y su agenda comercial.</p><form class="space" method="POST" action="{{ route('comercial.equipo.store') }}">@csrf
<div class="field"><label for="nombre">Nombre(s)</label><input name="nombre" id="nombre" maxlength="100" autocomplete="given-name" value="{{ old('nombre') }}" required></div>
<div class="field"><label for="apellido_paterno">Apellido paterno</label><input name="apellido_paterno" id="apellido_paterno" maxlength="100" autocomplete="family-name" value="{{ old('apellido_paterno') }}" required></div>
<div class="field"><label for="correo">Correo de acceso</label><input name="correo" id="correo" type="email" maxlength="150" autocomplete="email" value="{{ old('correo') }}" required></div>
<div class="field"><label for="password">Contraseña</label><input name="password" id="password" type="password" minlength="12" maxlength="128" autocomplete="new-password" required><small>Mínimo 12 caracteres. Se almacena cifrada mediante hash.</small></div>
<div class="field"><label for="password_confirmation">Confirmar contraseña</label><input name="password_confirmation" id="password_confirmation" type="password" minlength="12" maxlength="128" autocomplete="new-password" required></div><button class="btn primary">Crear asesor</button>
</form></section></div>
@endsection
