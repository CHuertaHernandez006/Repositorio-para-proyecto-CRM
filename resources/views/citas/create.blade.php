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
    ========================================================== */

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
    ========================================================== */

    .cita-card {
        background: #111827;

        border: 1px solid #1e293b;

        border-radius: 16px;

        overflow: hidden;

        box-shadow:
            0 12px 35px
            rgba(0, 0, 0, .18);
    }


    /* =========================================================
       SECCIONES
    ========================================================== */

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
    ========================================================== */

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

        border:
            1px solid
            #263449 !important;

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
        background:
            #0d1728 !important;

        border-color:
            #00c6ff !important;

        box-shadow:
            0 0 0 3px
            rgba(0, 198, 255, .07) !important;
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
       OPERARIO NO AUTORIZADO
    ========================================================== */

    .operario-warning {
        display: none;

        align-items: flex-start;

        gap: 10px;

        margin-top: 11px;

        padding: 12px 14px;

        border:
            1px solid
            rgba(248, 113, 113, .22);

        border-radius: 10px;

        background:
            rgba(248, 113, 113, .07);

        color: #fca5a5;

        font-size: 12px;

        line-height: 1.55;
    }

    .operario-warning.show {
        display: flex;
    }

    .operario-warning i {
        color: #f87171;

        font-size: 15px;

        margin-top: 1px;

        flex-shrink: 0;
    }

    .operario-warning strong {
        display: block;

        margin-bottom: 3px;

        color: #fecaca;

        font-size: 12px;
    }

    .operario-warning-text {
        color: #fca5a5;
    }

    .field-select.operario-bloqueado {
        border-color:
            rgba(248, 113, 113, .55) !important;

        box-shadow:
            0 0 0 3px
            rgba(248, 113, 113, .06) !important;
    }

    .operario-help {
        display: flex;

        align-items: flex-start;

        gap: 7px;

        margin-top: 9px;

        padding: 9px 11px;

        border:
            1px solid
            rgba(148, 163, 184, .07);

        border-radius: 8px;

        background:
            rgba(148, 163, 184, .025);

        color: #64748b;

        font-size: 10px;

        line-height: 1.5;
    }

    .operario-help i {
        flex-shrink: 0;

        margin-top: 1px;

        color: #00c6ff;
    }


    /* =========================================================
       ERROR
    ========================================================== */

    .validation-box {
        margin-bottom: 24px;

        padding: 15px 17px;

        background:
            rgba(239, 68, 68, .07);

        border:
            1px solid
            rgba(239, 68, 68, .20);

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
    ========================================================== */

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

        background:
            rgba(148, 163, 184, .05);
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

        cursor: pointer;
    }

    .btn-submit:hover {
        color: #06131a;

        background: #20cfff;

        transform: translateY(-1px);

        box-shadow:
            0 5px 18px
            rgba(0, 198, 255, .18);
    }

    .btn-submit:disabled {
        cursor: not-allowed;

        opacity: .38;

        transform: none;

        box-shadow: none;

        background: #64748b;

        color: #cbd5e1;
    }

    .btn-submit:disabled:hover {
        background: #64748b;

        transform: none;

        box-shadow: none;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

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
    ========================================================== --}}

    <div class="cita-top">

        <a
            href="{{ route('citas.index') }}"
            class="cita-back"
        >

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

                    Programa una cita y asígnala
                    al operario correspondiente.

                </p>

            </div>

        </div>

    </div>



    {{-- =========================================================
         ERRORES
    ========================================================== --}}

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
    ========================================================== --}}

    <div class="cita-card">

        <form
            method="POST"
            action="{{ route('citas.store') }}"
            id="formNuevaCita"
        >

            @csrf



            {{-- =================================================
                 PARTICIPANTES
            ================================================== --}}

            <div class="cita-section">

                <div class="section-heading">

                    <h2>
                        Participantes
                    </h2>

                    <p>

                        Selecciona el cliente y
                        el operario encargado.

                    </p>

                </div>


                <div class="row g-4">


                    {{-- =================================================
                         CLIENTE
                    ================================================== --}}

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
                                class="field-select
                                @error('id_cliente')
                                    is-invalid
                                @enderror"
                                required
                            >

                                <option value="">

                                    Selecciona un cliente

                                </option>


                                @foreach($clientes as $cliente)

                                    <option
                                        value="{{ $cliente->id_cliente }}"
                                        {{
                                            old('id_cliente')
                                            == $cliente->id_cliente
                                                ? 'selected'
                                                : ''
                                        }}
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



                    {{-- =================================================
                         OPERARIO
                    ================================================== --}}

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
                                class="field-select
                                @error('id_usuario')
                                    is-invalid
                                @enderror"
                                required
                            >

                                <option
                                    value=""
                                    data-autorizado="1"
                                    data-mensaje=""
                                >

                                    Selecciona un operario

                                </option>


                                @foreach($operarios as $operario)

                                    @php

                                        /*
                                        |--------------------------------------------------------------------------
                                        | ESTADO DE APROBACIÓN
                                        |--------------------------------------------------------------------------
                                        */

                                        $estadoAprobacion =
                                            $operario->estado_aprobacion;


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Compatibilidad con operarios antiguos:
                                        | NULL se considera aprobado si además está activo.
                                        |--------------------------------------------------------------------------
                                        */

                                        $aprobado =
                                            $estadoAprobacion === 'aprobado'
                                            ||
                                            $estadoAprobacion === null;


                                        $activo =
                                            (bool) $operario->estado;


                                        $puedeTrabajar =
                                            $aprobado
                                            &&
                                            $activo;


                                        /*
                                        |--------------------------------------------------------------------------
                                        | MENSAJE
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            $estadoAprobacion
                                            === 'rechazado'
                                        ) {

                                            $mensajeBloqueo =
                                                'Este operario no puede trabajar ni recibir citas porque su solicitud fue rechazada por el Super Administrador.';

                                        } elseif (
                                            $estadoAprobacion
                                            === 'pendiente'
                                        ) {

                                            $mensajeBloqueo =
                                                'Este operario no puede trabajar ni recibir citas porque todavía no ha sido autorizado por el Super Administrador.';

                                        } elseif (
                                            !$activo
                                        ) {

                                            $mensajeBloqueo =
                                                'Este operario se encuentra inactivo y no puede recibir citas.';

                                        } else {

                                            $mensajeBloqueo =
                                                '';

                                        }

                                    @endphp


                                    <option
                                        value="{{ $operario->id }}"

                                        data-autorizado="{{
                                            $puedeTrabajar
                                                ? '1'
                                                : '0'
                                        }}"

                                        data-mensaje="{{
                                            $mensajeBloqueo
                                        }}"

                                        {{
                                            old('id_usuario')
                                            == $operario->id
                                                ? 'selected'
                                                : ''
                                        }}
                                    >

                                        {{ $operario->name }}

                                        @if(!$puedeTrabajar)

                                            —
                                            @if(
                                                $estadoAprobacion
                                                === 'rechazado'
                                            )

                                                Rechazado

                                            @elseif(
                                                $estadoAprobacion
                                                === 'pendiente'
                                            )

                                                Pendiente de autorización

                                            @elseif(!$activo)

                                                Inactivo

                                            @else

                                                No autorizado

                                            @endif

                                        @endif

                                    </option>

                                @endforeach

                            </select>


                            {{-- =================================================
                                 MENSAJE DE OPERARIO BLOQUEADO
                            ================================================== --}}

                            <div
                                class="operario-warning"
                                id="operarioWarning"
                            >

                                <i class="bi bi-shield-x"></i>


                                <div>

                                    <strong>
                                        Operario no disponible
                                    </strong>


                                    <div
                                        class="operario-warning-text"
                                        id="operarioWarningText"
                                    >

                                        Este operario no puede
                                        trabajar porque no ha sido
                                        autorizado.

                                    </div>

                                </div>

                            </div>


                            <div class="operario-help">

                                <i class="bi bi-info-circle"></i>

                                <span>

                                    Solo los operarios aprobados y
                                    activos pueden recibir citas.
                                    Los operarios pendientes,
                                    rechazados o inactivos no pueden
                                    trabajar hasta que su situación
                                    sea regularizada.

                                </span>

                            </div>


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
            ================================================== --}}

            <div class="cita-section">

                <div class="section-heading">

                    <h2>
                        Programación
                    </h2>

                    <p>

                        Define cuándo está programada
                        la cita.

                    </p>

                </div>


                <div class="row g-4">


                    {{-- =================================================
                         FECHA DE LA CITA
                    ================================================== --}}

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
                                class="field-input
                                @error('fecha_hora_inicio')
                                    is-invalid
                                @enderror"
                                required
                            >


                            @error('fecha_hora_inicio')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>

                    </div>



                    {{-- =================================================
                         FINALIZACIÓN
                    ================================================== --}}

                    <div class="col-12 col-md-6">

                        <div class="field">

                            <label class="field-label">

                                <i class="bi bi-clock-history"></i>

                                Finalización

                            </label>


                            <div
                                class="field-help"
                                style="
                                    margin-top:0;
                                    font-size:13px;
                                    line-height:1.6;
                                "
                            >

                                La hora de finalización
                                se registrará cuando el
                                operario termine la llamada
                                con el cliente.

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- =================================================
                 DETALLES
            ================================================== --}}

            <div class="cita-section">

                <div class="section-heading">

                    <h2>
                        Detalles
                    </h2>

                    <p>

                        Añade información adicional
                        relacionada con la cita.

                    </p>

                </div>



                {{-- =================================================
                     MOTIVO
                ================================================== --}}

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
                        class="field-input
                        @error('motivo')
                            is-invalid
                        @enderror"
                        placeholder="Motivo de la cita"
                    >


                    @error('motivo')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>



                {{-- =================================================
                     OBSERVACIONES
                ================================================== --}}

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
                        class="field-textarea
                        @error('observaciones')
                            is-invalid
                        @enderror"
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
            ================================================== --}}

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
                        id="btnRegistrarCita"
                    >

                        <i class="bi bi-calendar-check"></i>

                        Registrar cita

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>



<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ELEMENTOS
        |--------------------------------------------------------------------------
        */

        const formulario =
            document.getElementById(
                'formNuevaCita'
            );

        const selectOperario =
            document.getElementById(
                'id_usuario'
            );

        const warning =
            document.getElementById(
                'operarioWarning'
            );

        const warningText =
            document.getElementById(
                'operarioWarningText'
            );

        const btnRegistrar =
            document.getElementById(
                'btnRegistrarCita'
            );


        /*
        |--------------------------------------------------------------------------
        | VALIDAR OPERARIO
        |--------------------------------------------------------------------------
        */

        function validarOperario() {

            if (!selectOperario) {
                return true;
            }


            const opcion =
                selectOperario.options[
                    selectOperario.selectedIndex
                ];


            /*
            |--------------------------------------------------------------------------
            | SIN SELECCIÓN
            |--------------------------------------------------------------------------
            */

            if (
                !opcion
                ||
                !opcion.value
            ) {

                if (warning) {

                    warning.classList.remove(
                        'show'
                    );
                }


                selectOperario
                    .classList
                    .remove(
                        'operario-bloqueado'
                    );


                if (btnRegistrar) {

                    btnRegistrar.disabled =
                        false;
                }


                return true;
            }


            /*
            |--------------------------------------------------------------------------
            | ESTADO
            |--------------------------------------------------------------------------
            */

            const autorizado =
                opcion.dataset.autorizado
                === '1';


            /*
            |--------------------------------------------------------------------------
            | AUTORIZADO
            |--------------------------------------------------------------------------
            */

            if (autorizado) {

                if (warning) {

                    warning.classList.remove(
                        'show'
                    );
                }


                selectOperario
                    .classList
                    .remove(
                        'operario-bloqueado'
                    );


                if (btnRegistrar) {

                    btnRegistrar.disabled =
                        false;
                }


                return true;
            }


            /*
            |--------------------------------------------------------------------------
            | NO AUTORIZADO
            |--------------------------------------------------------------------------
            */

            const mensaje =
                opcion.dataset.mensaje
                ||
                'Este operario no puede trabajar porque no ha sido autorizado.';


            if (warningText) {

                warningText.textContent =
                    mensaje;
            }


            if (warning) {

                warning.classList.add(
                    'show'
                );
            }


            selectOperario
                .classList
                .add(
                    'operario-bloqueado'
                );


            if (btnRegistrar) {

                btnRegistrar.disabled =
                    true;
            }


            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | CAMBIO DE OPERARIO
        |--------------------------------------------------------------------------
        */

        if (selectOperario) {

            selectOperario.addEventListener(
                'change',
                validarOperario
            );


            /*
            |--------------------------------------------------------------------------
            | Ejecutar al cargar por si old() dejó un operario seleccionado.
            |--------------------------------------------------------------------------
            */

            validarOperario();
        }


        /*
        |--------------------------------------------------------------------------
        | PROTECCIÓN AL ENVIAR
        |--------------------------------------------------------------------------
        */

        if (formulario) {

            formulario.addEventListener(
                'submit',
                function (event) {

                    if (
                        !validarOperario()
                    ) {

                        event.preventDefault();


                        if (warning) {

                            warning.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center',
                            });
                        }


                        return false;
                    }

                }
            );
        }

    }
);
</script>

@endsection