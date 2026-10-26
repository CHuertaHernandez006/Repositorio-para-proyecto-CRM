@if($p->cita_fecha)
    <strong>{{ \Carbon\Carbon::parse($p->cita_fecha)->timezone('America/Mexico_City')->format('d/m/Y') }}</strong>
    <small>{{ \Carbon\Carbon::parse($p->cita_fecha)->timezone('America/Mexico_City')->format('H:i') }} · Ciudad de México</small>
    <small><span class="badge">{{ \App\Support\CitasProspecto::ESTADOS[$p->cita_estado] ?? $p->cita_estado }}</span></small>
    @if($p->cita_pasada)
        <small>Horario pasado · no implica que haya sido atendida</small>
    @endif
    @if($p->cita_asesor)
        <small>Asesor de la cita: {{ $p->cita_asesor }}</small>
    @endif
    @if($p->cita_fuente === 'anterior')
        <small>Registro de agenda anterior</small>
    @endif
    @if((int) $p->cita_total > 1)
        <small>{{ $p->cita_total }} citas registradas · consulta el detalle</small>
    @endif
@elseif($p->cita_solicitada_comi)
    <strong>Solicitud registrada</strong>
    <small>Sin fecha vinculada al prospecto</small>
@else
    <span class="muted">Sin cita ni solicitud vinculada</span>
@endif
