@extends('layouts.app')

@section('content')

<style>
    .cita-create {
        max-width: 1180px;
        margin: 0 auto;
        padding: 34px 24px 50px;
    }

    /* =========================================================
       ENCABEZADO
       ========================================================= */

    .cita-top {
        margin-bottom: 34px;
    }

    .cita-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #94a3b8;
        text-decoration: none;
        font-size: 14px;
        margin-bottom: 22px;
        transition: color .2s ease;
    }

    .cita-back:hover {
        color: #00c6ff;
    }

    .cita-title-row {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .cita-title-icon {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 198, 255, .08);
        border: 1px solid rgba(0, 198, 255, .16);
        color: #00c6ff;
        font-size: 23px;
    }

    .cita-title {
        margin: 0;
        color: #f8fafc;
        font-size: 27px;
        font-weight: 700;
        letter-spacing: -.4px;
    }

    .cita-subtitle {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    /* =========================================================
       CARD PRINCIPAL
       ========================================================= */

    .cita-card {
        background: #111827;
        border: 1px solid #1e293b;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 35px rgba(0, 0, 0, .18);
    }

    /* =========================================================
       SECCIONES
       ========================================================= */

    .cita-section {
        padding: 32px 34px;
    }

    .cita-section + .cita-section {
        border-top: 1px solid #1e293b;
    }

    .section-heading {
        margin-bottom: 26px;
    }

    .section-heading h2 {
        margin: 0;
        color: #f8fafc;
        font-size: 16px;
        font-weight: 650;
    }

    .section-heading p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    /* =========================================================
       CAMPOS
       ========================================================= */

    .field {
        margin-bottom: 24px;
    }

    .field:last-child {
        margin-bottom: 0;
    }

    .field-label {
        display: block;
        margin-bottom: 9px;
        color: #cbd5e1;
        font-size: 13px;
        font-weight: 600;
    }

    .field-label i {
        margin-right: 5px;
        color: #64748b;
    }

    .required {
        color: #00c6ff;
    }

    .field-input,
    .field-select,
    .field-textarea {
        width: 100%;
        color: #f8fafc !important;
        background: #0b1220 !important;
        border: 1px solid #263449 !important;
        border-radius: 9px !important;
        box-shadow: none !important;
        transition:
            border-color .2s ease,
            background-color .2s ease,
            box-shadow .2s ease;
    }

    .field-input,
    .field-select {
        height: 46px;
        padding: 0 13px;
    }

    .field-textarea {
        min-height: 140px;
        padding: 13px;
        resize: vertical;
    }

    .field-input::placeholder,
    .field-textarea::placeholder {
        color: #475569 !important;
    }

    .field-input:focus,
    .field-select:focus,
    .field-textarea:focus {
        background: #0d1728 !important;
        border-color: #00c6ff !important;
        box-shadow: 0 0 0 3px rgba(0, 198, 255, .07) !important;
    }

    .field-select option {
        background: #0b1220;
        color: #f8fafc;
    }

    .field-help {
        margin-top: 7px;
        color: #475569;
        font-size: 11px;
    }

    .invalid-feedback {
        font-size: 12px;
    }

    /* =========================================================
       ERROR
       ========================================================= */

    .validation-box {
        margin-bottom: 24px;
        padding: 15px 17px;
        background: rgba(239, 68, 68, .07);
        border: 1px solid rgba(239, 68, 68, .20);
        border-radius: 10px;
        color: #fca5a5;
        font-size: 13px;
    }

    .validation-box strong {
        color: #fecaca;
    }

    .validation-box ul {
        margin: 7px 0 0;
        padding-left: 20px;
    }

    /* =========================================================
       FOOTER
       ========================================================= */

    .cita-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 34px;
        background: #0d1524;
        border-top: 1px solid #1e293b;
    }

    .required-info {
        color: #475569;
        font-size: 12px;
    }

    .required-info span {
        color: #00c6ff;
    }

    .cita-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-cancel {
        height: 42px;
        padding: 0 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid #334155;
        border-radius: 8px;
        color: #94a3b8;
        background: transparent;
        text-decoration: none;
        font-size: 13px;
        transition: all .2s ease;
    }

    .btn-cancel:hover {
        color: #e2e8f0;
        border-color: #475569;
        background: rgba(148, 163, 184, .05);
    }

    .btn-submit {
        height: 42px;
        padding: 0 19px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: none;
        border-radius: 8px;
        color: #06131a;
        background: #00c6ff;
        font-size: 13px;
        font-weight: 700;
        transition: all .2s ease;
    }

    .btn-submit:hover {
        color: #06131a;
        background: #20cfff;
        transform: translateY(-1px);
        box-shadow: 0 5px 18px rgba(0, 198, 255, .18);
    }

    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 768px) {

        .cita-create {
            padding: 25px 16px 40px;
        }

        .cita-section {
            padding: 25px 20px;
        }

        .cita-footer {
            padding: 18px 20px;
            flex-direction: column;
            align-items: stretch;
        }

        .cita-actions {
            justify-content: flex-end;
        }

        .cita-title {
            font-size: 23px;
        }

        .cita-title-icon {
            width: 46px;
            height: 46px;
            min-width: 46px;
        }
    }
