@extends('layouts.app')

@section('content')

<style>
    .cita-show-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 32px 26px;
        color: #e8eef7;
    }

    /* =========================================================
       ENCABEZADO
    ========================================================== */

    .cita-show-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px;
    }

    .cita-show-kicker {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 9px;
        color: #35c6ff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .cita-show-kicker-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 10px rgba(53, 198, 255, .6);
    }

    .cita-show-title {
        margin: 0;
        color: #fff;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .cita-show-subtitle {
        margin: 9px 0 0;
        color: #8190a7;
        font-size: 14px;
        line-height: 1.6;
    }

    .header-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .header-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 39px;
        padding: 0 14px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 9px;
        background: transparent;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .header-btn svg {
        width: 15px;
        height: 15px;
    }

    .header-btn:hover {
        border-color: rgba(148,163,184,.2);
        background: rgba(255,255,255,.035);
        color: #fff;
        transform: translateY(-1px);
    }

    .header-btn.edit {
        border-color: rgba(251,191,36,.16);
        background: rgba(251,191,36,.06);
        color: #fbbf24;
    }

    .header-btn.edit:hover {
        border-color: rgba(251,191,36,.35);
        background: rgba(251,191,36,.10);
        color: #fcd34d;
    }

    /* =========================================================
       ALERTA
    ========================================================== */

    .cita-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 15px;
        margin-bottom: 20px;
        border: 1px solid rgba(52,211,153,.15);
        border-radius: 10px;
        background: rgba(52,211,153,.07);
        color: #6ee7b7;
        font-size: 12px;
        line-height: 1.5;
    }

    .cita-alert svg {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        margin-top: 1px;
    }

    /* =========================================================
       RESUMEN
    ========================================================== */

    .cita-overview {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 22px;
    }

    .overview-card {
        position: relative;
        overflow: hidden;
        min-height: 105px;
        padding: 20px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 15px;
        background: #111c30;
        box-shadow: 0 12px 30px rgba(0,0,0,.08);
    }

    .overview-card::after {
        content: "";
        position: absolute;
        width: 100px;
        height: 100px;
        right: -45px;
        bottom: -55px;
        border-radius: 50%;
        background: rgba(53,198,255,.04);
    }

    .overview-label {
        color: #718098;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .overview-value {
        margin-top: 9px;
        padding-right: 50px;
        color: #fff;
        font-size: 19px;
        line-height: 1.25;
        font-weight: 750;
    }

    .overview-icon {
        position: absolute;
        z-index: 2;
        top: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 39px;
        height: 39px;
        border: 1px solid rgba(53,198,255,.12);
        border-radius: 10px;
        background: rgba(53,198,255,.08);
        color: #35c6ff;
    }

    .overview-icon svg {
        width: 19px;
        height: 19px;
    }

    .overview-card.green .overview-icon {
        border-color: rgba(52,211,153,.12);
        background: rgba(52,211,153,.08);
        color: #34d399;
    }

    .overview-card.yellow .overview-icon {
        border-color: rgba(251,191,36,.12);
        background: rgba(251,191,36,.08);
        color: #fbbf24;
    }

    /* =========================================================
       GRID PRINCIPAL
    ========================================================== */

    .cita-info-grid {
        display: grid;
        grid-template-columns: 1.35fr .85fr;
        gap: 22px;
    }

    .cita-card {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 16px;
        background: #111c30;
        box-shadow: 0 20px 50px rgba(0,0,0,.10);
    }

    .cita-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 19px 21px;
        border-bottom: 1px solid rgba(255,255,255,.055);
    }

    .cita-card-header-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: 1px solid rgba(53,198,255,.12);
        border-radius: 9px;
        background: rgba(53,198,255,.08);
        color: #35c6ff;
    }

    .cita-card-header-icon svg {
        width: 16px;
        height: 16px;
    }

    .cita-card-title {
        margin: 0;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
    }

    .cita-card-subtitle {
        margin: 3px 0 0;
        color: #66758d;
        font-size: 11px;
    }

    .cita-card-body {
        padding: 21px;
    }

    /* =========================================================
       CLIENTE
    ========================================================== */

    .client-profile {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,.055);
    }

    .client-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border: 1px solid rgba(53,198,255,.15);
        border-radius: 13px;
        background: rgba(53,198,255,.08);
        color: #55ceff;
        font-size: 15px;
        font-weight: 800;
    }

    .client-avatar svg {
        width: 20px;
        height: 20px;
    }

    .client-profile-name {
        margin: 0;
        color: #edf3fb;
        font-size: 15px;
        font-weight: 700;
    }

    .client-profile-company {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
        color: #687890;
        font-size: 11px;
    }

    .client-profile-company svg {
        width: 13px;
        height: 13px;
    }

    /* =========================================================
       INFORMACIÓN
    ========================================================== */

    .info-list {
        display: grid;
    }

    .info-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 14px 0;
        border-bottom: 1px solid rgba(255,255,255,.035);
    }

    .info-row:first-child {
        padding-top: 0;
    }

    .info-row:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .info-label {
        color: #687890;
        font-size: 11px;
        font-weight: 650;
    }

    .info-value {
        color: #dbe4ef;
        font-size: 12px;
        font-weight: 600;
        text-align: right;
        word-break: break-word;
    }

    /* =========================================================
       MOTIVO / OBSERVACIONES
    ========================================================== */

    .motivo-box {
        padding: 15px;
        margin-top: 20px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 11px;
        background: rgba(255,255,255,.018);
    }

    .motivo-box.observaciones {
        margin-top: 12px;
    }

    .motivo-label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 9px;
        color: #718098;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .motivo-label svg {
        width: 14px;
        height: 14px;
        color: #35c6ff;
    }

    .motivo-text {
        margin: 0;
        color: #cbd5e1;
        font-size: 12px;
        line-height: 1.7;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .sin-informacion {
        color: #64748b;
        font-style: italic;
    }

    /* =========================================================
       OPERARIO
    ========================================================== */

    .operator-profile {
        display: flex;
        align-items: center;
        gap: 13px;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,.055);
    }

    .operator-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border: 1px solid rgba(52,211,153,.14);
        border-radius: 12px;
        background: rgba(52,211,153,.07);
        color: #34d399;
    }

    .operator-avatar svg {
        width: 19px;
        height: 19px;
    }

    .operator-name {
        margin: 0;
        color: #edf3fb;
        font-size: 14px;
        font-weight: 700;
    }

    .operator-role {
        margin-top: 3px;
        color: #65748b;
        font-size: 10px;
    }

    /* =========================================================
       ACCIONES
    ========================================================== */

    .bottom-actions {
        display: flex;
        gap: 8px;
        margin-top: 22px;
    }

    .bottom-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 39px;
        padding: 0 14px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 9px;
        background: transparent;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: .18s ease;
    }

    .bottom-action svg {
        width: 15px;
        height: 15px;
    }

    .bottom-action:hover {
        transform: translateY(-1px);
        background: rgba(255,255,255,.035);
        color: #fff;
        border-color: rgba(255,255,255,.12);
    }

    .bottom-action.edit {
        border-color: rgba(251,191,36,.15);
        background: rgba(251,191,36,.055);
        color: #fbbf24;
    }

    .bottom-action.edit:hover {
        border-color: rgba(251,191,36,.35);
        background: rgba(251,191,36,.09);
        color: #fcd34d;
    }

    .bottom-action.delete {
        border-color: rgba(248,113,113,.14);
        background: rgba(248,113,113,.05);
        color: #f87171;
    }

    .bottom-action.delete:hover {
        border-color: rgba(248,113,113,.35);
        background: rgba(248,113,113,.09);
        color: #fb8b8b;
    }

    .bottom-actions form {
        margin: 0;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 950px) {
        .cita-overview {
            grid-template-columns: 1fr;
        }

        .cita-info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .cita-show-page {
            padding: 22px 15px;
        }

        .cita-show-header {
            flex-direction: column;
        }

        .header-actions {
            width: 100%;
        }

        .header-btn {
            flex: 1;
        }

        .cita-show-title {
            font-size: 27px;
        }

        .cita-card-body {
            padding: 17px;
        }

        .bottom-actions {
            flex-wrap: wrap;
        }
    }
