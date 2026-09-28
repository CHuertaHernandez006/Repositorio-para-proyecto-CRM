@extends('layouts.app')

@section('content')

@php
    $buscarActual = $buscar ?? request('buscar');

    $operarioSeleccionado = null;
    $estadoSeleccionado = null;

    if (request()->filled('operario')) {
        $operarioSeleccionado = $operarios
            ->firstWhere('id', request('operario'));
    }

    if (request()->filled('estado')) {
        $estadoSeleccionado = $estados
            ->firstWhere(
                'id_estado_cita',
                request('estado')
            );
    }

    $hayFiltrosAvanzados =
        request()->filled('fecha_desde') ||
        request()->filled('fecha_hasta');

    $hayFiltros =
        request()->filled('buscar') ||
        request()->filled('operario') ||
        request()->filled('estado') ||
        request()->filled('fecha_desde') ||
        request()->filled('fecha_hasta');
@endphp


<style>
    .citas-page {
        max-width: 1450px;
        margin: 0 auto;
        padding: 28px 26px;
        color: #e8eef7;
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .citas-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 22px;
        margin-bottom: 22px;
    }

    .citas-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;

        color: #38bdf8;

        font-size: 10px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .citas-eyebrow-dot {
        width: 6px;
        height: 6px;

        border-radius: 50%;

        background: #38bdf8;

        box-shadow:
            0 0 10px
            rgba(56, 189, 248, .65);
    }

    .citas-title {
        margin: 0;

        color: #f8fafc;

        font-size: 30px;
        line-height: 1.15;

        font-weight: 800;

        letter-spacing: -.03em;
    }

    .citas-subtitle {
        max-width: 650px;

        margin: 7px 0 0;

        color: #718198;

        font-size: 12px;
        line-height: 1.6;
    }

    .btn-nueva-cita {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        height: 39px;

        padding: 0 15px;

        border:
            1px solid
            rgba(56, 189, 248, .28);

        border-radius: 9px;

        background: #1596ce;

        color: #ffffff;

        font-size: 10px;
        font-weight: 800;

        text-decoration: none;

        transition: .2s ease;

        white-space: nowrap;
    }

    .btn-nueva-cita:hover {
        background: #27a7df;

        transform: translateY(-1px);

        box-shadow:
            0 10px 25px
            rgba(56, 189, 248, .13);
    }

    .btn-nueva-cita svg {
        width: 14px;
        height: 14px;
    }


    /* =========================================================
       ALERTAS
    ========================================================== */

    .citas-alert {
        display: flex;
        align-items: flex-start;

        gap: 9px;

        margin-bottom: 16px;

        padding: 11px 13px;

        border-radius: 9px;

        font-size: 11px;
        line-height: 1.55;
    }

    .citas-alert svg {
        width: 15px;
        height: 15px;

        flex-shrink: 0;

        margin-top: 1px;
    }

    .citas-alert-success {
        border:
            1px solid
            rgba(52, 211, 153, .18);

        background:
            rgba(52, 211, 153, .07);

        color: #6ee7b7;
    }

    .citas-alert-danger {
        border:
            1px solid
            rgba(248, 113, 113, .18);

        background:
            rgba(248, 113, 113, .06);

        color: #fca5a5;
    }

    .citas-alert ul {
        margin: 5px 0 0;

        padding-left: 17px;
    }


    /* =========================================================
       RESUMEN GRANDE
    ========================================================== */

    .citas-stats {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 14px;

        margin-bottom: 20px;
    }

    .stat-card {
        position: relative;

        display: flex;

        align-items: center;

        justify-content:
            space-between;

        min-height: 118px;

        padding: 20px 22px;

        border:
            1px solid
            rgba(255, 255, 255, .06);

        border-radius: 14px;

        background: #111c30;

        overflow: hidden;

        box-shadow:
            0 14px 35px
            rgba(0, 0, 0, .07);

        transition: .2s ease;
    }

    .stat-card:hover {
        border-color:
            rgba(56, 189, 248, .16);

        transform:
            translateY(-1px);
    }

    .stat-card::after {
        content: "";

        position: absolute;

        right: -45px;
        bottom: -62px;

        width: 150px;
        height: 150px;

        border-radius: 50%;

        background:
            rgba(56, 189, 248, .035);

        pointer-events: none;
    }

    .stat-card.green::after {
        background:
            rgba(52, 211, 153, .03);
    }

    .stat-content {
        position: relative;

        z-index: 2;
    }

    .stat-label {
        color: #718198;

        font-size: 9px;

        font-weight: 800;

        letter-spacing: .10em;

        text-transform: uppercase;
    }

    .stat-number {
        margin-top: 7px;

        color: #ffffff;

        font-size: 32px;

        line-height: 1;

        font-weight: 850;

        letter-spacing: -.04em;
    }

    .stat-description {
        margin-top: 8px;

        color: #56667d;

        font-size: 9px;

        line-height: 1.5;
    }

    .stat-icon {
        position: relative;

        z-index: 2;

        display: flex;

        align-items: center;

        justify-content: center;

        width: 48px;
        height: 48px;

        flex: 0 0 48px;

        border:
            1px solid
            rgba(56, 189, 248, .18);

        border-radius: 12px;

        background:
            rgba(56, 189, 248, .09);

        color: #38bdf8;
    }

    .stat-icon.green {
        border-color:
            rgba(52, 211, 153, .18);

        background:
            rgba(52, 211, 153, .08);

        color: #34d399;
    }

    .stat-icon svg {
        width: 22px;
        height: 22px;
    }


    /* =========================================================
       FILTROS COMPACTOS
    ========================================================== */

    .filters-shell {
        margin-bottom: 18px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 12px;

        background: #111c30;
    }

    .filters-main {
        display: grid;

        grid-template-columns:
            minmax(250px, 1.6fr)
            minmax(145px, .65fr)
            minmax(140px, .6fr)
            auto;

        gap: 9px;

        align-items: end;

        padding: 13px;
    }

    .filter-group {
        min-width: 0;
    }

    .filter-label {
        display: block;

        margin-bottom: 5px;

        color: #617188;

        font-size: 8px;

        font-weight: 800;

        letter-spacing: .08em;

        text-transform: uppercase;
    }

    .search-control-wrapper {
        position: relative;
    }

    .search-control-wrapper svg {
        position: absolute;

        top: 50%;
        left: 11px;

        width: 13px;
        height: 13px;

        color: #53637a;

        transform:
            translateY(-50%);

        pointer-events: none;
    }

    .filter-control {
        width: 100%;
        height: 36px;

        padding: 0 10px;

        border:
            1px solid
            rgba(255, 255, 255, .075);

        border-radius: 8px;

        outline: none;

        background: #0a1527;

        color: #dbe5f1;

        font-size: 10px;

        box-sizing: border-box;

        color-scheme: dark;

        transition: .2s ease;
    }

    .search-control-wrapper .filter-control {
        padding-left: 33px;
    }

    .filter-control::placeholder {
        color: #4f6078;
    }

    .filter-control:focus {
        border-color:
            rgba(56, 189, 248, .35);

        box-shadow:
            0 0 0 3px
            rgba(56, 189, 248, .04);
    }

    .filter-control option {
        background: #0a1527;

        color: #dbe5f1;
    }

    .filter-buttons {
        display: flex;

        align-items: center;

        gap: 6px;
    }

    .btn-filter {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        height: 36px;

        padding: 0 12px;

        border:
            1px solid
            rgba(56, 189, 248, .24);

        border-radius: 8px;

        background:
            rgba(56, 189, 248, .10);

        color: #68d4ff;

        font-size: 9px;

        font-weight: 800;

        cursor: pointer;

        transition: .2s ease;

        white-space: nowrap;
    }

    .btn-filter:hover {
        background:
            rgba(56, 189, 248, .17);
    }

    .btn-filter svg {
        width: 12px;
        height: 12px;
    }

    .btn-clear {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        width: 36px;
        height: 36px;

        padding: 0;

        border:
            1px solid
            rgba(148, 163, 184, .12);

        border-radius: 8px;

        background:
            rgba(148, 163, 184, .04);

        color: #8391a4;

        text-decoration: none;

        transition: .2s ease;
    }

    .btn-clear:hover {
        background:
            rgba(148, 163, 184, .09);

        color: #cbd5e1;
    }

    .btn-clear svg {
        width: 13px;
        height: 13px;
    }


    /* =========================================================
       FILTROS AVANZADOS
    ========================================================== */

    .advanced-filters {
        border-top:
            1px solid
            rgba(255, 255, 255, .045);
    }

    .advanced-filters summary {
        display: flex;

        align-items: center;

        gap: 7px;

        width: fit-content;

        padding: 9px 13px;

        color: #718198;

        font-size: 9px;

        font-weight: 700;

        cursor: pointer;

        list-style: none;

        user-select: none;
    }

    .advanced-filters summary::-webkit-details-marker {
        display: none;
    }

    .advanced-filters summary:hover {
        color: #9acbe0;
    }

    .advanced-filters summary svg {
        width: 12px;
        height: 12px;
    }

    .advanced-content {
        display: grid;

        grid-template-columns:
            minmax(150px, 220px)
            minmax(150px, 220px);

        gap: 9px;

        padding:
            0 13px 13px;
    }


    /* =========================================================
       FILTROS ACTIVOS
    ========================================================== */

    .active-filters {
        display: flex;

        align-items: center;

        flex-wrap: wrap;

        gap: 6px;

        margin-top: -6px;
        margin-bottom: 18px;
    }

    .active-filter-label {
        color: #53637a;

        font-size: 8px;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: .07em;
    }

    .filter-chip {
        display: inline-flex;

        align-items: center;

        gap: 5px;

        min-height: 23px;

        padding: 0 8px;

        border:
            1px solid
            rgba(56, 189, 248, .11);

        border-radius: 999px;

        background:
            rgba(56, 189, 248, .045);

        color: #78d5f8;

        font-size: 8px;

        font-weight: 700;
    }

    .filter-chip svg {
        width: 10px;
        height: 10px;
    }


    /* =========================================================
       TABLA
    ========================================================== */

    .table-card {
        overflow: hidden;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 13px;

        background: #111c30;
    }

    .table-header {
        display: flex;

        align-items: center;

        justify-content:
            space-between;

        gap: 15px;

        padding: 14px 16px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .05);
    }

    .table-title {
        margin: 0;

        color: #f8fafc;

        font-size: 14px;

        font-weight: 750;
    }

    .table-subtitle {
        margin-top: 3px;

        color: #607087;

        font-size: 9px;
    }

    .table-count {
        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 6px 9px;

        border:
            1px solid
            rgba(56, 189, 248, .10);

        border-radius: 7px;

        background:
            rgba(56, 189, 248, .04);

        color: #69d2fa;

        font-size: 9px;

        font-weight: 750;
    }

    .table-count svg {
        width: 11px;
        height: 11px;
    }

    .table-scroll {
        overflow-x: auto;
    }

    .citas-table {
        width: 100%;

        border-collapse: collapse;

        min-width: 1000px;
    }

    .citas-table thead th {
        position: sticky;

        top: 0;

        z-index: 2;

        padding: 10px 13px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .05);

        background: #0d1728;

        color: #607087;

        font-size: 8px;

        font-weight: 800;

        letter-spacing: .08em;

        text-transform: uppercase;

        white-space: nowrap;
    }

    .citas-table tbody tr {
        border-bottom:
            1px solid
            rgba(255, 255, 255, .035);

        transition: .15s ease;
    }

    .citas-table tbody tr:last-child {
        border-bottom: 0;
    }

    .citas-table tbody tr:hover {
        background:
            rgba(56, 189, 248, .025);
    }

    .citas-table tbody td {
        padding: 12px 13px;

        color: #cbd5e1;

        font-size: 10px;

        vertical-align: middle;
    }


    /* =========================================================
       CLIENTE
    ========================================================== */

    .cliente-wrap {
        display: flex;

        align-items: center;

        gap: 9px;

        min-width: 180px;
    }

    .cliente-avatar {
        display: flex;

        align-items: center;

        justify-content: center;

        width: 30px;
        height: 30px;

        flex: 0 0 30px;

        border:
            1px solid
            rgba(56, 189, 248, .13);

        border-radius: 8px;

        background:
            rgba(56, 189, 248, .06);

        color: #61d2ff;

        font-size: 9px;

        font-weight: 800;
    }

    .cliente-name {
        color: #f1f5f9;

        font-size: 10px;

        font-weight: 700;
    }

    .cliente-company {
        display: flex;

        align-items: center;

        gap: 4px;

        margin-top: 3px;

        color: #64748b;

        font-size: 8px;
    }

    .cliente-company svg {
        width: 9px;
        height: 9px;
    }


    /* =========================================================
       OPERARIO
    ========================================================== */

    .operario-name {
        display: inline-flex;

        align-items: center;

        gap: 5px;

        color: #b8c5d5;

        font-size: 9px;

        font-weight: 650;
    }

    .operario-name svg {
        width: 11px;
        height: 11px;

        color: #38bdf8;
    }


    /* =========================================================
       FECHA / HORA
    ========================================================== */

    .date-main {
        color: #dce5f0;

        font-size: 9px;

        font-weight: 700;

        white-space: nowrap;
    }

    .time-badge {
        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 5px 7px;

        border:
            1px solid
            rgba(56, 189, 248, .11);

        border-radius: 7px;

        background:
            rgba(56, 189, 248, .055);

        color: #72d6fb;

        font-size: 9px;

        font-weight: 700;

        white-space: nowrap;
    }

    .time-badge svg {
        width: 10px;
        height: 10px;
    }


    /* =========================================================
       ESTADO
    ========================================================== */

    .status-badge {
        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 5px 8px;

        border:
            1px solid
            rgba(56, 189, 248, .13);

        border-radius: 999px;

        background:
            rgba(56, 189, 248, .055);

        color: #7dd3fc;

        font-size: 8px;

        font-weight: 750;

        white-space: nowrap;
    }

    .status-dot {
        width: 5px;
        height: 5px;

        border-radius: 50%;

        background: currentColor;
    }


    /* =========================================================
       DESCRIPCIÓN
    ========================================================== */

    .descripcion-main {
        max-width: 210px;

        color: #dbe5ef;

        font-size: 9px;

        font-weight: 650;

        line-height: 1.4;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }

    .descripcion-secondary {
        max-width: 210px;

        margin-top: 3px;

        color: #607087;

        font-size: 8px;

        line-height: 1.4;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }

    .descripcion-label {
        color: #53637a;

        font-size: 7px;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: .04em;

        margin-right: 3px;
    }

    .sin-descripcion {
        color: #53637a;

        font-weight: 500;

        font-style: italic;
    }


    /* =========================================================
       ACCIONES
    ========================================================== */

    .actions {
        display: flex;

        justify-content: flex-end;

        gap: 5px;
    }

    .action-btn {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        width: 29px;
        height: 29px;

        padding: 0;

        border:
            1px solid
            rgba(148, 163, 184, .13);

        border-radius: 7px;

        background: transparent;

        text-decoration: none;

        cursor: pointer;

        box-sizing: border-box;

        transition: .18s ease;
    }

    .action-btn svg {
        width: 12px;
        height: 12px;
    }

    .action-view {
        color: #38bdf8;
    }

    .action-view:hover {
        border-color:
            rgba(56, 189, 248, .35);

        background:
            rgba(56, 189, 248, .08);
    }

    .action-edit {
        color: #fbbf24;
    }

    .action-edit:hover {
        border-color:
            rgba(251, 191, 36, .35);

        background:
            rgba(251, 191, 36, .08);
    }

    .action-delete {
        color: #fb7185;
    }

    .action-delete:hover {
        border-color:
            rgba(251, 113, 133, .35);

        background:
            rgba(251, 113, 133, .08);
    }

    .actions form {
        margin: 0;
    }


    /* =========================================================
       VACÍO
    ========================================================== */

    .empty-state {
        padding: 55px 20px;

        text-align: center;
    }

    .empty-icon {
        display: flex;

        align-items: center;

        justify-content: center;

        width: 50px;
        height: 50px;

        margin: 0 auto 12px;

        border:
            1px solid
            rgba(56, 189, 248, .12);

        border-radius: 13px;

        background:
            rgba(56, 189, 248, .05);

        color: #38bdf8;
    }

    .empty-icon svg {
        width: 21px;
        height: 21px;
    }

    .empty-title {
        color: #f1f5f9;

        font-size: 13px;

        font-weight: 750;
    }

    .empty-text {
        margin: 5px 0 0;

        color: #64748b;

        font-size: 10px;
    }

    .pagination-wrapper {
        padding: 14px 16px;

        border-top:
            1px solid
            rgba(255, 255, 255, .045);
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 1050px) {

        .filters-main {
            grid-template-columns:
                minmax(230px, 1fr)
                1fr
                1fr;
        }

        .filter-buttons {
            grid-column:
                1 / -1;
        }
    }


    @media (max-width: 750px) {

        .citas-page {
            padding:
                22px 15px;
        }

        .citas-header {
            align-items:
                flex-start;

            flex-direction:
                column;
        }

        .btn-nueva-cita {
            width: 100%;
        }

        .citas-stats {
            grid-template-columns:
                1fr;
        }

        .stat-card {
            min-height: 105px;

            padding:
                18px;
        }

        .filters-main {
            grid-template-columns:
                1fr;
        }

        .filter-buttons {
            grid-column: 1;
        }

        .btn-filter {
            flex: 1;
        }

        .advanced-content {
            grid-template-columns:
                1fr;
        }

        .table-header {
            align-items:
                flex-start;

            flex-direction:
                column;
        }
    }
