@extends('comercial.base')
@section('commercial-title', 'Editar información del prospecto')
@section('commercial-subtitle', 'Completa y corrige los datos que confirmes durante la atención comercial.')
@section('commercial-content')
    <div class="head">
        <a href="{{ route('comercial.prospectos.show', $prospecto->id) }}">← Volver al prospecto</a>
        <span class="badge">{{ \App\Support\Comercial::ESTADOS[$prospecto->estado] }}</span>
    </div>
    <form method="POST" action="{{ route('comercial.prospectos.informacion.update', $prospecto->id) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="version" value="{{ old('version', $version) }}">
        <section class="card">
            <h2>Datos de contacto</h2>
            <p class="muted">Los campos con * son obligatorios. Estos datos pertenecen al contacto original y se reflejarán también donde se consulte ese cliente.</p>
            <div class="grid space">
                @foreach(\App\Support\FichaProspecto::CONTACTO as $campo => $etiqueta)
                    <div class="field">
                        <label for="ficha-{{ $campo }}">{{ $etiqueta }}{{ in_array($campo, ['nombre', 'apellido_paterno'], true) ? ' *' : '' }}</label>
                        <input id="ficha-{{ $campo }}" name="{{ $campo }}"
                               type="{{ $campo === 'correo' ? 'email' : (str_starts_with($campo, 'telefono') ? 'tel' : 'text') }}"
                               value="{{ old($campo, $ficha['cliente']->$campo) }}"
                               maxlength="{{ in_array($campo, ['organizacion', 'correo'], true) ? 150 : (str_starts_with($campo, 'telefono') ? 16 : 100) }}"
                               @required(in_array($campo, ['nombre', 'apellido_paterno'], true))
                               @if(str_starts_with($campo, 'telefono')) placeholder="Ej. +525512345678" @endif>
                        @error($campo)<small class="danger">{{ $message }}</small>@enderror
                    </div>
                @endforeach
            </div>
            <p class="muted">Teléfonos: entre 7 y 15 dígitos, con + opcional y sin espacios. Correo y teléfonos pueden quedar vacíos si no se conocen.</p>
        </section>
        <section class="card">
            <h2>Necesidades y datos comerciales</h2>
            <p class="muted">Se guardará la revisión del equipo comercial; la calificación original de COMI seguirá disponible en la ficha.</p>
            <div class="grid space">
                @foreach(\App\Support\FichaProspecto::NECESIDADES as $campo => $etiqueta)
                    <div class="field" @if($campo === 'objetivo_principal') style="grid-column:1/-1" @endif>
                        <label for="ficha-{{ $campo }}">{{ $etiqueta }}</label>
                        @if($campo === 'objetivo_principal')
                            <textarea id="ficha-{{ $campo }}" name="{{ $campo }}" maxlength="5000">{{ old($campo, $ficha['necesidades'][$campo]) }}</textarea>
                        @else
                            <input id="ficha-{{ $campo }}" name="{{ $campo }}" type="text"
                                   maxlength="{{ $campo === 'giro' ? 150 : 100 }}"
                                   value="{{ old($campo, $ficha['necesidades'][$campo]) }}">
                        @endif
                        @error($campo)<small class="danger">{{ $message }}</small>@enderror
                    </div>
                @endforeach
            </div>
            <div class="actions" style="justify-content:flex-end;border-top:1px solid #334155;padding-top:20px">
                <a class="btn" href="{{ route('comercial.prospectos.show', $prospecto->id) }}">Cancelar</a>
                <button class="btn primary" type="submit">Guardar información</button>
            </div>
        </section>
    </form>
@endsection
