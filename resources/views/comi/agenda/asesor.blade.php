<div style="min-width:220px;display:grid;gap:10px;">
    @if($cita->id_asesor)
        <span class="ca-primary">Asesor: {{ $cita->asesor_interno_nombre }}</span>
        <span class="ca-secondary">{{ $cita->correo_enviado_at ? 'Confirmación enviada por correo' : 'Correo pendiente de envío' }}</span>
        @if($esGestorInterno && !$cita->correo_enviado_at && $cita->estado === 'confirmada')
            <form method="POST" action="{{ route('comi.agenda.correo', $cita->id_cita) }}">
                @csrf
                <button class="ca-button ca-button-secondary" type="submit">Reintentar correo</button>
            </form>
        @endif
    @elseif($esGestorInterno && in_array($cita->estado, ['pendiente_aprobacion', 'confirmada'], true))
        <form method="POST" action="{{ route('comi.agenda.confirmar-asesor', $cita->id_cita) }}" style="display:grid;gap:8px;">
            @csrf
            <label for="asesor-{{ $cita->id_cita }}">Asesor interno</label>
            <select id="asesor-{{ $cita->id_cita }}" name="id_asesor" class="ca-select" required>
                <option value="">Selecciona un asesor</option>
                @foreach($asesoresInternos as $asesorInterno)
                    <option value="{{ $asesorInterno->id_usuario }}">{{ $asesorInterno->nombre }} {{ $asesorInterno->apellido_paterno }}</option>
                @endforeach
            </select>
            <button class="ca-button ca-button-confirm" type="submit">{{ $cita->estado === 'confirmada' ? 'Asignar asesor y enviar correo' : 'Confirmar y enviar correo' }}</button>
            @if($asesoresInternos->isEmpty())<span class="ca-secondary">El Súper Admin debe crear un asesor activo en Equipo comercial.</span>@endif
        </form>
    @else
        <span class="ca-secondary">Sin asesor asignado</span>
    @endif
    @if($esGestorInterno && $cita->estado === 'pendiente_aprobacion')
        <div class="ca-actions">
            <form method="POST" action="{{ route('comi.agenda.estado', $cita->id_cita) }}" onsubmit="return confirm('¿Rechazar esta cita?');">
                @csrf @method('PATCH')
                <input type="hidden" name="estado" value="rechazada">
                <button class="ca-button ca-button-reject" type="submit">Rechazar</button>
            </form>
            <a class="ca-button ca-button-reprogram" href="{{ route('comi.agenda.reprogramar.form', $cita->id_cita) }}">Reprogramar</a>
        </div>
    @endif
</div>
