@extends('layouts.app')

@section('title', 'Solicitudes de operarios - CRM')
@section('header-title', 'Solicitudes de Operarios')

@section('content')

<style>
    .requests-page {
        max-width: 1250px;
        margin: 0 auto;
        padding: 28px 26px;
        color: #e8eef7;
    }

    /* =========================================================
       HEADER
    ========================================================== */

    .requests-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .requests-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;

        margin-bottom: 8px;

        color: #35c6ff;

        font-size: 10px;
        font-weight: 800;

        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .requests-eyebrow-dot {
        width: 6px;
        height: 6px;

        border-radius: 50%;

        background: #35c6ff;

        box-shadow:
            0 0 10px
            rgba(53, 198, 255, .6);
    }

    .requests-title {
        margin: 0;

        color: #ffffff;

        font-size: 30px;
        font-weight: 800;

        letter-spacing: -.03em;
    }

    .requests-subtitle {
        max-width: 700px;

        margin: 7px 0 0;

        color: #718198;

        font-size: 12px;
        line-height: 1.6;
    }


    /* =========================================================
       ALERTAS
    ========================================================== */

    .requests-alert {
        display: flex;
        align-items: flex-start;
        gap: 9px;

        margin-bottom: 18px;

        padding: 12px 14px;

        border-radius: 10px;

        font-size: 11px;
        line-height: 1.5;
    }

    .requests-alert svg {
        width: 15px;
        height: 15px;

        flex-shrink: 0;

        margin-top: 1px;
    }

    .requests-alert-success {
        border:
            1px solid
            rgba(52, 211, 153, .18);

        background:
            rgba(52, 211, 153, .06);

        color: #86efac;
    }

    .requests-alert-danger {
        border:
            1px solid
            rgba(248, 113, 113, .18);

        background:
            rgba(248, 113, 113, .06);

        color: #fca5a5;
    }

    .requests-alert-danger ul {
        margin: 5px 0 0;
        padding-left: 17px;
    }


    /* =========================================================
       RESUMEN
    ========================================================== */

    .summary-grid {
        display: grid;

        grid-template-columns:
            minmax(240px, 1fr)
            minmax(240px, 1fr);

        gap: 14px;

        margin-bottom: 20px;
    }

    .summary-card {
        position: relative;

        display: flex;
        align-items: center;
        justify-content: space-between;

        min-height: 105px;

        padding: 18px 20px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 14px;

        background: #111c30;

        overflow: hidden;
    }

    .summary-card::after {
        content: "";

        position: absolute;

        right: -40px;
        bottom: -55px;

        width: 130px;
        height: 130px;

        border-radius: 50%;

        background:
            rgba(251, 191, 36, .045);
    }

    .summary-card.blue::after {
        background:
            rgba(53, 198, 255, .04);
    }

    .summary-content {
        position: relative;
        z-index: 2;
    }

    .summary-label {
        color: #718198;

        font-size: 9px;
        font-weight: 800;

        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .summary-number {
        margin-top: 7px;

        color: #ffffff;

        font-size: 30px;
        font-weight: 850;

        line-height: 1;
    }

    .summary-description {
        margin-top: 7px;

        color: #56667d;

        font-size: 9px;
        line-height: 1.45;
    }

    .summary-icon {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 44px;
        height: 44px;

        flex: 0 0 44px;

        border:
            1px solid
            rgba(251, 191, 36, .18);

        border-radius: 11px;

        background:
            rgba(251, 191, 36, .08);

        color: #fbbf24;
    }

    .summary-icon.blue {
        border-color:
            rgba(53, 198, 255, .18);

        background:
            rgba(53, 198, 255, .08);

        color: #35c6ff;
    }

    .summary-icon svg {
        width: 20px;
        height: 20px;
    }


    /* =========================================================
       BUSCADOR
    ========================================================== */

    .search-card {
        margin-bottom: 18px;

        padding: 12px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 12px;

        background: #111c30;
    }

    .search-form {
        display: flex;
        align-items: center;

        gap: 8px;
    }

    .search-wrapper {
        position: relative;

        flex: 1;
    }

    .search-wrapper svg {
        position: absolute;

        top: 50%;
        left: 12px;

        width: 13px;
        height: 13px;

        color: #526278;

        transform:
            translateY(-50%);

        pointer-events: none;
    }

    .search-input {
        width: 100%;
        height: 38px;

        padding:
            0 12px 0 35px;

        border:
            1px solid
            rgba(255, 255, 255, .075);

        border-radius: 8px;

        outline: none;

        background: #091426;
        color: #edf4fc;

        font-size: 10px;

        box-sizing: border-box;

        transition: .2s ease;
    }

    .search-input::placeholder {
        color: #526278;
    }

    .search-input:focus {
        border-color:
            rgba(53, 198, 255, .35);

        box-shadow:
            0 0 0 3px
            rgba(53, 198, 255, .04);
    }

    .search-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        height: 38px;

        padding: 0 13px;

        border:
            1px solid
            rgba(53, 198, 255, .22);

        border-radius: 8px;

        background:
            rgba(53, 198, 255, .10);

        color: #64d4ff;

        font-size: 9px;
        font-weight: 800;

        cursor: pointer;
    }

    .search-btn svg {
        width: 12px;
        height: 12px;
    }

    .clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 38px;
        height: 38px;

        border:
            1px solid
            rgba(148, 163, 184, .12);

        border-radius: 8px;

        background:
            rgba(148, 163, 184, .04);

        color: #8492a6;

        text-decoration: none;
    }

    .clear-btn:hover {
        color: #cbd5e1;

        background:
            rgba(148, 163, 184, .08);
    }

    .clear-btn svg {
        width: 13px;
        height: 13px;
    }


    /* =========================================================
       HEADER LISTADO
    ========================================================== */

    .list-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;

        margin-bottom: 11px;
    }

    .list-title {
        margin: 0;

        color: #f8fafc;

        font-size: 14px;
        font-weight: 750;
    }

    .list-count {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        padding: 5px 8px;

        border:
            1px solid
            rgba(251, 191, 36, .13);

        border-radius: 999px;

        background:
            rgba(251, 191, 36, .055);

        color: #fbbf24;

        font-size: 8px;
        font-weight: 800;
    }

    .list-count svg {
        width: 10px;
        height: 10px;
    }


    /* =========================================================
       SOLICITUD
    ========================================================== */

    .request-list {
        display: flex;
        flex-direction: column;

        gap: 10px;
    }

    .request-card {
        position: relative;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 13px;

        background: #111c30;

        overflow: hidden;

        transition: .18s ease;
    }

    .request-card:hover {
        border-color:
            rgba(53, 198, 255, .12);
    }

    .request-main {
        display: grid;

        grid-template-columns:
            minmax(190px, 1fr)
            minmax(150px, .75fr)
            minmax(190px, .9fr)
            minmax(155px, .72fr)
            auto;

        gap: 15px;

        align-items: center;

        padding: 16px 18px;
    }


    /* =========================================================
       OPERARIO
    ========================================================== */

    .operator-block {
        display: flex;
        align-items: center;

        gap: 11px;

        min-width: 0;
    }

    .operator-avatar {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 40px;
        height: 40px;

        flex: 0 0 40px;

        border:
            1px solid
            rgba(53, 198, 255, .15);

        border-radius: 10px;

        background:
            rgba(53, 198, 255, .07);

        color: #5dd0ff;

        font-size: 12px;
        font-weight: 850;
    }

    .operator-info {
        min-width: 0;
    }

    .operator-name {
        color: #f1f5f9;

        font-size: 12px;
        font-weight: 750;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .operator-email {
        margin-top: 3px;

        color: #65758b;

        font-size: 9px;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pending-badge {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        margin-top: 6px;

        padding: 4px 7px;

        border:
            1px solid
            rgba(251, 191, 36, .14);

        border-radius: 999px;

        background:
            rgba(251, 191, 36, .055);

        color: #fbbf24;

        font-size: 7px;
        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: .04em;
    }

    .pending-dot {
        width: 5px;
        height: 5px;

        border-radius: 50%;

        background: currentColor;
    }


    /* =========================================================
       INFORMACIÓN
    ========================================================== */

    .info-label {
        margin-bottom: 5px;

        color: #536278;

        font-size: 7px;
        font-weight: 800;

        letter-spacing: .07em;

        text-transform: uppercase;
    }

    .info-value {
        display: flex;
        align-items: center;

        gap: 5px;

        color: #b9c5d4;

        font-size: 9px;
        font-weight: 650;

        line-height: 1.45;
    }

    .info-value svg {
        width: 11px;
        height: 11px;

        flex-shrink: 0;

        color: #35c6ff;
    }

    .info-secondary {
        margin-top: 4px;

        color: #5f6f85;

        font-size: 8px;
    }


    /* =========================================================
       TIPO DE OPERARIO
    ========================================================== */

    .type-card {
        display: flex;
        align-items: flex-start;

        gap: 8px;

        padding: 9px 10px;

        border:
            1px solid
            rgba(53, 198, 255, .10);

        border-radius: 9px;

        background:
            rgba(53, 198, 255, .035);
    }

    .type-card.assignment {
        border-color:
            rgba(167, 139, 250, .13);

        background:
            rgba(167, 139, 250, .045);
    }

    .type-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 28px;
        height: 28px;

        flex: 0 0 28px;

        border-radius: 7px;

        background:
            rgba(53, 198, 255, .08);

        color: #35c6ff;
    }

    .type-card.assignment .type-icon {
        background:
            rgba(167, 139, 250, .10);

        color: #c4b5fd;
    }

    .type-icon svg {
        width: 13px;
        height: 13px;
    }

    .type-content {
        min-width: 0;
    }

    .type-name {
        margin: 0;

        color: #dceaf6;

        font-size: 9px;
        font-weight: 750;
    }

    .type-card.assignment .type-name {
        color: #d8ccff;
    }

    .type-description {
        margin: 3px 0 0;

        color: #607187;

        font-size: 7px;
        line-height: 1.4;
    }

    .type-missing {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        padding: 6px 8px;

        border:
            1px solid
            rgba(248, 113, 113, .12);

        border-radius: 7px;

        background:
            rgba(248, 113, 113, .04);

        color: #fca5a5;

        font-size: 8px;
        font-weight: 700;
    }

    .type-missing svg {
        width: 11px;
        height: 11px;
    }


    /* =========================================================
       ACCIONES
    ========================================================== */

    .request-actions {
        display: flex;
        align-items: center;

        gap: 7px;

        justify-content: flex-end;
    }

    .request-actions form {
        margin: 0;
    }

    .btn-approve,
    .btn-reject {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        height: 34px;

        padding: 0 11px;

        border-radius: 8px;

        font-size: 8px;
        font-weight: 800;

        cursor: pointer;

        transition: .18s ease;

        white-space: nowrap;
    }

    .btn-approve {
        border:
            1px solid
            rgba(52, 211, 153, .18);

        background:
            rgba(52, 211, 153, .07);

        color: #6ee7b7;
    }

    .btn-approve:hover {
        background:
            rgba(52, 211, 153, .13);

        border-color:
            rgba(52, 211, 153, .30);
    }

    .btn-reject {
        border:
            1px solid
            rgba(248, 113, 113, .18);

        background:
            rgba(248, 113, 113, .06);

        color: #fca5a5;
    }

    .btn-reject:hover {
        background:
            rgba(248, 113, 113, .11);

        border-color:
            rgba(248, 113, 113, .30);
    }

    .btn-approve svg,
    .btn-reject svg {
        width: 12px;
        height: 12px;
    }


    /* =========================================================
       RECHAZO
    ========================================================== */

    .reject-panel {
        display: none;

        padding: 14px 18px 16px;

        border-top:
            1px solid
            rgba(248, 113, 113, .10);

        background:
            rgba(248, 113, 113, .015);
    }

    .reject-panel.open {
        display: block;
    }

    .reject-panel-title {
        display: flex;
        align-items: center;

        gap: 6px;

        margin-bottom: 8px;

        color: #fca5a5;

        font-size: 9px;
        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: .05em;
    }

    .reject-panel-title svg {
        width: 12px;
        height: 12px;
    }

    .reject-textarea {
        width: 100%;

        min-height: 80px;

        padding: 10px 11px;

        border:
            1px solid
            rgba(248, 113, 113, .16);

        border-radius: 8px;

        outline: none;

        background: #091426;
        color: #e7eef7;

        font-family: inherit;
        font-size: 10px;
        line-height: 1.5;

        resize: vertical;

        box-sizing: border-box;
    }

    .reject-textarea::placeholder {
        color: #526278;
    }

    .reject-textarea:focus {
        border-color:
            rgba(248, 113, 113, .35);

        box-shadow:
            0 0 0 3px
            rgba(248, 113, 113, .04);
    }

    .reject-panel-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;

        gap: 7px;

        margin-top: 8px;
    }

    .btn-cancel-reject {
        height: 32px;

        padding: 0 10px;

        border:
            1px solid
            rgba(148, 163, 184, .12);

        border-radius: 7px;

        background:
            rgba(148, 163, 184, .04);

        color: #8492a6;

        font-size: 8px;
        font-weight: 750;

        cursor: pointer;
    }

    .btn-confirm-reject {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        height: 32px;

        padding: 0 10px;

        border:
            1px solid
            rgba(248, 113, 113, .22);

        border-radius: 7px;

        background:
            rgba(248, 113, 113, .10);

        color: #fca5a5;

        font-size: 8px;
        font-weight: 800;

        cursor: pointer;
    }

    .btn-confirm-reject svg {
        width: 11px;
        height: 11px;
    }


    /* =========================================================
       EMPTY
    ========================================================== */

    .empty-state {
        padding: 55px 20px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 13px;

        background: #111c30;

        text-align: center;
    }

    .empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 52px;
        height: 52px;

        margin: 0 auto 13px;

        border:
            1px solid
            rgba(52, 211, 153, .13);

        border-radius: 13px;

        background:
            rgba(52, 211, 153, .06);

        color: #34d399;
    }

    .empty-icon svg {
        width: 22px;
        height: 22px;
    }

    .empty-title {
        color: #f1f5f9;

        font-size: 13px;
        font-weight: 750;
    }

    .empty-text {
        max-width: 450px;

        margin:
            5px auto 0;

        color: #64748b;

        font-size: 9px;
        line-height: 1.55;
    }


    /* =========================================================
       PAGINACIÓN
    ========================================================== */

    .pagination-wrapper {
        margin-top: 15px;

        padding: 13px 15px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 11px;

        background: #111c30;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 1150px) {

        .request-main {
            grid-template-columns:
                1fr 1fr 1fr;
        }

        .request-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 800px) {

        .requests-page {
            padding: 22px 15px;
        }

        .requests-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .search-form {
            align-items: stretch;
            flex-wrap: wrap;
        }

        .search-wrapper {
            flex: 1 1 100%;
        }

        .request-main {
            grid-template-columns: 1fr;
        }

        .request-actions {
            justify-content: stretch;
        }

        .btn-approve,
        .btn-reject {
            flex: 1;
        }
    }
</style>


<div class="requests-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="requests-header">

        <div>

            <div class="requests-eyebrow">

                <span class="requests-eyebrow-dot"></span>

                Administración de usuarios

            </div>


            <h1 class="requests-title">
                Solicitudes de operarios
            </h1>


            <p class="requests-subtitle">

                Revisa las solicitudes enviadas por los
                administradores de empresa antes de habilitar
                el acceso de nuevos operarios al sistema.

            </p>

        </div>

    </div>


    {{-- =========================================================
         MENSAJES
    ========================================================== --}}

    @if(session('success'))

        <div class="requests-alert requests-alert-success">

            <i data-lucide="circle-check"></i>

            <div>
                {{ session('success') }}
            </div>

        </div>

    @endif


    @if($errors->any())

        <div class="requests-alert requests-alert-danger">

            <i data-lucide="triangle-alert"></i>

            <div>

                <strong>
                    No se pudo completar la operación.
                </strong>

                <ul>

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif


    {{-- =========================================================
         RESUMEN
    ========================================================== --}}

    <div class="summary-grid">

        <div class="summary-card">

            <div class="summary-content">

                <div class="summary-label">
                    Solicitudes pendientes
                </div>

                <div class="summary-number">
                    {{ $solicitudes->total() }}
                </div>

                <div class="summary-description">

                    Operarios que esperan una decisión
                    del Super Administrador.

                </div>

            </div>


            <div class="summary-icon">

                <i data-lucide="user-round-clock"></i>

            </div>

        </div>


        <div class="summary-card blue">

            <div class="summary-content">

                <div class="summary-label">
                    Proceso de autorización
                </div>

                <div
                    class="summary-number"
                    style="font-size:22px;"
                >
                    Revisión
                </div>

                <div class="summary-description">

                    Verifica empresa y tipo de operario
                    antes de autorizar su acceso.

                </div>

            </div>


            <div class="summary-icon blue">

                <i data-lucide="shield-check"></i>

            </div>

        </div>

    </div>


    {{-- =========================================================
         BUSCADOR
    ========================================================== --}}

    <div class="search-card">

        <form
            method="GET"
            action="{{ route('operarios.solicitudes') }}"
            class="search-form"
        >

            <div class="search-wrapper">

                <i data-lucide="search"></i>

                <input
                    type="text"
                    name="buscar"
                    class="search-input"
                    value="{{ request('buscar') }}"
                    placeholder="Buscar por nombre o correo electrónico..."
                >

            </div>


            <button
                type="submit"
                class="search-btn"
            >

                <i data-lucide="search"></i>

                Buscar

            </button>


            @if(request()->filled('buscar'))

                <a
                    href="{{ route('operarios.solicitudes') }}"
                    class="clear-btn"
                    title="Limpiar búsqueda"
                >

                    <i data-lucide="rotate-ccw"></i>

                </a>

            @endif

        </form>

    </div>


    {{-- =========================================================
         LISTADO
    ========================================================== --}}

    <div class="list-header">

        <h2 class="list-title">
            Pendientes de revisión
        </h2>


        <span class="list-count">

            <i data-lucide="inbox"></i>

            {{ $solicitudes->total() }}

            {{
                $solicitudes->total() === 1
                    ? 'solicitud'
                    : 'solicitudes'
            }}

        </span>

    </div>


    <div class="request-list">

        @forelse($solicitudes as $solicitud)

            @php
                $inicial =
                    mb_strtoupper(
                        mb_substr(
                            $solicitud->name ?? 'O',
                            0,
                            1
                        )
                    );

                $solicitante =
                    $solicitantes[
                        $solicitud->solicitado_por
                    ] ?? null;

                $empresaNombre =
                    $solicitud->empresa->nombre
                    ?? $solicitud->empresa->nombre_empresa
                    ?? $solicitud->empresa->razon_social
                    ?? 'Sin empresa asociada';

                $tipoOperario =
                    $solicitud->tipoOperario;

                $tipoNombre =
                    $tipoOperario->nombre
                    ?? null;

                $tipoDescripcion =
                    $tipoOperario->descripcion
                    ?? null;

                $tipoLower =
                    $tipoNombre
                        ? mb_strtolower($tipoNombre)
                        : '';

                $esProspeccion =
                    str_contains(
                        $tipoLower,
                        'prospec'
                    );

                $iconoTipo =
                    $esProspeccion
                        ? 'search'
                        : 'user-round-check';
            @endphp


            <div class="request-card">

                <div class="request-main">


                    {{-- =========================================
                         OPERARIO
                    ========================================== --}}

                    <div class="operator-block">

                        <div class="operator-avatar">

                            {{ $inicial }}

                        </div>


                        <div class="operator-info">

                            <div class="operator-name">

                                {{ $solicitud->name }}

                            </div>


                            <div class="operator-email">

                                {{ $solicitud->email }}

                            </div>


                            <span class="pending-badge">

                                <span class="pending-dot"></span>

                                Pendiente

                            </span>

                        </div>

                    </div>


                    {{-- =========================================
                         EMPRESA
                    ========================================== --}}

                    <div>

                        <div class="info-label">
                            Empresa
                        </div>


                        <div class="info-value">

                            <i data-lucide="building-2"></i>

                            {{ $empresaNombre }}

                        </div>

                    </div>


                    {{-- =========================================
                         TIPO DE OPERARIO
                    ========================================== --}}

                    <div>

                        <div class="info-label">
                            Tipo de operario
                        </div>


                        @if($tipoOperario)

                            <div
                                class="type-card
                                {{ $esProspeccion ? '' : 'assignment' }}"
                            >

                                <div class="type-icon">

                                    <i
                                        data-lucide="{{ $iconoTipo }}"
                                    ></i>

                                </div>


                                <div class="type-content">

                                    <p class="type-name">

                                        {{ $tipoNombre }}

                                    </p>


                                    <p class="type-description">

                                        {{
                                            $tipoDescripcion
                                            ?? 'Tipo de operación asignado.'
                                        }}

                                    </p>

                                </div>

                            </div>

                        @else

                            <span class="type-missing">

                                <i data-lucide="triangle-alert"></i>

                                Sin tipo asignado

                            </span>

                        @endif

                    </div>


                    {{-- =========================================
                         SOLICITADO POR
                    ========================================== --}}

                    <div>

                        <div class="info-label">
                            Solicitado por
                        </div>


                        <div class="info-value">

                            <i data-lucide="user-round"></i>

                            {{
                                $solicitante
                                    ? $solicitante->name
                                    : 'Usuario no disponible'
                            }}

                        </div>


                        <div class="info-secondary">

                            @if($solicitud->fecha_solicitud)

                                {{
                                    $solicitud
                                        ->fecha_solicitud
                                        ->format(
                                            'd/m/Y H:i'
                                        )
                                }}

                            @else

                                Fecha no disponible

                            @endif

                        </div>

                    </div>


                    {{-- =========================================
                         ACCIONES
                    ========================================== --}}

                    <div class="request-actions">


                        {{-- APROBAR --}}

                        <form
                            action="{{ route(
                                'operarios.solicitud.aprobar',
                                $solicitud->id
                            ) }}"
                            method="POST"
                            onsubmit="
                                return confirm(
                                    '¿Aprobar a {{ addslashes($solicitud->name) }} como {{ addslashes($tipoNombre ?? 'operario') }}? El usuario podrá acceder al sistema.'
                                );
                            "
                        >

                            @csrf
                            @method('PATCH')


                            <button
                                type="submit"
                                class="btn-approve"
                            >

                                <i data-lucide="circle-check"></i>

                                Aprobar

                            </button>

                        </form>


                        {{-- RECHAZAR --}}

                        <button
                            type="button"
                            class="btn-reject"
                            data-reject-toggle="{{ $solicitud->id }}"
                        >

                            <i data-lucide="circle-x"></i>

                            Rechazar

                        </button>

                    </div>

                </div>


                {{-- =============================================
                     PANEL DE RECHAZO
                ============================================== --}}

                <div
                    class="reject-panel"
                    id="reject-panel-{{ $solicitud->id }}"
                >

                    <form
                        action="{{ route(
                            'operarios.solicitud.rechazar',
                            $solicitud->id
                        ) }}"
                        method="POST"
                    >

                        @csrf
                        @method('PATCH')


                        <div class="reject-panel-title">

                            <i data-lucide="message-square-warning"></i>

                            Motivo del rechazo

                        </div>


                        <textarea
                            name="motivo_rechazo"
                            class="reject-textarea"
                            maxlength="1000"
                            required
                            placeholder="Indica por qué no se autoriza esta solicitud..."
                        ></textarea>


                        <div class="reject-panel-actions">

                            <button
                                type="button"
                                class="btn-cancel-reject"
                                data-reject-cancel="{{ $solicitud->id }}"
                            >
                                Cancelar
                            </button>


                            <button
                                type="submit"
                                class="btn-confirm-reject"
                                onclick="
                                    return confirm(
                                        '¿Confirmas que deseas rechazar esta solicitud?'
                                    );
                                "
                            >

                                <i data-lucide="ban"></i>

                                Confirmar rechazo

                            </button>

                        </div>

                    </form>

                </div>

            </div>


        @empty

            <div class="empty-state">

                <div class="empty-icon">

                    <i data-lucide="badge-check"></i>

                </div>


                <div class="empty-title">
                    No hay solicitudes pendientes
                </div>


                <p class="empty-text">

                    Cuando un Admin Cliente registre un nuevo
                    operario, la solicitud aparecerá aquí para
                    que puedas revisar su empresa, tipo de operación
                    y aprobarla o rechazarla.

                </p>

            </div>

        @endforelse

    </div>


    {{-- =========================================================
         PAGINACIÓN
    ========================================================== --}}

    @if($solicitudes->hasPages())

        <div class="pagination-wrapper">

            {{ $solicitudes->links() }}

        </div>

    @endif

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
        | ABRIR RECHAZO
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-reject-toggle]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const id =
                                button.getAttribute(
                                    'data-reject-toggle'
                                );

                            const panel =
                                document.getElementById(
                                    'reject-panel-' + id
                                );

                            if (!panel) {
                                return;
                            }


                            document
                                .querySelectorAll(
                                    '.reject-panel.open'
                                )
                                .forEach(
                                    function (otherPanel) {

                                        if (
                                            otherPanel
                                            !== panel
                                        ) {
                                            otherPanel
                                                .classList
                                                .remove(
                                                    'open'
                                                );
                                        }

                                    }
                                );


                            panel
                                .classList
                                .toggle(
                                    'open'
                                );


                            if (
                                panel
                                    .classList
                                    .contains(
                                        'open'
                                    )
                            ) {
                                const textarea =
                                    panel.querySelector(
                                        'textarea'
                                    );

                                if (textarea) {
                                    textarea.focus();
                                }
                            }

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | CANCELAR RECHAZO
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-reject-cancel]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const id =
                                button.getAttribute(
                                    'data-reject-cancel'
                                );

                            const panel =
                                document.getElementById(
                                    'reject-panel-' + id
                                );

                            if (!panel) {
                                return;
                            }

                            panel
                                .classList
                                .remove(
                                    'open'
                                );

                        }
                    );

                }
            );

    }
);
</script>

@endsection