</style>


<div class="cita-show-page">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}

    <div class="cita-show-header">

        <div>

            <div class="cita-show-kicker">
                <span class="cita-show-kicker-dot"></span>
                Detalle de cita
            </div>

            <h1 class="cita-show-title">
                Cita #{{ $cita->id_cita }}
            </h1>

            <p class="cita-show-subtitle">
                Información y datos registrados de la cita.
            </p>

        </div>


        <div class="header-actions">

            <a
                href="{{ route('citas.index') }}"
                class="header-btn"
            >
                <i data-lucide="arrow-left"></i>
                Volver
            </a>

            <a
                href="{{ route('citas.edit', $cita->id_cita) }}"
                class="header-btn edit"
            >
                <i data-lucide="pencil"></i>
                Editar
            </a>

        </div>

    </div>


    {{-- =========================================================
         ALERTA
    ========================================================== --}}

    @if(session('exito'))

        <div class="cita-alert">

            <i data-lucide="circle-check"></i>

            <div>
                {{ session('exito') }}
            </div>

        </div>

    @endif


    {{-- =========================================================
         RESUMEN
    ========================================================== --}}

    <div class="cita-overview">

        <div class="overview-card">

            <div class="overview-label">
                Fecha
            </div>

            <div class="overview-value">
                {{ $cita->fecha_hora_inicio->format('d/m/Y') }}
            </div>

            <div class="overview-icon">
                <i data-lucide="calendar-days"></i>
            </div>

        </div>


        <div class="overview-card green">

            <div class="overview-label">
                Horario
            </div>

            <div class="overview-value">

                {{ $cita->fecha_hora_inicio->format('H:i') }}

                @if($cita->fecha_hora_fin)
                    - {{ $cita->fecha_hora_fin->format('H:i') }}
                @endif

            </div>

            <div class="overview-icon">
                <i data-lucide="clock-3"></i>
            </div>

        </div>


        <div class="overview-card yellow">

            <div class="overview-label">
                Estado
            </div>

            <div class="overview-value">
                {{ $cita->estadoCita?->nombre ?? 'Sin estado' }}
            </div>

            <div class="overview-icon">
                <i data-lucide="circle-dot"></i>
            </div>

        </div>

    </div>


    {{-- =========================================================
         INFORMACIÓN PRINCIPAL
    ========================================================== --}}

    <div class="cita-info-grid">


        {{-- =====================================================
             CLIENTE
        ====================================================== --}}

        <div class="cita-card">

            <div class="cita-card-header">

                <div class="cita-card-header-icon">
                    <i data-lucide="user-round"></i>
                </div>

                <div>

                    <h2 class="cita-card-title">
                        Cliente
                    </h2>

                    <p class="cita-card-subtitle">
                        Información de contacto.
                    </p>

                </div>

            </div>


            <div class="cita-card-body">

                @if($cita->cliente)

                    @php

                        $nombreCliente = trim(
                            $cita->cliente->nombre . ' ' .
                            $cita->cliente->apellido_paterno . ' ' .
                            $cita->cliente->apellido_materno
                        );

                        $inicialCliente = strtoupper(
                            substr($cita->cliente->nombre, 0, 1)
                        );

                    @endphp


                    <div class="client-profile">

                        <div class="client-avatar">
                            {{ $inicialCliente }}
                        </div>

                        <div>

                            <p class="client-profile-name">
                                {{ $nombreCliente }}
                            </p>

                            @if($cita->cliente->empresa)

                                <div class="client-profile-company">

                                    <i data-lucide="building-2"></i>

                                    {{ $cita->cliente->empresa }}

                                </div>

                            @endif

                        </div>

                    </div>


                    <div class="info-list">

                        <div class="info-row">

                            <span class="info-label">
                                Correo
                            </span>

                            <span class="info-value">
                                {{ $cita->cliente->correo ?: 'No registrado' }}
                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Teléfono
                            </span>

                            <span class="info-value">
                                {{ $cita->cliente->telefono_principal ?: 'No registrado' }}
                            </span>

                        </div>

                    </div>

                @else

                    <div class="client-profile">

                        <div class="client-avatar">
                            <i data-lucide="user-round-x"></i>
                        </div>

                        <div>

                            <p class="client-profile-name">
                                Cliente no disponible
                            </p>

                            <div class="client-profile-company">
                                No se encontró información del cliente.
                            </div>

                        </div>

                    </div>

                @endif


                {{-- =================================================
                     MOTIVO
                ================================================== --}}

                <div class="motivo-box">

                    <div class="motivo-label">

                        <i data-lucide="message-square-text"></i>

                        Motivo

                    </div>


                    @if(filled($cita->motivo))

                        <p class="motivo-text">
                            {{ $cita->motivo }}
                        </p>

                    @else

                        <p class="motivo-text sin-informacion">
                            No se registró un motivo para esta cita.
                        </p>

                    @endif

                </div>


                {{-- =================================================
                     OBSERVACIONES
                ================================================== --}}

                <div class="motivo-box observaciones">

                    <div class="motivo-label">

                        <i data-lucide="notebook-pen"></i>

                        Observaciones

                    </div>


                    @if(filled($cita->observaciones))

                        <p class="motivo-text">
                            {{ $cita->observaciones }}
                        </p>

                    @else

                        <p class="motivo-text sin-informacion">
                            No se registraron observaciones.
                        </p>

                    @endif

                </div>

            </div>

        </div>


        {{-- =====================================================
             OPERARIO
        ====================================================== --}}

        <div class="cita-card">

            <div class="cita-card-header">

                <div class="cita-card-header-icon">
                    <i data-lucide="user-round"></i>
                </div>

                <div>

                    <h2 class="cita-card-title">
                        Operario
                    </h2>

                    <p class="cita-card-subtitle">
                        Responsable asignado.
                    </p>

                </div>

            </div>


            <div class="cita-card-body">

                @if($cita->usuario)

                    <div class="operator-profile">

                        <div class="operator-avatar">
                            <i data-lucide="user-round"></i>
                        </div>

                        <div>

                            <p class="operator-name">
                                {{ $cita->usuario->name }}
                            </p>

                            <p class="operator-role">
                                Operario asignado
                            </p>

                        </div>

                    </div>


                    <div class="info-list">

                        <div class="info-row">

                            <span class="info-label">
                                Correo
                            </span>

                            <span class="info-value">
                                {{ $cita->usuario->email ?: 'No registrado' }}
                            </span>

                        </div>

                    </div>

                @else

                    <div class="client-profile">

                        <div class="client-avatar">
                            <i data-lucide="user-round-x"></i>
                        </div>

                        <div>

                            <p class="client-profile-name">
                                Sin operario asignado
                            </p>

                            <div class="client-profile-company">
                                Esta cita no tiene responsable.
                            </div>

                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- =========================================================
         ACCIONES
    ========================================================== --}}

    <div class="bottom-actions">

        <a
            href="{{ route('citas.edit', $cita->id_cita) }}"
            class="bottom-action edit"
        >
            <i data-lucide="pencil"></i>
            Editar cita
        </a>


        <form
            action="{{ route('citas.destroy', $cita->id_cita) }}"
            method="POST"
            onsubmit="return confirm('¿Seguro que deseas eliminar esta cita?');"
        >

            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="bottom-action delete"
            >
                <i data-lucide="trash-2"></i>
                Eliminar cita
            </button>

        </form>

    </div>

</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    });
</script>

@endsection