</style>


<div class="cita-create">

    {{-- =========================================================
         ENCABEZADO
         ========================================================= --}}

    <div class="cita-top">

        <a href="{{ route('citas.index') }}" class="cita-back">
            <i class="bi bi-arrow-left"></i>
            Volver a citas
        </a>

        <div class="cita-title-row">

            <div class="cita-title-icon">
                <i class="bi bi-calendar-plus"></i>
            </div>

            <div>

                <h1 class="cita-title">
                    Nueva cita
                </h1>

                <p class="cita-subtitle">
                    Programa una cita y asígnala al operario correspondiente.
                </p>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ERRORES
         ========================================================= --}}

    @if($errors->any())

        <div class="validation-box">

            <strong>
                <i class="bi bi-exclamation-circle me-1"></i>
                No se pudo registrar la cita.
            </strong>

            <ul>

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         FORMULARIO
         ========================================================= --}}

    <div class="cita-card">

        <form method="POST" action="{{ route('citas.store') }}">

            @csrf


            {{-- =================================================
                 PARTICIPANTES
                 ================================================= --}}

            <div class="cita-section">

                <div class="section-heading">

                    <h2>
                        Participantes
                    </h2>

                    <p>
                        Selecciona el cliente y el operario encargado.
                    </p>

                </div>


                <div class="row g-4">


                    {{-- CLIENTE --}}

                    <div class="col-12 col-md-6">

                        <div class="field">

                            <label
                                for="id_cliente"
                                class="field-label"
                            >
                                <i class="bi bi-person"></i>
                                Cliente
                                <span class="required">*</span>
                            </label>


                            <select
                                name="id_cliente"
                                id="id_cliente"
                                class="field-select @error('id_cliente') is-invalid @enderror"
                                required
                            >

                                <option value="">
                                    Selecciona un cliente
                                </option>

                                @foreach($clientes as $cliente)

                                    <option
                                        value="{{ $cliente->id_cliente }}"
                                        {{ old('id_cliente') == $cliente->id_cliente ? 'selected' : '' }}
                                    >

                                        {{ $cliente->nombre }}
                                        {{ $cliente->apellido_paterno }}

                                        @if($cliente->apellido_materno)
                                            {{ $cliente->apellido_materno }}
                                        @endif

                                        @if($cliente->empresa)
                                            — {{ $cliente->empresa }}
                                        @endif

                                    </option>

                                @endforeach

                            </select>


                            @error('id_cliente')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </div>


                    {{-- OPERARIO --}}

                    <div class="col-12 col-md-6">

                        <div class="field">

                            <label
                                for="id_usuario"
                                class="field-label"
                            >
                                <i class="bi bi-headset"></i>
                                Operario
                                <span class="required">*</span>
                            </label>


                            <select
                                name="id_usuario"
                                id="id_usuario"
                                class="field-select @error('id_usuario') is-invalid @enderror"
                                required
                            >

                                <option value="">
                                    Selecciona un operario
                                </option>

                                @foreach($operarios as $operario)

                                    <option
                                        value="{{ $operario->id }}"
                                        {{ old('id_usuario') == $operario->id ? 'selected' : '' }}
                                    >
                                        {{ $operario->name }}
                                    </option>

                                @endforeach

                            </select>


                            @error('id_usuario')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </div>

                </div>

            </div>


           {{-- =================================================
     PROGRAMACIÓN
     ================================================= --}}

