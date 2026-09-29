@extends('layouts.app')

@section('title', 'Nueva campaña - CRM')
@section('header-title', 'Nueva Campaña')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | ESTADOS AUTOMÁTICOS
    |--------------------------------------------------------------------------
    |
    | Buscamos los IDs directamente en el catálogo.
    |
    | Para "En proceso" permitimos también el nombre "Activa"
    | porque actualmente tu catálogo todavía puede tener ese nombre.
    |
    */

    $estadoPlaneada = $estados->first(function ($estado) {
        return mb_strtolower(trim($estado->nombre)) === 'planeada';
    });

    $estadoEnProceso = $estados->first(function ($estado) {
        return in_array(
            mb_strtolower(trim($estado->nombre)),
            [
                'en proceso',
                'activa',
            ]
        );
    });

    $estadoFinalizada = $estados->first(function ($estado) {
        return mb_strtolower(trim($estado->nombre)) === 'finalizada';
    });

    $estadoPlaneadaId =
        $estadoPlaneada?->id_estado_campana;

    $estadoEnProcesoId =
        $estadoEnProceso?->id_estado_campana;

    $estadoFinalizadaId =
        $estadoFinalizada?->id_estado_campana;
@endphp


<style>
    .campaign-form-page {
        max-width: 1000px;
        margin: 0 auto;
        color: #e8eef7;
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .form-page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 25px;
    }

    .page-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;

        color: #35c6ff;

        font-size: 10px;
        font-weight: 750;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .eyebrow-dot {
        width: 6px;
        height: 6px;

        border-radius: 50%;

        background: #35c6ff;

        box-shadow:
            0 0 9px
            rgba(53, 198, 255, .6);
    }

    .page-title {
        margin: 0;

        color: #ffffff;

        font-size: 30px;
        font-weight: 750;

        letter-spacing: -.03em;
    }

    .page-description {
        max-width: 680px;

        margin: 8px 0 0;

        color: #7e8da3;

        font-size: 13px;
        line-height: 1.6;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        height: 40px;

        padding: 0 14px;

        border:
            1px solid
            rgba(148, 163, 184, .15);

        border-radius: 9px;

        background:
            rgba(148, 163, 184, .05);

        color: #9aa8ba;

        text-decoration: none;

        font-size: 11px;
        font-weight: 700;

        transition: .2s ease;
    }

    .btn-back:hover {
        background:
            rgba(148, 163, 184, .10);

        color: #e2e8f0;
    }

    .btn-back svg {
        width: 14px;
        height: 14px;
    }


    /* =========================================================
       ALERTA
    ========================================================== */

    .error-alert {
        margin-bottom: 18px;

        padding: 14px 16px;

        border:
            1px solid
            rgba(248, 113, 113, .16);

        border-radius: 11px;

        background:
            rgba(248, 113, 113, .06);

        color: #fca5a5;

        font-size: 11px;
        line-height: 1.6;
    }

    .error-alert ul {
        margin: 6px 0 0;

        padding-left: 18px;
    }


    /* =========================================================
       CARD
    ========================================================== */

    .form-card {
        overflow: hidden;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 16px;

        background: #111c30;

        box-shadow:
            0 18px 40px
            rgba(0, 0, 0, .08);
    }

    .form-card-header {
        display: flex;
        align-items: center;

        gap: 12px;

        padding: 19px 21px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .05);
    }

    .form-card-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 39px;
        height: 39px;

        flex-shrink: 0;

        border:
            1px solid
            rgba(53, 198, 255, .18);

        border-radius: 10px;

        background:
            rgba(53, 198, 255, .08);

        color: #35c6ff;
    }

    .form-card-icon svg {
        width: 18px;
        height: 18px;
    }

    .form-card-title {
        color: #ffffff;

        font-size: 14px;
        font-weight: 750;
    }

    .form-card-description {
        margin-top: 3px;

        color: #617087;

        font-size: 10px;
    }


    /* =========================================================
       FORM
    ========================================================== */

    .campaign-form {
        display: grid;

        grid-template-columns:
            1fr 1fr;

        gap: 18px;

        padding: 22px;
    }

    .form-group {
        min-width: 0;
    }

    .form-group.full {
        grid-column:
            1 / -1;
    }

    .form-label {
        display: block;

        margin-bottom: 7px;

        color: #8090a7;

        font-size: 9px;
        font-weight: 750;

        letter-spacing: .08em;

        text-transform: uppercase;
    }

    .required {
        color: #35c6ff;
    }

    .form-control {
        width: 100%;

        min-height: 42px;

        padding: 0 12px;

        border:
            1px solid
            rgba(255, 255, 255, .075);

        border-radius: 9px;

        outline: none;

        background: #091426;

        color: #edf4fc;

        font-size: 12px;

        box-sizing: border-box;

        transition: .2s ease;

        color-scheme: dark;
    }

    textarea.form-control {
        min-height: 110px;

        padding: 12px;

        resize: vertical;

        line-height: 1.55;
    }

    .form-control:focus {
        border-color:
            rgba(53, 198, 255, .38);

        box-shadow:
            0 0 0 3px
            rgba(53, 198, 255, .045);
    }

    .form-control::placeholder {
        color: #526278;
    }

    .form-hint {
        margin-top: 5px;

        color: #536278;

        font-size: 9px;
        line-height: 1.45;
    }

    .input-error {
        margin-top: 5px;

        color: #fca5a5;

        font-size: 9px;
    }


    /* =========================================================
       BLOQUE DE FECHAS
    ========================================================== */

    .date-section-title {
        display: flex;
        align-items: center;
        gap: 6px;

        grid-column:
            1 / -1;

        margin-top: 2px;

        color: #7d8da3;

        font-size: 9px;
        font-weight: 750;

        letter-spacing: .08em;

        text-transform: uppercase;
    }

    .date-section-title svg {
        width: 12px;
        height: 12px;

        color: #35c6ff;
    }


    /* =========================================================
       ESTADO AUTOMÁTICO
    ========================================================== */

    .automatic-state-wrapper {
        grid-column:
            1 / -1;
    }

    .automatic-state-label {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 12px;

        margin-bottom: 7px;
    }

    .automatic-state-label-left {
        display: flex;
        align-items: center;

        gap: 6px;

        color: #8090a7;

        font-size: 9px;
        font-weight: 750;

        letter-spacing: .08em;

        text-transform: uppercase;
    }

    .automatic-state-label-left svg {
        width: 12px;
        height: 12px;
    }

    .automatic-label-badge {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        padding: 4px 7px;

        border:
            1px solid
            rgba(53, 198, 255, .11);

        border-radius: 999px;

        background:
            rgba(53, 198, 255, .04);

        color: #65d2ff;

        font-size: 8px;
        font-weight: 750;

        letter-spacing: .04em;

        text-transform: none;
    }

    .automatic-label-badge svg {
        width: 9px;
        height: 9px;
    }

    .automatic-state {
        position: relative;

        display: flex;
        align-items: center;

        gap: 14px;

        min-height: 80px;

        padding: 15px 17px;

        border:
            1px solid
            rgba(148, 163, 184, .12);

        border-radius: 11px;

        background:
            rgba(7, 17, 31, .27);

        overflow: hidden;

        transition: .2s ease;
    }

    .automatic-state::after {
        content: "";

        position: absolute;

        right: -40px;
        bottom: -55px;

        width: 115px;
        height: 115px;

        border-radius: 50%;

        opacity: .5;

        pointer-events: none;
    }

    .automatic-state-icon {
        position: relative;

        z-index: 2;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 43px;
        height: 43px;

        flex: 0 0 43px;

        border:
            1px solid
            rgba(148, 163, 184, .13);

        border-radius: 10px;

        background:
            rgba(148, 163, 184, .06);

        color: #94a3b8;
    }

    .automatic-state-icon svg {
        width: 18px;
        height: 18px;
    }

    .automatic-state-content {
        position: relative;

        z-index: 2;

        flex: 1;

        min-width: 0;
    }

    .automatic-state-name {
        color: #e2e8f0;

        font-size: 13px;
        font-weight: 750;
    }

    .automatic-state-description {
        margin-top: 4px;

        color: #64748b;

        font-size: 9px;
        line-height: 1.55;
    }


    /* PENDIENTE */

    .automatic-state.state-pending {
        border-color:
            rgba(148, 163, 184, .12);
    }

    .automatic-state.state-pending::after {
        background:
            rgba(148, 163, 184, .04);
    }


    /* PLANEADA */

    .automatic-state.state-planned {
        border-color:
            rgba(167, 139, 250, .17);

        background:
            rgba(167, 139, 250, .035);
    }

    .automatic-state.state-planned::after {
        background:
            rgba(167, 139, 250, .08);
    }

    .automatic-state.state-planned
    .automatic-state-icon {
        border-color:
            rgba(167, 139, 250, .20);

        background:
            rgba(167, 139, 250, .08);

        color: #c4b5fd;
    }

    .automatic-state.state-planned
    .automatic-state-name {
        color: #c4b5fd;
    }


    /* EN PROCESO */

    .automatic-state.state-running {
        border-color:
            rgba(52, 211, 153, .18);

        background:
            rgba(52, 211, 153, .035);
    }

    .automatic-state.state-running::after {
        background:
            rgba(52, 211, 153, .08);
    }

    .automatic-state.state-running
    .automatic-state-icon {
        border-color:
            rgba(52, 211, 153, .20);

        background:
            rgba(52, 211, 153, .08);

        color: #6ee7b7;
    }

    .automatic-state.state-running
    .automatic-state-name {
        color: #6ee7b7;
    }


    /* FINALIZADA */

    .automatic-state.state-finished {
        border-color:
            rgba(148, 163, 184, .15);

        background:
            rgba(148, 163, 184, .025);
    }

    .automatic-state.state-finished::after {
        background:
            rgba(148, 163, 184, .06);
    }

    .automatic-state.state-finished
    .automatic-state-icon {
        color: #94a3b8;
    }


    /* ERROR */

    .automatic-state.state-error {
        border-color:
            rgba(248, 113, 113, .18);

        background:
            rgba(248, 113, 113, .035);
    }

    .automatic-state.state-error::after {
        background:
            rgba(248, 113, 113, .07);
    }

    .automatic-state.state-error
    .automatic-state-icon {
        border-color:
            rgba(248, 113, 113, .20);

        background:
            rgba(248, 113, 113, .07);

        color: #fca5a5;
    }

    .automatic-state.state-error
    .automatic-state-name {
        color: #fca5a5;
    }


    /* =========================================================
       ACCIONES
    ========================================================== */

    .form-actions {
        display: flex;

        justify-content: flex-end;

        gap: 9px;

        grid-column:
            1 / -1;

        margin-top: 4px;

        padding-top: 18px;

        border-top:
            1px solid
            rgba(255, 255, 255, .05);
    }

    .btn-cancel,
    .btn-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        min-height: 40px;

        padding: 0 16px;

        border-radius: 9px;

        font-size: 11px;
        font-weight: 800;

        text-decoration: none;

        cursor: pointer;

        transition: .2s ease;
    }

    .btn-cancel {
        border:
            1px solid
            rgba(148, 163, 184, .14);

        background:
            rgba(148, 163, 184, .05);

        color: #94a3b8;
    }

    .btn-cancel:hover {
        background:
            rgba(148, 163, 184, .10);

        color: #d5dde7;
    }

    .btn-save {
        border:
            1px solid
            rgba(53, 198, 255, .30);

        background: #35c6ff;

        color: #07111f;
    }

    .btn-save:hover:not(:disabled) {
        background: #64d4ff;

        transform:
            translateY(-1px);

        box-shadow:
            0 0 17px
            rgba(53, 198, 255, .14);
    }

    .btn-save:disabled {
        opacity: .35;

        cursor: not-allowed;

        transform: none;

        box-shadow: none;
    }

    .btn-save svg,
    .btn-cancel svg {
        width: 14px;
        height: 14px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 700px) {

        .form-page-header {
            align-items:
                flex-start;

            flex-direction:
                column;
        }

        .btn-back {
            width: 100%;
        }

        .campaign-form {
            grid-template-columns:
                1fr;
        }

        .form-group.full,
        .date-section-title,
        .automatic-state-wrapper,
        .form-actions {
            grid-column: 1;
        }

        .form-actions {
            flex-direction:
                column-reverse;
        }

        .btn-cancel,
        .btn-save {
            width: 100%;
        }

        .automatic-state {
            align-items:
                flex-start;
        }
    }