</style>


<div class="citas-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="citas-header">

        <div>

            <div class="citas-eyebrow">

                <span class="citas-eyebrow-dot"></span>

                Gestión de citas

            </div>


            <h1 class="citas-title">
                Citas
            </h1>


            <p class="citas-subtitle">

                Consulta, filtra y administra las citas
                registradas por los operarios de tu empresa.

            </p>

        </div>


        <a
            href="{{ route('citas.create') }}"
            class="btn-nueva-cita"
        >

            <i data-lucide="calendar-plus"></i>

            Nueva cita

        </a>

    </div>


    {{-- =========================================================
         MENSAJES
    ========================================================== --}}

    @if(session('exito'))

        <div class="citas-alert citas-alert-success">

            <i data-lucide="circle-check"></i>

            <div>
                {{ session('exito') }}
            </div>

        </div>

    @endif


    @if($errors->any())

        <div class="citas-alert citas-alert-danger">

            <i data-lucide="triangle-alert"></i>

            <div>

                <strong>
                    Revisa los siguientes datos:
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

    <div class="citas-stats">


        {{-- CITAS ENCONTRADAS --}}

        <div class="stat-card">

            <div class="stat-content">

                <div class="stat-label">
                    Citas encontradas
                </div>


                <div class="stat-number">
                    {{ $citas->total() }}
                </div>


                <div class="stat-description">

                    Registros que coinciden con
                    la búsqueda y filtros actuales.

                </div>

            </div>


            <div class="stat-icon">

                <i data-lucide="calendar-days"></i>

            </div>

        </div>


        {{-- OPERARIOS --}}

        <div class="stat-card green">

            <div class="stat-content">

                <div class="stat-label">
                    Operarios disponibles
                </div>


                <div class="stat-number">
                    {{ $operarios->count() }}
                </div>


                <div class="stat-description">

                    Operarios disponibles para
                    consulta dentro de la empresa.

                </div>

            </div>


            <div class="stat-icon green">

                <i data-lucide="users-round"></i>

            </div>

        </div>

    </div>


    {{-- =========================================================
         FILTROS
    ========================================================== --}}

    <form
        method="GET"
        action="{{ route('citas.index') }}"
    >

        <div class="filters-shell">

            <div class="filters-main">


                {{-- BUSCAR --}}

                <div class="filter-group">

                    <label class="filter-label">
                        Buscar cliente
                    </label>


                    <div class="search-control-wrapper">

                        <i data-lucide="search"></i>


                        <input
                            type="text"
                            name="buscar"
                            value="{{ $buscarActual }}"
                            class="filter-control"
                            placeholder="Nombre, empresa, correo o teléfono..."
                        >

                    </div>

                </div>


                {{-- OPERARIO --}}

                <div class="filter-group">

                    <label class="filter-label">
                        Operario
                    </label>


                    <select
                        name="operario"
                        class="filter-control"
                    >

                        <option value="">
                            Todos
                        </option>


                        @foreach($operarios as $operario)

                            <option
                                value="{{ $operario->id }}"
                                {{
                                    request('operario')
                                    == $operario->id
                                        ? 'selected'
                                        : ''
                                }}
                            >

                                {{ $operario->name }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- ESTADO --}}

                <div class="filter-group">

                    <label class="filter-label">
                        Estado
                    </label>


                    <select
                        name="estado"
                        class="filter-control"
                    >

                        <option value="">
                            Todos
                        </option>


                        @foreach($estados as $estado)

                            <option
                                value="{{ $estado->id_estado_cita }}"
                                {{
                                    request('estado')
                                    == $estado->id_estado_cita
                                        ? 'selected'
                                        : ''
                                }}
                            >

                                {{ $estado->nombre }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- BOTONES --}}

                <div class="filter-buttons">

                    <button
                        type="submit"
                        class="btn-filter"
                    >

                        <i data-lucide="search"></i>

                        Aplicar

                    </button>


                    @if($hayFiltros)

                        <a
                            href="{{ route('citas.index') }}"
                            class="btn-clear"
                            title="Limpiar filtros"
                        >

                            <i data-lucide="rotate-ccw"></i>

                        </a>

                    @endif

                </div>

            </div>


            {{-- =================================================
                 FILTROS AVANZADOS
            ================================================== --}}

            <details
                class="advanced-filters"
                {{ $hayFiltrosAvanzados ? 'open' : '' }}
            >

                <summary>

                    <i data-lucide="sliders-horizontal"></i>

                    Más filtros

                </summary>


                <div class="advanced-content">


                    <div class="filter-group">

                        <label class="filter-label">
                            Desde
                        </label>

                        <input
                            type="date"
                            name="fecha_desde"
                            value="{{ request('fecha_desde') }}"
                            class="filter-control"
                        >

                    </div>


                    <div class="filter-group">

                        <label class="filter-label">
                            Hasta
                        </label>

                        <input
                            type="date"
                            name="fecha_hasta"
                            value="{{ request('fecha_hasta') }}"
                            class="filter-control"
                        >

                    </div>

                </div>

            </details>

        </div>

    </form>


    {{-- =========================================================
         FILTROS ACTIVOS
    ========================================================== --}}

    @if($hayFiltros)

        <div class="active-filters">

            <span class="active-filter-label">
                Filtros activos:
            </span>


            @if(request()->filled('buscar'))

                <span class="filter-chip">

                    <i data-lucide="search"></i>

                    {{ request('buscar') }}

                </span>

            @endif


            @if($operarioSeleccionado)

                <span class="filter-chip">

                    <i data-lucide="user"></i>

                    {{ $operarioSeleccionado->name }}

                </span>

            @endif


            @if($estadoSeleccionado)

                <span class="filter-chip">

                    <i data-lucide="circle-dot"></i>

                    {{ $estadoSeleccionado->nombre }}

                </span>

            @endif


            @if(request()->filled('fecha_desde'))

                <span class="filter-chip">

                    <i data-lucide="calendar"></i>

                    Desde {{ request('fecha_desde') }}

                </span>

            @endif


            @if(request()->filled('fecha_hasta'))

                <span class="filter-chip">

                    <i data-lucide="calendar-check"></i>

                    Hasta {{ request('fecha_hasta') }}

                </span>

            @endif

        </div>

    @endif


    {{-- =========================================================
         TABLA
    ========================================================== --}}

    <div class="table-card">

        <div class="table-header">

            <div>

                <h2 class="table-title">
                    Citas registradas
                </h2>


                <div class="table-subtitle">

                    Consulta la información
                    y administra cada cita.

                </div>

            </div>


            <div class="table-count">

                <i data-lucide="calendar-range"></i>

                {{ $citas->total() }}

                {{
                    $citas->total() === 1
                        ? 'resultado'
                        : 'resultados'
                }}

            </div>

        </div>


        <div class="table-scroll">

            <table class="citas-table">

                <thead>

                    <tr>

                        <th>
                            Cliente
                        </th>

                        <th>
                            Operario
                        </th>

                        <th>
                            Fecha
                        </th>

                        <th>
                            Horario
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Detalle
                        </th>

                        <th style="text-align:right;">
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($citas as $cita)

                        <tr>


                            {{-- CLIENTE --}}

                            <td>

                                @if($cita->cliente)

                                    @php
                                        $nombreCliente = trim(
                                            ($cita->cliente->nombre ?? '')
                                            . ' ' .
                                            ($cita->cliente->apellido_paterno ?? '')
                                            . ' ' .
                                            ($cita->cliente->apellido_materno ?? '')
                                        );

                                        $inicialCliente =
                                            mb_strtoupper(
                                                mb_substr(
                                                    $cita->cliente->nombre
                                                    ?? 'C',
                                                    0,
                                                    1
                                                )
                                            );
                                    @endphp


                                    <div class="cliente-wrap">

                                        <div class="cliente-avatar">

                                            {{ $inicialCliente }}

                                        </div>


                                        <div>

                                            <div class="cliente-name">

                                                {{ $nombreCliente }}

                                            </div>


                                            @if($cita->cliente->empresa)

                                                <div class="cliente-company">

                                                    <i data-lucide="building-2"></i>

                                                    {{ $cita->cliente->empresa }}

                                                </div>

                                            @endif

                                        </div>

                                    </div>

                                @else

                                    <span class="sin-descripcion">
                                        Cliente no disponible
                                    </span>

                                @endif

                            </td>


                            {{-- OPERARIO --}}

                            <td>

                                @if($cita->usuario)

                                    <span class="operario-name">

                                        <i data-lucide="user"></i>

                                        {{ $cita->usuario->name }}

                                    </span>

                                @else

                                    <span class="sin-descripcion">
                                        Sin asignar
                                    </span>

                                @endif

                            </td>


                            {{-- FECHA --}}

                            <td>

                                <span class="date-main">

                                    {{
                                        $cita
                                            ->fecha_hora_inicio
                                            ->format('d/m/Y')
                                    }}

                                </span>

                            </td>


                            {{-- HORARIO --}}

                            <td>

                                <span class="time-badge">

                                    <i data-lucide="clock-3"></i>

                                    {{
                                        $cita
                                            ->fecha_hora_inicio
                                            ->format('H:i')
                                    }}


                                    @if($cita->fecha_hora_fin)

                                        -

                                        {{
                                            $cita
                                                ->fecha_hora_fin
                                                ->format('H:i')
                                        }}

                                    @endif

                                </span>

                            </td>


                            {{-- ESTADO --}}

                            <td>

                                @if($cita->estadoCita)

                                    <span class="status-badge">

                                        <span class="status-dot"></span>

                                        {{ $cita->estadoCita->nombre }}

                                    </span>

                                @else

                                    <span class="sin-descripcion">
                                        Sin estado
                                    </span>

                                @endif

                            </td>


                            {{-- DETALLE --}}

                            <td>

                                @if($cita->motivo)

                                    <div
                                        class="descripcion-main"
                                        title="{{ $cita->motivo }}"
                                    >

                                        {{ $cita->motivo }}

                                    </div>

                                @else

                                    <div
                                        class="
                                            descripcion-main
                                            sin-descripcion
                                        "
                                    >
                                        Sin motivo
                                    </div>

                                @endif


                                @if($cita->observaciones)

                                    <div
                                        class="descripcion-secondary"
                                        title="{{ $cita->observaciones }}"
                                    >

                                        <span class="descripcion-label">
                                            Nota
                                        </span>

                                        {{ $cita->observaciones }}

                                    </div>

                                @endif

                            </td>


                            {{-- ACCIONES --}}

                            <td>

                                <div class="actions">


                                    {{-- VER --}}

                                    <a
                                        href="{{
                                            route(
                                                'citas.show',
                                                $cita->id_cita
                                            )
                                        }}"
                                        class="action-btn action-view"
                                        title="Ver cita"
                                    >

                                        <i data-lucide="eye"></i>

                                    </a>


                                    {{-- EDITAR --}}

                                    <a
                                        href="{{
                                            route(
                                                'citas.edit',
                                                $cita->id_cita
                                            )
                                        }}"
                                        class="action-btn action-edit"
                                        title="Editar cita"
                                    >

                                        <i data-lucide="pencil"></i>

                                    </a>


                                    {{-- ELIMINAR --}}

                                    <form
                                        action="{{
                                            route(
                                                'citas.destroy',
                                                $cita->id_cita
                                            )
                                        }}"
                                        method="POST"
                                        onsubmit="
                                            return confirm(
                                                '¿Seguro que deseas eliminar esta cita?'
                                            );
                                        "
                                    >

                                        @csrf

                                        @method('DELETE')


                                        <button
                                            type="submit"
                                            class="action-btn action-delete"
                                            title="Eliminar cita"
                                        >

                                            <i data-lucide="trash-2"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="7">

                                <div class="empty-state">

                                    <div class="empty-icon">

                                        <i data-lucide="calendar-x-2"></i>

                                    </div>


                                    <div class="empty-title">
                                        No hay citas para mostrar
                                    </div>


                                    <p class="empty-text">

                                        No encontramos resultados
                                        con los filtros seleccionados.

                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             PAGINACIÓN
        ====================================================== --}}

        @if($citas->hasPages())

            <div class="pagination-wrapper">

                {{ $citas->links() }}

            </div>

        @endif

    </div>

</div>


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    }
);
</script>

@endsection