<div class="cita-section">

    <div class="section-heading">

        <h2>
            Programación
        </h2>

        <p>
            Define cuándo está programada la cita.
        </p>

    </div>


    <div class="row g-4">

        {{-- FECHA DE LA CITA --}}

        <div class="col-12 col-md-6">

            <div class="field">

                <label
                    for="fecha_hora_inicio"
                    class="field-label"
                >
                    <i class="bi bi-calendar-event"></i>
                    Fecha y hora de la cita
                    <span class="required">*</span>
                </label>


                <input
                    type="datetime-local"
                    name="fecha_hora_inicio"
                    id="fecha_hora_inicio"
                    value="{{ old('fecha_hora_inicio') }}"
                    class="field-input @error('fecha_hora_inicio') is-invalid @enderror"
                    required
                >


                @error('fecha_hora_inicio')

                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>

                @enderror

            </div>

        </div>


        {{-- FINALIZACIÓN --}}

        <div class="col-12 col-md-6">

            <div class="field">

                <label
                    class="field-label"
                >
                    <i class="bi bi-clock-history"></i>
                    Finalización
                </label>

                <div
                    class="field-help"
                    style="
                        margin-top: 0;
                        font-size: 13px;
                        line-height: 1.6;
                    "
                >
                    La hora de finalización se registrará cuando
                    el operario termine la llamada con el cliente.
                </div>

            </div>

        </div>

    </div>

</div>
                    {{-- INFORMACIÓN DE FIN --}}

                    <div class="col-12 col-md-6">

                        <div class="field">

                            <label
                                class="field-label"
                            >
                                <i class="bi bi-clock-history"></i>
                                Finalización
                            </label>

                            <div class="field-help" style="margin-top: 0; font-size: 13px; line-height: 1.6;">

                                La hora de finalización se registrará cuando
                                el operario termine la llamada con el cliente.

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 DETALLES
                 ================================================= --}}

            <div class="cita-section">

                <div class="section-heading">

                    <h2>
                        Detalles
                    </h2>

                    <p>
                        Añade información adicional relacionada con la cita.
                    </p>

                </div>


                {{-- MOTIVO --}}

                <div class="field">

                    <label
                        for="motivo"
                        class="field-label"
                    >
                        <i class="bi bi-chat-left-text"></i>
                        Motivo
                    </label>


                    <input
                        type="text"
                        name="motivo"
                        id="motivo"
                        value="{{ old('motivo') }}"
                        maxlength="255"
                        class="field-input @error('motivo') is-invalid @enderror"
                        placeholder="Motivo de la cita"
                    >


                    @error('motivo')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- OBSERVACIONES --}}

                <div class="field">

                    <label
                        for="observaciones"
                        class="field-label"
                    >
                        <i class="bi bi-journal-text"></i>
                        Observaciones
                    </label>


                    <textarea
                        name="observaciones"
                        id="observaciones"
                        maxlength="1000"
                        class="field-textarea @error('observaciones') is-invalid @enderror"
                        placeholder="Agrega observaciones, acuerdos o información importante sobre la cita..."
                    >{{ old('observaciones') }}</textarea>


                    <div class="field-help">
                        Máximo 1000 caracteres.
                    </div>


                    @error('observaciones')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            </div>


            {{-- =================================================
                 ACCIONES
                 ================================================= --}}

            <div class="cita-footer">

                <div class="required-info">

                    <span>*</span>
                    Campos obligatorios

                </div>


                <div class="cita-actions">

                    <a
                        href="{{ route('citas.index') }}"
                        class="btn-cancel"
                    >
                        <i class="bi bi-x-lg"></i>
                        Cancelar
                    </a>


                    <button
                        type="submit"
                        class="btn-submit"
                    >
                        <i class="bi bi-calendar-check"></i>
                        Registrar cita
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection