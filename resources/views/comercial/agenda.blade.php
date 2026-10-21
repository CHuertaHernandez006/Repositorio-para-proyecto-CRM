@extends('comercial.base')
@section('commercial-title', 'Agenda comercial')
@section('commercial-subtitle', 'Citas confirmadas y asignadas al equipo interno de COMICenter e InterAcom.')
@push('styles')
<style>
.cm .agenda-next{display:flex;align-items:center;justify-content:space-between;gap:20px;border:1px solid #2f6b5e;border-left:4px solid #34d399;border-radius:14px;padding:24px;margin-bottom:20px;background:linear-gradient(110deg,#133337,#1e293b)}
.cm .agenda-next h2{margin:8px 0}.cm .agenda-next p{margin:6px 0}.cm .next-label{color:#6ee7b7;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.cm .calendar-modal{margin:auto;width:min(1250px,96vw);max-width:none;max-height:92vh;overflow:auto;padding:24px;border:1px solid #475569;border-radius:16px;background:#1e293b;color:#f1f5f9}
.cm .calendar-modal::backdrop{background:#020617bd;backdrop-filter:blur(4px)}
.cm .calendar-top{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px}
#commercial-calendar{--fc-border-color:#334155;--fc-page-bg-color:#1e293b;--fc-neutral-bg-color:#172235;--fc-list-event-hover-bg-color:#203249;--fc-today-bg-color:#38bdf810;--fc-button-bg-color:#142033;--fc-button-border-color:#475569;--fc-button-hover-bg-color:#244057;--fc-button-active-bg-color:#0284c7;--fc-event-bg-color:#0369a1;--fc-event-border-color:#38bdf8}
#commercial-calendar .fc-col-header-cell-cushion,#commercial-calendar .fc-daygrid-day-number{color:#e2e8f0}
.cm .calendar-details{margin-top:18px;white-space:pre-wrap;line-height:1.7;color:#cbd5e1}
@media(max-width:650px){.cm .agenda-next{align-items:stretch;flex-direction:column}.cm .calendar-modal{padding:14px}#commercial-calendar .fc-toolbar{align-items:stretch;gap:10px;flex-direction:column}}
</style>
@endpush
@section('commercial-content')
    <div class="actions" style="justify-content:flex-end;margin-bottom:20px;">
        @if($gestor)<a class="btn" href="{{ route('comi.agenda.index') }}">Gestionar solicitudes COMI</a>@endif
        <button class="btn primary" type="button" id="open-commercial-calendar" aria-haspopup="dialog">Ver calendario</button>
    </div>
    <div class="metrics">
        <div class="card metric"><small>Citas confirmadas para hoy</small><strong>{{ $resumen['hoy'] }}</strong></div>
        <div class="card metric"><small>Citas confirmadas con horario anterior</small><strong>{{ $resumen['anteriores'] }}</strong></div>
        <div class="card metric"><small>Próximas citas confirmadas</small><strong>{{ $resumen['proximas'] }}</strong></div>
    </div>
    <section class="agenda-next" aria-labelledby="next-commercial-title">
        <div>
            <span class="next-label">Próxima cita</span>
            @if($proxima)
                <h2 id="next-commercial-title">{{ $proxima->empresa_nombre ?: $proxima->contacto_nombre }}</h2>
                <p>{{ \Carbon\Carbon::parse($proxima->fecha_hora_inicio)->timezone($zona)->format('d/m/Y H:i') }} · {{ $proxima->motivo }}</p>
                <p>Contacto: {{ $proxima->contacto_nombre }} · {{ $proxima->telefono_contacto }}</p>
                <p>Asesor: <strong>{{ $proxima->asesor_nombre }}</strong></p>
            @else
                <h2 id="next-commercial-title">No hay próximas citas confirmadas</h2>
                <p>Las citas aparecerán aquí después de confirmarlas y asignarles un asesor en Agenda COMI.</p>
            @endif
        </div>
        @if($proxima && $proxima->prospecto_id && ((int) auth()->user()->id_rol === 1 || (\App\Support\Comercial::asesor(auth()->user()) && (int) $proxima->prospecto_asesor === (int) auth()->id())))
            <a class="btn primary" href="{{ route('comercial.prospectos.show', $proxima->prospecto_id) }}">Ver prospecto</a>
        @endif
    </section>
    <section class="card">
        <h2>Agenda del equipo comercial</h2>
        <p class="muted">Horario {{ $zona }}. Los filtros también se aplican al calendario y a los indicadores.</p>
        <form method="GET" action="{{ route('comercial.agenda') }}" class="toolbar">
            <div class="field"><label for="commercial-search">Buscar</label><input id="commercial-search" name="buscar" value="{{ request('buscar') }}" maxlength="100" placeholder="Contacto, empresa, teléfono o motivo…"></div>
            @if($gestor)
                <div class="field"><label for="commercial-advisor">Asesor</label><select id="commercial-advisor" name="asesor"><option value="">Todos</option>@foreach($asesores as $asesor)<option value="{{ $asesor->id_usuario }}" @selected((string) request('asesor') === (string) $asesor->id_usuario)>{{ $asesor->nombre }} {{ $asesor->apellido_paterno }}</option>@endforeach</select></div>
            @endif
            <button class="btn primary" type="submit">Buscar</button><a class="btn" href="{{ route('comercial.agenda') }}">Limpiar</a>
        </form>
        <p class="muted" role="status">{{ $citas->total() }} citas confirmadas y asignadas{{ request('buscar') ? ' para “'.request('buscar').'”' : '' }}.</p>
        <div class="scroll"><table><thead><tr><th>Fecha y hora</th><th>Contacto</th><th>Motivo</th><th>Asesor</th><th>Confirmación</th></tr></thead><tbody>
        @forelse($citas as $cita)
            <tr>
                <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->timezone($zona)->format('d/m/Y H:i') }}<small>{{ $cita->agenda_nombre }}</small></td>
                <td><strong>{{ $cita->contacto_nombre }}</strong><small>{{ $cita->empresa_nombre }}</small><small>{{ $cita->telefono_contacto }}</small></td>
                <td>{{ $cita->motivo }}</td><td>{{ $cita->asesor_nombre }}</td>
                <td><span class="badge contratado">Confirmada</span><small>{{ $cita->correo_enviado_at ? 'Correo enviado' : 'Correo pendiente' }}</small></td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">No hay citas que coincidan. Las solicitudes pendientes se gestionan en Agenda COMI.</td></tr>
        @endforelse
        </tbody></table></div>
        <div class="space">{{ $citas->links() }}</div>
    </section>
    <dialog class="calendar-modal" id="commercial-calendar-modal" aria-labelledby="commercial-calendar-title">
        <div class="calendar-top"><div><div class="eyebrow">Vista de apoyo</div><h2 id="commercial-calendar-title">Calendario comercial</h2><small>Horario America/Mexico_City · Citas confirmadas</small></div><button class="btn" type="button" id="close-commercial-calendar">Cerrar</button></div>
        <p id="commercial-calendar-status" role="status"></p>
        <div id="commercial-calendar"></div>
        <div class="calendar-details" id="commercial-calendar-details" aria-live="polite"></div>
    </dialog>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.21/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('commercial-calendar-modal');
    const open = document.getElementById('open-commercial-calendar');
    const status = document.getElementById('commercial-calendar-status');
    const endpoint = @json(route('comercial.agenda.eventos'));
    const filters = @json(request()->only(['buscar', 'asesor']));
    let calendar;
    open.addEventListener('click', () => {
        modal.showModal();
        if (!window.FullCalendar) {status.textContent = 'No se pudo cargar el calendario. Puedes consultar las citas en la lista.'; return;}
        if (!calendar) {
            calendar = new FullCalendar.Calendar(document.getElementById('commercial-calendar'), {
                locale: 'es', firstDay: 1, timeZone: 'UTC', height: 580,
                now: () => new Intl.DateTimeFormat('sv-SE', {timeZone:'America/Mexico_City', year:'numeric', month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit', second:'2-digit', hourCycle:'h23'}).format(new Date()).replace(' ', 'T'),
                customButtons: {hoyMexico: {text: 'Hoy', click: () => calendar.gotoDate(new Intl.DateTimeFormat('sv-SE', {timeZone:'America/Mexico_City', year:'numeric', month:'2-digit', day:'2-digit'}).format(new Date()))}},
                initialView: window.innerWidth < 650 ? 'timeGridDay' : 'timeGridWeek',
                initialDate: @json(now('America/Mexico_City')->format('Y-m-d')),
                headerToolbar: {left: 'prev,next hoyMexico', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'},
                buttonText: {today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Agenda'},
                allDaySlot: false, slotMinTime: '00:00:00', slotMaxTime: '24:00:00', scrollTime: '08:00:00',
                noEventsContent: 'No hay citas confirmadas en este período.',
                events: async (info, success, failure) => {
                    status.textContent = 'Cargando citas…';
                    const url = new URL(endpoint);
                    Object.entries(filters).forEach(([k,v]) => {if (v) url.searchParams.set(k,v);});
                    // Interpretar los límites como fechas locales de México en el servidor.
                    url.searchParams.set('start', info.startStr.slice(0,10));
                    url.searchParams.set('end', info.endStr.slice(0,10));
                    try {
                        const r = await fetch(url, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
                        if (!r.ok) throw new Error('No fue posible consultar las citas.');
                        const events = await r.json(); success(events);
                        status.textContent = events.length + ' citas en este período.';
                    } catch(e) {failure(e); status.textContent = 'No se pudieron cargar las citas. Cierra y vuelve a abrir el calendario para reintentar.';}
                },
                eventClick: info => {
                    const e = info.event;
                    document.getElementById('commercial-calendar-details').textContent = e.title + '\n' + (e.extendedProps.telefono || '') + '\n' + (e.extendedProps.motivo || '');
                },
            });
            calendar.render();
        } else {calendar.updateSize(); calendar.refetchEvents();}
    });
    document.getElementById('close-commercial-calendar').addEventListener('click', () => modal.close());
    modal.addEventListener('close', () => open.focus());
});
</script>
@endpush
