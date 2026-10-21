@extends('comercial.base')
@section('title', 'Citas · COMICenter')
@section('header-title', 'Citas')
@section('commercial-title', 'Citas')
@section('commercial-subtitle', 'Consulta las citas confirmadas de COMI y las citas que registraste para tus clientes.')
@section('commercial-content')
    <section class="card" aria-labelledby="comi-confirmadas-title">
        <div class="head">
            <div><div class="eyebrow">COMICENTER / InterAcom</div><h2 id="comi-confirmadas-title">Citas COMI confirmadas</h2><p class="muted">La misma confirmación que ves en Agenda COMI. Horario America/Mexico_City.</p></div>
            <div class="actions"><a class="btn" href="{{ route('comi.agenda.index') }}">Gestionar solicitudes</a><a class="btn primary" href="{{ route('comercial.agenda') }}">Agenda comercial y calendario</a></div>
        </div>
        <form method="GET" action="{{ route('citas.index') }}" class="toolbar">
            <div class="field"><label for="buscar-comi">Buscar en citas COMI</label><input id="buscar-comi" type="search" name="buscar_comi" value="{{ $buscarComi }}" maxlength="100" placeholder="Contacto, empresa, motivo o asesor"></div>
            <button class="btn primary" type="submit">Buscar</button>
        </form>
        <p class="muted" role="status">{{ $citasComi->total() }} citas COMI confirmadas{{ $buscarComi !== '' ? ' para “'.$buscarComi.'”' : '' }}.</p>
        <div class="scroll"><table><thead><tr><th>Referencia</th><th>Fecha y hora</th><th>Contacto</th><th>Motivo</th><th>Asesor interno</th><th>Estado</th></tr></thead><tbody>
            @forelse($citasComi as $citaComi)
                <tr>
                    <td>COMI #{{ $citaComi->id_cita }}</td>
                    <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($citaComi->fecha_hora_inicio)->timezone('America/Mexico_City')->format('d/m/Y H:i') }}<small>{{ $citaComi->agenda_nombre }}</small></td>
                    <td><strong>{{ $citaComi->contacto_nombre }}</strong><small>{{ $citaComi->empresa_nombre }}</small><small>{{ $citaComi->telefono_contacto }}</small></td>
                    <td>{{ $citaComi->motivo }}</td>
                    <td>{{ $citaComi->asesor_nombre ?: 'Sin asignar' }}@if(!$citaComi->id_asesor)<small><a href="{{ route('comi.agenda.index') }}">Asignar en Agenda COMI</a></small>@endif</td>
                    <td><span class="badge contratado">Confirmada</span><small>{{ $citaComi->correo_enviado_at ? 'Correo enviado' : 'Correo pendiente' }}</small></td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No hay citas COMI confirmadas que coincidan.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="space">{{ $citasComi->links() }}</div>
    </section>
    <section class="card" aria-labelledby="citas-clientes-title">
        <div class="head"><div><h2 id="citas-clientes-title">Citas de clientes</h2><p class="muted">Citas registradas para los operarios de tu empresa.</p></div><a class="btn primary" href="{{ route('citas.create') }}">Nueva cita</a></div>
        <form method="GET" action="{{ route('citas.index') }}" class="toolbar">
            <div class="field"><label for="buscar-cliente">Buscar cliente</label><input id="buscar-cliente" type="search" name="buscar" value="{{ $buscar }}" maxlength="100"></div>
            <div class="field"><label for="estado-cliente">Estado de cita</label><select id="estado-cliente" name="estado"><option value="">Todos</option>@foreach($estados as $estado)<option value="{{ $estado->id_estado_cita }}" @selected((string) request('estado') === (string) $estado->id_estado_cita)>{{ $estado->nombre }}</option>@endforeach</select></div>
            <div class="field"><label for="operario-cliente">Operario</label><select id="operario-cliente" name="operario"><option value="">Todos</option>@foreach($operarios as $operario)<option value="{{ $operario->id_usuario }}" @selected((string) request('operario') === (string) $operario->id_usuario)>{{ $operario->name }}</option>@endforeach</select></div>
            <div class="field"><label for="desde-cliente">Desde</label><input id="desde-cliente" type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"></div>
            <div class="field"><label for="hasta-cliente">Hasta</label><input id="hasta-cliente" type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"></div>
            <button class="btn primary" type="submit">Filtrar</button><a class="btn" href="{{ route('citas.index') }}">Limpiar</a>
        </form>
        <p class="muted" role="status">{{ $citas->total() }} citas de clientes encontradas.</p>
        <div class="scroll"><table><thead><tr><th>Referencia</th><th>Fecha y hora</th><th>Cliente</th><th>Operario</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            @forelse($citas as $cita)
                <tr>
                    <td>#{{ $cita->id_cita }}</td>
                    <td style="white-space:nowrap;">{{ $cita->fecha_hora_inicio?->timezone('America/Mexico_City')->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                    <td>{{ $cita->cliente?->nombre }} {{ $cita->cliente?->apellido_paterno }}<small>{{ $cita->motivo }}</small></td>
                    <td>{{ $cita->usuario?->name ?? 'Sin operario' }}</td><td>{{ $cita->estadoCita?->nombre ?? 'Sin estado' }}</td>
                    <td><div class="actions"><a href="{{ route('citas.show', $cita->id_cita) }}">Ver</a><a href="{{ route('citas.edit', $cita->id_cita) }}">Editar</a><form method="POST" action="{{ route('citas.destroy', $cita->id_cita) }}" onsubmit="return confirm('¿Eliminar esta cita?');">@csrf @method('DELETE')<button class="btn danger" type="submit">Eliminar</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No hay citas de clientes que coincidan con los filtros.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="space">{{ $citas->links() }}</div>
    </section>
@endsection