</style>


<div class="campaign-form-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="form-page-header">

        <div>

            <div class="page-eyebrow">

                <span class="eyebrow-dot"></span>

                Gestión de campañas

            </div>


            <h1 class="page-title">
                Nueva campaña
            </h1>


            <p class="page-description">

                Registra la información general de la campaña.
                El estado se determinará automáticamente
                de acuerdo con las fechas de inicio y finalización.

            </p>

        </div>


        <a
            href="{{ route('campanas.index') }}"
            class="btn-back"
        >

            <i data-lucide="arrow-left"></i>

            Volver

        </a>

    </div>


    {{-- =========================================================
         ERRORES
    ========================================================== --}}

    @if($errors->any())

        <div class="error-alert">

            <strong>
                Revisa la información del formulario.
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

    <div class="form-card">


        {{-- HEADER CARD --}}

        <div class="form-card-header">

            <div class="form-card-icon">

                <i data-lucide="megaphone"></i>

            </div>


            <div>

                <div class="form-card-title">
                    Información de campaña
                </div>


                <div class="form-card-description">

                    Define el nombre, objetivo y periodo
                    de ejecución.

                </div>

            </div>

        </div>


        <form
            action="{{ route('campanas.store') }}"
            method="POST"
            class="campaign-form"
            id="campaignForm"
        >

            @csrf


            {{-- =================================================
                 ESTADO OCULTO
            ================================================== --}}

            <input
                type="hidden"
                name="id_estado_campana"
                id="id_estado_campana"
                value="{{ old('id_estado_campana') }}"
            >


            {{-- =================================================
                 NOMBRE
            ================================================== --}}

            <div class="form-group full">

                <label class="form-label">

                    Nombre de la campaña

                    <span class="required">*</span>

                </label>


                <input
                    type="text"
                    name="nombre"
                    class="form-control"
                    maxlength="150"
                    value="{{ old('nombre') }}"
                    placeholder="Ej. Seguimiento de prospectos septiembre"
                    required
                >


                @error('nombre')

                    <div class="input-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- =================================================
                 PERIODO
            ================================================== --}}

            <div class="date-section-title">

                <i data-lucide="calendar-range"></i>

                Periodo de la campaña

            </div>


            {{-- FECHA INICIO --}}

            <div class="form-group">

                <label class="form-label">

                    Fecha de inicio

                    <span class="required">*</span>

                </label>


                <input
                    type="date"
                    name="fecha_inicio"
                    id="fecha_inicio"
                    class="form-control"
                    value="{{ old('fecha_inicio') }}"
                    required
                >


                <div class="form-hint">

                    Día en que comenzará
                    la ejecución de la campaña.

                </div>


                @error('fecha_inicio')

                    <div class="input-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- FECHA FIN --}}

            <div class="form-group">

                <label class="form-label">

                    Fecha de finalización

                    <span class="required">*</span>

                </label>


                <input
                    type="date"
                    name="fecha_fin"
                    id="fecha_fin"
                    class="form-control"
                    value="{{ old('fecha_fin') }}"
                    required
                >


                <div class="form-hint">

                    Último día en que la campaña
                    permanecerá en ejecución.

                </div>


                @error('fecha_fin')

                    <div class="input-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- =================================================
                 ESTADO AUTOMÁTICO
            ================================================== --}}

            <div class="automatic-state-wrapper">

                <div class="automatic-state-label">

                    <div class="automatic-state-label-left">

                        <i data-lucide="activity"></i>

                        Estado de la campaña

                    </div>


                    <span class="automatic-label-badge">

                        <i data-lucide="wand-sparkles"></i>

                        Automático

                    </span>

                </div>


                <div
                    class="automatic-state state-pending"
                    id="automaticState"
                >

                    <div
                        class="automatic-state-icon"
                        id="automaticStateIcon"
                    >

                        <i data-lucide="calendar-clock"></i>

                    </div>


                    <div class="automatic-state-content">

                        <div
                            class="automatic-state-name"
                            id="automaticStateName"
                        >
                            Estado pendiente
                        </div>


                        <div
                            class="automatic-state-description"
                            id="automaticStateDescription"
                        >

                            Selecciona las fechas de inicio y finalización
                            para determinar automáticamente
                            el estado de la campaña.

                        </div>

                    </div>

                </div>


                @error('id_estado_campana')

                    <div class="input-error">
                        {{ $message }}
                    </div>

                @enderror

            </div>


            {{-- =================================================
                 DESCRIPCIÓN
            ================================================== --}}

            <div class="form-group full">

                <label class="form-label">
                    Descripción
                </label>


                <textarea
                    name="descripcion"
                    class="form-control"
                    maxlength="2000"
                    placeholder="Describe brevemente en qué consiste la campaña..."
                >{{ old('descripcion') }}</textarea>


                <div class="form-hint">

                    Información general sobre
                    la campaña.

                </div>

            </div>


            {{-- =================================================
                 OBJETIVO
            ================================================== --}}

            <div class="form-group full">

                <label class="form-label">
                    Objetivo
                </label>


                <textarea
                    name="objetivo"
                    class="form-control"
                    maxlength="2000"
                    placeholder="Ej. Contactar prospectos interesados para dar seguimiento..."
                >{{ old('objetivo') }}</textarea>


                <div class="form-hint">

                    Define qué se pretende lograr
                    durante la campaña.

                </div>

            </div>


            {{-- =================================================
                 ACCIONES
            ================================================== --}}

            <div class="form-actions">

                <a
                    href="{{ route('campanas.index') }}"
                    class="btn-cancel"
                >

                    Cancelar

                </a>


                <button
                    type="submit"
                    class="btn-save"
                    id="btnSave"
                >

                    <i data-lucide="save"></i>

                    Crear campaña

                </button>

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
        | LUCIDE
        |--------------------------------------------------------------------------
        */

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }


        /*
        |--------------------------------------------------------------------------
        | ELEMENTOS
        |--------------------------------------------------------------------------
        */

        const inicio =
            document.getElementById(
                'fecha_inicio'
            );

        const fin =
            document.getElementById(
                'fecha_fin'
            );

        const estadoInput =
            document.getElementById(
                'id_estado_campana'
            );

        const estadoBox =
            document.getElementById(
                'automaticState'
            );

        const estadoIcon =
            document.getElementById(
                'automaticStateIcon'
            );

        const estadoName =
            document.getElementById(
                'automaticStateName'
            );

        const estadoDescription =
            document.getElementById(
                'automaticStateDescription'
            );

        const btnSave =
            document.getElementById(
                'btnSave'
            );


        /*
        |--------------------------------------------------------------------------
        | IDS DE ESTADOS
        |--------------------------------------------------------------------------
        */

        const ESTADO_PLANEADA =
            @json($estadoPlaneadaId);

        const ESTADO_EN_PROCESO =
            @json($estadoEnProcesoId);

        const ESTADO_FINALIZADA =
            @json($estadoFinalizadaId);


        /*
        |--------------------------------------------------------------------------
        | FECHA LOCAL DE HOY
        |--------------------------------------------------------------------------
        |
        | Se construye manualmente para evitar problemas
        | de zona horaria con new Date('YYYY-MM-DD').
        |
        */

        function obtenerHoy() {

            const ahora =
                new Date();

            const anio =
                ahora.getFullYear();

            const mes =
                String(
                    ahora.getMonth() + 1
                ).padStart(
                    2,
                    '0'
                );

            const dia =
                String(
                    ahora.getDate()
                ).padStart(
                    2,
                    '0'
                );


            return (
                anio +
                '-' +
                mes +
                '-' +
                dia
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FORMATEAR FECHA
        |--------------------------------------------------------------------------
        */

        function formatearFecha(
            fecha
        ) {

            if (!fecha) {
                return '';
            }


            const partes =
                fecha.split('-');


            if (partes.length !== 3) {
                return fecha;
            }


            return (
                partes[2] +
                '/' +
                partes[1] +
                '/' +
                partes[0]
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CAMBIAR PRESENTACIÓN
        |--------------------------------------------------------------------------
        */

        function mostrarEstado(
            tipo,
            nombre,
            descripcion,
            icono,
            idEstado
        ) {

            estadoBox.className =
                'automatic-state ' +
                tipo;


            estadoName.textContent =
                nombre;


            estadoDescription.textContent =
                descripcion;


            estadoIcon.innerHTML =
                '<i data-lucide="' +
                icono +
                '"></i>';


            estadoInput.value =
                idEstado || '';


            /*
            |--------------------------------------------------------------------------
            | Si no tenemos un ID válido, no permitimos guardar.
            |--------------------------------------------------------------------------
            */

            btnSave.disabled =
                !idEstado;


            if (
                typeof lucide !==
                'undefined'
            ) {

                lucide.createIcons();

            }

        }


        /*
        |--------------------------------------------------------------------------
        | CALCULAR ESTADO
        |--------------------------------------------------------------------------
        */

        function calcularEstado() {

            const fechaInicio =
                inicio.value;

            const fechaFin =
                fin.value;

            const hoy =
                obtenerHoy();


            /*
            |--------------------------------------------------------------------------
            | SIN FECHAS
            |--------------------------------------------------------------------------
            */

            if (
                !fechaInicio ||
                !fechaFin
            ) {

                mostrarEstado(
                    'state-pending',
                    'Estado pendiente',
                    'Selecciona las fechas de inicio y finalización para determinar automáticamente el estado de la campaña.',
                    'calendar-clock',
                    null
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | RANGO INVÁLIDO
            |--------------------------------------------------------------------------
            */

            if (
                fechaFin <
                fechaInicio
            ) {

                mostrarEstado(
                    'state-error',
                    'Periodo inválido',
                    'La fecha de finalización no puede ser anterior a la fecha de inicio.',
                    'triangle-alert',
                    null
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | PLANEADA
            |--------------------------------------------------------------------------
            |
            | Hoy todavía es anterior a la fecha de inicio.
            |
            */

            if (
                hoy <
                fechaInicio
            ) {

                mostrarEstado(
                    'state-planned',
                    'Planeada',
                    'La campaña comenzará el ' +
                    formatearFecha(
                        fechaInicio
                    ) +
                    '.',
                    'calendar-clock',
                    ESTADO_PLANEADA
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | FINALIZADA
            |--------------------------------------------------------------------------
            |
            | La fecha final ya pasó.
            |
            */

            if (
                hoy >
                fechaFin
            ) {

                mostrarEstado(
                    'state-finished',
                    'Finalizada',
                    'El periodo de esta campaña concluyó el ' +
                    formatearFecha(
                        fechaFin
                    ) +
                    '.',
                    'circle-check',
                    ESTADO_FINALIZADA
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | EN PROCESO
            |--------------------------------------------------------------------------
            |
            | fecha_inicio <= hoy <= fecha_fin
            |
            */

            mostrarEstado(
                'state-running',
                'En proceso',
                'La fecha actual se encuentra dentro del periodo de ejecución de la campaña.',
                'radio-tower',
                ESTADO_EN_PROCESO
            );

        }


        /*
        |--------------------------------------------------------------------------
        | RELACIÓN ENTRE FECHAS
        |--------------------------------------------------------------------------
        */

        function actualizarFechaMinima() {

            if (
                inicio.value
            ) {

                fin.min =
                    inicio.value;

            } else {

                fin.removeAttribute(
                    'min'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Si ya había una fecha final menor,
            | la limpiamos.
            |--------------------------------------------------------------------------
            */

            if (
                inicio.value &&
                fin.value &&
                fin.value <
                inicio.value
            ) {

                fin.value = '';

            }


            calcularEstado();

        }


        /*
        |--------------------------------------------------------------------------
        | EVENTOS
        |--------------------------------------------------------------------------
        */

        inicio.addEventListener(
            'change',
            actualizarFechaMinima
        );


        inicio.addEventListener(
            'input',
            actualizarFechaMinima
        );


        fin.addEventListener(
            'change',
            calcularEstado
        );


        fin.addEventListener(
            'input',
            calcularEstado
        );


        /*
        |--------------------------------------------------------------------------
        | CARGA INICIAL
        |--------------------------------------------------------------------------
        */

        if (
            inicio.value
        ) {

            fin.min =
                inicio.value;

        }


        calcularEstado();

    }
);
</script>

@endsection