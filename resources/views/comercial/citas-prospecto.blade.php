<section class="card">
    <h2>Citas del prospecto</h2>
    <p class="muted">Horario de Ciudad de México. La cita mantiene el estado registrado en su agenda.</p>
    @forelse($citasProspecto as $citaRegistro)
        <article class="history">
            <strong>{{ \Carbon\Carbon::parse($citaRegistro->cita_fecha)->timezone('America/Mexico_City')->format('d/m/Y H:i') }}</strong>
            <span class="badge">{{ \App\Support\CitasProspecto::ESTADOS[$citaRegistro->cita_estado] ?? $citaRegistro->cita_estado }}</span>
            <p>{{ $citaRegistro->cita_agenda }} · {{ $citaRegistro->cita_fuente === 'comi' ? 'COMI' : 'Agenda anterior' }} #{{ $citaRegistro->cita_id }}</p>
            <p class="muted">Asesor de la cita: {{ $citaRegistro->cita_asesor ?: 'Sin asignar' }}</p>
            @if(\Carbon\Carbon::parse($citaRegistro->cita_fecha)->isPast())
                <small>Horario pasado. El sistema no presupone que la cita haya sido atendida.</small>
            @endif
        </article>
    @empty
        <p class="empty">No hay citas vinculadas a este prospecto. Si existe una cita en Agenda COMI, revisa su cliente y empresa de origen.</p>
    @endforelse
    @include('comercial.paginacion', ['pagina' => $citasProspecto])
    @if((int) Auth::user()->id_rol === 1)
        <a class="btn space" href="{{ route('comi.agenda.index') }}">Gestionar en Agenda COMI</a>
    @endif
</section>
