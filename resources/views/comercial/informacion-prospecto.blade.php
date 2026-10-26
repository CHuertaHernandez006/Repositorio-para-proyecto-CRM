<section class="card">
    <div class="head" style="margin-bottom:12px">
        <h2>Información comercial</h2>
        <a href="{{ route('comercial.prospectos.informacion.edit', $prospecto->id) }}">Editar información →</a>
    </div>
    @if($ficha['manual'])
        <p class="muted">Información revisada por el equipo comercial.</p>
        <p class="note">
            Última edición de información:
            <strong>{{ \Carbon\Carbon::parse($ficha['manual']->updated_at)->timezone('America/Mexico_City')->format('Y-m-d H:i:s') }}</strong>
            · Ciudad de México
            @if($ficha['editor'])
                <br>Por {{ $ficha['editor']->nombre }} {{ $ficha['editor']->apellido_paterno }}
            @endif
        </p>
    @else
        <p class="muted">Datos capturados por COMI. Todavía no hay una revisión manual guardada.</p>
    @endif
    <div class="grid space">
        @foreach(\App\Support\FichaProspecto::NECESIDADES as $campo => $etiqueta)
            <div><small>{{ $etiqueta }}</small><p>{{ $ficha['necesidades'][$campo] ?? 'No proporcionado' }}</p></div>
        @endforeach
    </div>
    @if($ficha['original'])
        <details class="space">
            <summary>Ver datos originales de la última calificación COMI</summary>
            <p class="muted">Capturados el {{ \Carbon\Carbon::parse($ficha['original']->fecha_calificacion)->timezone('America/Mexico_City')->format('Y-m-d H:i:s') }} · Ciudad de México.</p>
            <div class="grid space">
                @foreach(\App\Support\FichaProspecto::NECESIDADES as $campo => $etiqueta)
                    <div><small>{{ $etiqueta }}</small><p>{{ $ficha['original']->$campo ?? 'No proporcionado' }}</p></div>
                @endforeach
            </div>
        </details>
    @endif
    <p class="note">COMI recopila información. La cotización y el contacto comercial los realiza el asesor.</p>
</section>
