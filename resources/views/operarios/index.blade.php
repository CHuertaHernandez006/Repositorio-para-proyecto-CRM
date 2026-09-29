@extends('layouts.app')

@section('title', 'Operarios - CRM')
@section('header-title', 'Operarios')

@section('content')

<style>
    .operarios-page {
        color: #e8eef7;
        max-width: 1400px;
        margin: 0 auto;
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .operarios-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        gap: 24px;

        margin-bottom: 26px;
    }

    .operarios-eyebrow {
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

    .operarios-eyebrow-dot {
        width: 6px;
        height: 6px;

        border-radius: 50%;

        background: #35c6ff;

        box-shadow:
            0 0 10px
            rgba(53, 198, 255, .6);
    }

    .operarios-title {
        margin: 0;

        color: #ffffff;

        font-size: 32px;
        line-height: 1.15;

        font-weight: 750;

        letter-spacing: -.03em;
    }

    .operarios-description {
        margin: 9px 0 0;

        color: #8190a7;

        font-size: 14px;
        line-height: 1.6;
    }

    .operarios-header-actions {
        display: flex;
        align-items: center;

        gap: 10px;

        flex-shrink: 0;
    }


    /* =========================================================
       BOTONES SUPERIORES
    ========================================================== */

    .btn-objetivos,
    .btn-nuevo-operario {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 9px;

        padding: 11px 17px;

        border-radius: 11px;

        font-size: 13px;
        font-weight: 700;

        text-decoration: none;

        transition: .2s ease;

        white-space: nowrap;
    }

    .btn-objetivos {
        border:
            1px solid
            rgba(52, 211, 153, .22);

        background:
            rgba(52, 211, 153, .08);

        color: #34d399;
    }

    .btn-objetivos:hover {
        border-color:
            rgba(52, 211, 153, .45);

        background:
            rgba(52, 211, 153, .16);

        color: #6ee7b7;

        transform: translateY(-1px);
    }

    .btn-nuevo-operario {
        border:
            1px solid
            rgba(53, 198, 255, .22);

        background:
            rgba(53, 198, 255, .09);

        color: #55ceff;
    }

    .btn-nuevo-operario:hover {
        border-color:
            rgba(53, 198, 255, .48);

        background:
            rgba(53, 198, 255, .18);

        color: #8dddff;

        transform: translateY(-1px);
    }

    .btn-objetivos svg,
    .btn-nuevo-operario svg {
        width: 16px;
        height: 16px;
    }


    /* =========================================================
       ALERTAS
    ========================================================== */

    .success-alert,
    .error-alert {
        display: flex;
        align-items: flex-start;

        gap: 10px;

        margin-bottom: 20px;

        padding: 12px 15px;

        border-radius: 10px;

        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
    }

    .success-alert {
        border:
            1px solid
            rgba(52, 211, 153, .15);

        background:
            rgba(52, 211, 153, .07);

        color: #6ee7b7;
    }

    .error-alert {
        border:
            1px solid
            rgba(248, 113, 113, .15);

        background:
            rgba(248, 113, 113, .06);

        color: #fca5a5;
    }

    .success-alert svg,
    .error-alert svg {
        width: 16px;
        height: 16px;

        flex: 0 0 16px;

        margin-top: 1px;
    }


    /* =========================================================
       ESTADÍSTICAS
    ========================================================== */

    .operarios-stats {
        display: grid;

        grid-template-columns:
            repeat(5, minmax(0, 1fr));

        gap: 12px;

        margin-bottom: 22px;
    }

    .operario-stat {
        position: relative;

        overflow: hidden;

        min-height: 105px;

        padding: 18px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 15px;

        background: #111c30;

        box-shadow:
            0 12px 30px
            rgba(0, 0, 0, .08);
    }

    .operario-stat::after {
        content: "";

        position: absolute;

        right: -45px;
        bottom: -55px;

        width: 100px;
        height: 100px;

        border-radius: 50%;

        background:
            rgba(53, 198, 255, .04);

        pointer-events: none;
    }

    .stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 10px;
    }

    .stat-label {
        color: #718098;

        font-size: 9px;
        font-weight: 700;

        letter-spacing: .09em;

        text-transform: uppercase;
    }

    .stat-number {
        margin-top: 8px;

        color: #ffffff;

        font-size: 27px;
        line-height: 1;

        font-weight: 750;
    }

    .stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 37px;
        height: 37px;

        flex: 0 0 37px;

        border:
            1px solid
            rgba(53, 198, 255, .28);

        border-radius: 10px;

        background:
            rgba(53, 198, 255, .13);

        color: #35c6ff;
    }

    .stat-icon svg {
        width: 18px;
        height: 18px;
    }

    .stat-icon.green {
        border-color:
            rgba(52, 211, 153, .28);

        background:
            rgba(52, 211, 153, .13);

        color: #34d399;
    }

    .stat-icon.yellow {
        border-color:
            rgba(251, 191, 36, .28);

        background:
            rgba(251, 191, 36, .11);

        color: #fbbf24;
    }

    .stat-icon.gray {
        border-color:
            rgba(148, 163, 184, .25);

        background:
            rgba(148, 163, 184, .11);

        color: #94a3b8;
    }

    .stat-icon.red {
        border-color:
            rgba(248, 113, 113, .24);

        background:
            rgba(248, 113, 113, .09);

        color: #f87171;
    }


    /* =========================================================
       MAIN CARD
    ========================================================== */

    .operarios-card {
        overflow: hidden;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 16px;

        background: #111c30;

        box-shadow:
            0 20px 50px
            rgba(0, 0, 0, .10);
    }

    .operarios-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 20px;

        padding: 19px 21px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .055);
    }

    .card-title {
        margin: 0;

        color: #ffffff;

        font-size: 15px;
        font-weight: 700;
    }

    .card-subtitle {
        margin: 4px 0 0;

        color: #66758d;

        font-size: 12px;
    }


    /* =========================================================
       FILTROS
    ========================================================== */

    .filters-form {
        display: flex;
        align-items: center;

        gap: 8px;

        flex-wrap: wrap;
    }

    .search-wrapper {
        position: relative;

        min-width: 240px;
    }

    .search-wrapper svg {
        position: absolute;

        left: 12px;
        top: 50%;

        width: 14px;
        height: 14px;

        transform:
            translateY(-50%);

        color: #5c6b81;

        pointer-events: none;
    }

    .search-wrapper input,
    .filter-select {
        height: 38px;

        border:
            1px solid
            rgba(255, 255, 255, .08);

        border-radius: 9px;

        outline: none;

        background: #0c1628;

        color: #ffffff;

        font-size: 11px;

        box-sizing: border-box;

        color-scheme: dark;
    }

    .search-wrapper input {
        width: 100%;

        padding:
            0 12px 0 35px;
    }

    .filter-select {
        min-width: 145px;

        padding: 0 10px;

        cursor: pointer;
    }

    .search-wrapper input:focus,
    .filter-select:focus {
        border-color:
            rgba(53, 198, 255, .35);

        box-shadow:
            0 0 0 3px
            rgba(53, 198, 255, .05);
    }

    .filter-button,
    .clear-filters {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        height: 38px;

        padding: 0 12px;

        border-radius: 9px;

        font-size: 10px;
        font-weight: 700;

        text-decoration: none;

        cursor: pointer;
    }

    .filter-button {
        border:
            1px solid
            rgba(53, 198, 255, .18);

        background:
            rgba(53, 198, 255, .08);

        color: #55ceff;
    }

    .clear-filters {
        border:
            1px solid
            rgba(148, 163, 184, .12);

        background:
            rgba(148, 163, 184, .05);

        color: #8492a6;
    }

    .filter-button svg,
    .clear-filters svg {
        width: 13px;
        height: 13px;
    }


    /* =========================================================
       TABLA
    ========================================================== */

    .operarios-table-wrapper {
        overflow-x: auto;
    }

    .operarios-table {
        width: 100%;

        border-collapse: collapse;
    }

    .operarios-table th {
        padding: 13px 18px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .045);

        color: #59687f;

        font-size: 9px;
        font-weight: 700;

        letter-spacing: .10em;

        text-align: left;
        text-transform: uppercase;

        white-space: nowrap;
    }

    .operarios-table td {
        padding: 14px 18px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .035);

        vertical-align: middle;
    }

    .operarios-table tbody tr {
        transition:
            background .15s ease;
    }

    .operarios-table tbody tr:hover {
        background:
            rgba(255, 255, 255, .018);
    }

    .operarios-table tbody tr:last-child td {
        border-bottom: none;
    }


    /* =========================================================
       OPERARIO
    ========================================================== */

    .operario-user {
        display: flex;
        align-items: center;

        gap: 12px;

        min-width: 190px;
    }

    .operario-avatar {
        position: relative;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 40px;
        height: 40px;

        flex: 0 0 40px;

        border:
            1px solid
            rgba(53, 198, 255, .12);

        border-radius: 11px;

        background:
            rgba(53, 198, 255, .07);

        color: #52cfff;

        font-size: 13px;
        font-weight: 800;
    }

    .operario-online {
        position: absolute;

        right: -2px;
        bottom: -2px;

        width: 9px;
        height: 9px;

        border:
            2px solid
            #111c30;

        border-radius: 50%;

        background: #34d399;
    }

    .operario-name {
        margin: 0;

        color: #edf3fb;

        font-size: 12px;
        font-weight: 650;
    }

    .operario-id {
        margin-top: 3px;

        color: #59687f;

        font-size: 9px;
    }


    /* =========================================================
       EMAIL
    ========================================================== */

    .operario-email {
        display: flex;
        align-items: center;

        gap: 7px;

        color: #8996aa;

        font-size: 11px;

        white-space: nowrap;
    }

    .operario-email svg {
        width: 13px;
        height: 13px;

        color: #58677d;
    }


    /* =========================================================
       EMPRESA
    ========================================================== */

    .empresa-badge {
        display: inline-flex;
        align-items: center;

        gap: 6px;

        padding: 6px 8px;

        border:
            1px solid
            rgba(255, 255, 255, .05);

        border-radius: 7px;

        background:
            rgba(255, 255, 255, .025);

        color: #a1adbd;

        font-size: 9px;
        font-weight: 600;

        white-space: nowrap;
    }

    .empresa-badge svg {
        width: 12px;
        height: 12px;

        color: #66758b;
    }

    .sin-empresa {
        color: #59687f;

        font-size: 10px;
    }


    /* =========================================================
       TIPO DE OPERARIO
    ========================================================== */

    .tipo-badge {
        display: inline-flex;
        align-items: center;

        gap: 6px;

        padding: 6px 8px;

        border:
            1px solid
            rgba(53, 198, 255, .10);

        border-radius: 7px;

        background:
            rgba(53, 198, 255, .04);

        color: #6fcff5;

        font-size: 9px;
        font-weight: 650;

        white-space: nowrap;
    }

    .tipo-badge svg {
        width: 12px;
        height: 12px;
    }

    .tipo-badge.assignment {
        border-color:
            rgba(167, 139, 250, .12);

        background:
            rgba(167, 139, 250, .05);

        color: #c4b5fd;
    }

    .tipo-none {
        color: #58677d;

        font-size: 9px;
    }


    /* =========================================================
       ESTADOS
    ========================================================== */

    .status-badge {
        display: inline-flex;
        align-items: center;

        gap: 7px;

        padding: 6px 9px;

        border-radius: 30px;

        font-size: 9px;
        font-weight: 700;

        white-space: nowrap;
    }

    .status-dot {
        width: 5px;
        height: 5px;

        border-radius: 50%;
    }

    .status-badge.active {
        border:
            1px solid
            rgba(52, 211, 153, .14);

        background:
            rgba(52, 211, 153, .07);

        color: #6ee7b7;
    }

    .status-badge.active .status-dot {
        background: #34d399;

        box-shadow:
            0 0 7px
            rgba(52, 211, 153, .6);
    }

    .status-badge.inactive {
        border:
            1px solid
            rgba(148, 163, 184, .1);

        background:
            rgba(148, 163, 184, .05);

        color: #728097;
    }

    .status-badge.inactive .status-dot {
        background: #64748b;
    }

    .status-badge.pending {
        border:
            1px solid
            rgba(251, 191, 36, .16);

        background:
            rgba(251, 191, 36, .07);

        color: #fbbf24;
    }

    .status-badge.pending .status-dot {
        background: #fbbf24;

        box-shadow:
            0 0 7px
            rgba(251, 191, 36, .45);
    }

    .status-badge.rejected {
        border:
            1px solid
            rgba(248, 113, 113, .15);

        background:
            rgba(248, 113, 113, .065);

        color: #f87171;
    }

    .status-badge.rejected .status-dot {
        background: #f87171;
    }


    /* =========================================================
       ACCIONES
    ========================================================== */

    .operario-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;

        gap: 5px;
    }

    .operario-actions form {
        margin: 0;
    }

    .action-button {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 31px;
        height: 31px;

        border:
            1px solid
            rgba(148, 163, 184, .16);

        border-radius: 8px;

        background:
            rgba(148, 163, 184, .07);

        color: #94a3b8;

        cursor: pointer;

        transition: .18s ease;

        text-decoration: none;

        box-sizing: border-box;
    }

    .action-button svg {
        width: 14px;
        height: 14px;

        stroke-width: 2.2;
    }

    .action-button:hover {
        transform:
            translateY(-1px);
    }

    .action-button.view {
        border-color:
            rgba(53, 198, 255, .18);

        background:
            rgba(53, 198, 255, .08);

        color: #35c6ff;
    }

    .action-button.edit {
        border-color:
            rgba(251, 191, 36, .18);

        background:
            rgba(251, 191, 36, .07);

        color: #fbbf24;
    }

    .action-button.toggle {
        border-color:
            rgba(52, 211, 153, .18);

        background:
            rgba(52, 211, 153, .07);

        color: #34d399;
    }

    .action-button.resend {
        border-color:
            rgba(167, 139, 250, .20);

        background:
            rgba(167, 139, 250, .08);

        color: #c4b5fd;
    }

    .action-button.delete {
        border-color:
            rgba(248, 113, 113, .18);

        background:
            rgba(248, 113, 113, .07);

        color: #f87171;
    }


    /* =========================================================
       PAGINACIÓN
    ========================================================== */

    .pagination-container {
        padding: 14px 18px;

        border-top:
            1px solid
            rgba(255, 255, 255, .045);
    }


    /* =========================================================
       EMPTY
    ========================================================== */

    .operarios-empty {
        padding: 65px 25px;

        text-align: center;
    }

    .empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 58px;
        height: 58px;

        margin: 0 auto;

        border:
            1px solid
            rgba(53, 198, 255, .1);

        border-radius: 15px;

        background:
            rgba(53, 198, 255, .045);

        color: #35c6ff;
    }

    .empty-icon svg {
        width: 25px;
        height: 25px;
    }

    .empty-title {
        margin: 17px 0 0;

        color: #ffffff;

        font-size: 16px;
        font-weight: 700;
    }

    .empty-description {
        max-width: 420px;

        margin:
            7px auto 0;

        color: #65748b;

        font-size: 12px;
        line-height: 1.7;
    }

    .empty-button {
        display: inline-flex;
        align-items: center;

        gap: 8px;

        margin-top: 20px;

        padding: 10px 16px;

        border-radius: 9px;

        background: #35c6ff;

        color: #07111f;

        font-size: 12px;
        font-weight: 800;

        text-decoration: none;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 1200px) {

        .operarios-stats {
            grid-template-columns:
                repeat(3, 1fr);
        }
    }

    @media (max-width: 900px) {

        .operarios-stats {
            grid-template-columns:
                repeat(2, 1fr);
        }

        .operarios-header {
            align-items: flex-start;

            flex-direction: column;
        }

        .operarios-header-actions {
            width: 100%;

            flex-direction: column;
        }

        .btn-objetivos,
        .btn-nuevo-operario {
            width: 100%;

            box-sizing: border-box;
        }

        .operarios-card-header {
            align-items: flex-start;

            flex-direction: column;
        }

        .filters-form {
            width: 100%;
        }

        .search-wrapper {
            width: 100%;
        }
    }

    @media (max-width: 600px) {

        .operarios-stats {
            grid-template-columns: 1fr;
        }

        .operarios-title {
            font-size: 27px;
        }

        .filter-select,
        .filter-button,
        .clear-filters {
            width: 100%;
        }
    }
</style>


<div class="operarios-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="operarios-header">

        <div>

            <div class="operarios-eyebrow">

                <span class="operarios-eyebrow-dot"></span>

                Gestión del equipo

            </div>


            <h1 class="operarios-title">
                Operarios
            </h1>


            <p class="operarios-description">

                Administra las cuentas, solicitudes y tipos
                de operarios de tu equipo.

            </p>

        </div>


        <div class="operarios-header-actions">

            @if(Route::has('operarios.objetivos'))

                <a
                    href="{{ route('operarios.objetivos') }}"
                    class="btn-objetivos"
                >

                    <i data-lucide="target"></i>

                    Objetivos

                </a>

            @endif


            <a
                href="{{ route('operarios.create') }}"
                class="btn-nuevo-operario"
            >

                <i data-lucide="user-plus"></i>

                Nuevo operario

            </a>

        </div>

    </div>


    {{-- =========================================================
         ALERTAS
    ========================================================== --}}

    @if(session('success'))

        <div class="success-alert">

            <i data-lucide="circle-check"></i>

            <div>
                {{ session('success') }}
            </div>

        </div>

    @endif


    @if($errors->any())

        <div class="error-alert">

            <i data-lucide="circle-alert"></i>

            <div>

                @foreach($errors->all() as $error)

                    <div>
                        {{ $error }}
                    </div>

                @endforeach

            </div>

        </div>

    @endif


    {{-- =========================================================
         ESTADÍSTICAS
    ========================================================== --}}

    <div class="operarios-stats">

        {{-- TOTAL --}}

        <div class="operario-stat">

            <div class="stat-top">

                <div>

                    <div class="stat-label">
                        Total
                    </div>

                    <div class="stat-number">
                        {{ $totalOperarios }}
                    </div>

                </div>

                <div class="stat-icon">

                    <i data-lucide="users-round"></i>

                </div>

            </div>

        </div>


        {{-- ACTIVOS --}}

        <div class="operario-stat">

            <div class="stat-top">

                <div>

                    <div class="stat-label">
                        Activos
                    </div>

                    <div class="stat-number">
                        {{ $operariosActivos }}
                    </div>

                </div>

                <div class="stat-icon green">

                    <i data-lucide="user-round-check"></i>

                </div>

            </div>

        </div>


        {{-- PENDIENTES --}}

        <div class="operario-stat">

            <div class="stat-top">

                <div>

                    <div class="stat-label">
                        Pendientes
                    </div>

                    <div class="stat-number">
                        {{ $operariosPendientes }}
                    </div>

                </div>

                <div class="stat-icon yellow">

                    <i data-lucide="user-round-clock"></i>

                </div>

            </div>

        </div>


        {{-- INACTIVOS --}}

        <div class="operario-stat">

            <div class="stat-top">

                <div>

                    <div class="stat-label">
                        Inactivos
                    </div>

                    <div class="stat-number">
                        {{ $operariosInactivos }}
                    </div>

                </div>

                <div class="stat-icon gray">

                    <i data-lucide="user-round-x"></i>

                </div>

            </div>

        </div>


        {{-- RECHAZADOS --}}

        <div class="operario-stat">

            <div class="stat-top">

                <div>

                    <div class="stat-label">
                        Rechazados
                    </div>

                    <div class="stat-number">
                        {{ $operariosRechazados }}
                    </div>

                </div>

                <div class="stat-icon red">

                    <i data-lucide="user-x"></i>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         LISTADO
    ========================================================== --}}

    <div class="operarios-card">

        <div class="operarios-card-header">

            <div>

                <h2 class="card-title">
                    Equipo de trabajo
                </h2>

                <p class="card-subtitle">

                    Operarios registrados y solicitudes
                    asociadas a tu empresa.

                </p>

            </div>


            {{-- =================================================
                 FILTROS
            ================================================== --}}

            <form
                action="{{ route('operarios.index') }}"
                method="GET"
                class="filters-form"
            >

                <div class="search-wrapper">

                    <i data-lucide="search"></i>

                    <input
                        type="text"
                        name="buscar"
                        value="{{ request('buscar') }}"
                        placeholder="Buscar operario..."
                    >

                </div>


                <select
                    name="estado"
                    class="filter-select"
                >

                    <option value="">
                        Todos los estados
                    </option>

                    <option
                        value="activo"
                        @selected(request('estado') === 'activo')
                    >
                        Activos
                    </option>

                    <option
                        value="inactivo"
                        @selected(request('estado') === 'inactivo')
                    >
                        Inactivos
                    </option>

                    <option
                        value="pendiente"
                        @selected(request('estado') === 'pendiente')
                    >
                        Pendientes
                    </option>

                    <option
                        value="rechazado"
                        @selected(request('estado') === 'rechazado')
                    >
                        Rechazados
                    </option>

                </select>


                <select
                    name="tipo_operario"
                    class="filter-select"
                >

                    <option value="">
                        Todos los tipos
                    </option>

                    @foreach($tiposOperario as $tipo)

                        <option
                            value="{{ $tipo->id_tipo_operario }}"
                            @selected(
                                (string) request('tipo_operario')
                                ===
                                (string) $tipo->id_tipo_operario
                            )
                        >

                            {{ $tipo->nombre }}

                        </option>

                    @endforeach

                </select>


                <button
                    type="submit"
                    class="filter-button"
                >

                    <i data-lucide="list-filter"></i>

                    Filtrar

                </button>


                @if(
                    request()->filled('buscar') ||
                    request()->filled('estado') ||
                    request()->filled('tipo_operario')
                )

                    <a
                        href="{{ route('operarios.index') }}"
                        class="clear-filters"
                    >

                        <i data-lucide="rotate-ccw"></i>

                        Limpiar

                    </a>

                @endif

            </form>

        </div>


        @if($operarios->count() > 0)

            <div class="operarios-table-wrapper">

                <table class="operarios-table">

                    <thead>

                        <tr>

                            <th>
                                Operario
                            </th>

                            <th>
                                Correo
                            </th>

                            <th>
                                Empresa
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Estado
                            </th>

                            <th style="text-align:right;">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($operarios as $operario)

                            @php

                                /*
                                |--------------------------------------------------------------------------
                                | ESTADO DE APROBACIÓN
                                |--------------------------------------------------------------------------
                                */

                                $estadoAprobacion =
                                    $operario->estado_aprobacion;

                                $pendiente =
                                    $estadoAprobacion === 'pendiente';

                                $rechazado =
                                    $estadoAprobacion === 'rechazado';

                                /*
                                |--------------------------------------------------------------------------
                                | NULL se acepta temporalmente para cuentas antiguas.
                                |--------------------------------------------------------------------------
                                */

                                $aprobado =
                                    $estadoAprobacion === 'aprobado'
                                    ||
                                    $estadoAprobacion === null;

                                $activo =
                                    $aprobado
                                    &&
                                    (bool) $operario->estado;

                                $inactivo =
                                    $aprobado
                                    &&
                                    !(bool) $operario->estado;


                                /*
                                |--------------------------------------------------------------------------
                                | INICIAL
                                |--------------------------------------------------------------------------
                                */

                                $inicial =
                                    mb_strtoupper(
                                        mb_substr(
                                            $operario->name ?? 'O',
                                            0,
                                            1
                                        )
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | TIPO
                                |--------------------------------------------------------------------------
                                */

                                $nombreTipo =
                                    $operario->tipoOperario->nombre
                                    ?? null;

                                $tipoLower =
                                    $nombreTipo
                                        ? mb_strtolower($nombreTipo)
                                        : '';

                                $esProspeccion =
                                    str_contains(
                                        $tipoLower,
                                        'prospec'
                                    );
                            @endphp


                            <tr>

                                {{-- =============================================
                                     OPERARIO
                                ============================================== --}}

                                <td>

                                    <div class="operario-user">

                                        <div class="operario-avatar">

                                            {{ $inicial }}

                                            @if($activo)

                                                <span
                                                    class="operario-online"
                                                ></span>

                                            @endif

                                        </div>


                                        <div>

                                            <p class="operario-name">

                                                {{ $operario->name }}

                                            </p>


                                            <p class="operario-id">

                                                ID #{{ $operario->id }}

                                            </p>

                                        </div>

                                    </div>

                                </td>


                                {{-- =============================================
                                     CORREO
                                ============================================== --}}

                                <td>

                                    <div class="operario-email">

                                        <i data-lucide="mail"></i>

                                        {{ $operario->email }}

                                    </div>

                                </td>


                                {{-- =============================================
                                     EMPRESA
                                ============================================== --}}

                                <td>

                                    @if($operario->empresa)

                                        <span class="empresa-badge">

                                            <i data-lucide="building-2"></i>

                                            {{
                                                $operario->empresa->nombre
                                                ??
                                                $operario->empresa->nombre_empresa
                                                ??
                                                $operario->empresa->razon_social
                                                ??
                                                'Empresa'
                                            }}

                                        </span>

                                    @else

                                        <span class="sin-empresa">
                                            Sin empresa
                                        </span>

                                    @endif

                                </td>


                                {{-- =============================================
                                     TIPO
                                ============================================== --}}

                                <td>

                                    @if($nombreTipo)

                                        <span
                                            class="tipo-badge
                                            {{ $esProspeccion ? '' : 'assignment' }}"
                                        >

                                            <i
                                                data-lucide="{{
                                                    $esProspeccion
                                                        ? 'search'
                                                        : 'user-round-check'
                                                }}"
                                            ></i>

                                            {{ $nombreTipo }}

                                        </span>

                                    @else

                                        <span class="tipo-none">

                                            Sin asignar

                                        </span>

                                    @endif

                                </td>


                                {{-- =============================================
                                     ESTADO
                                ============================================== --}}

                                <td>

                                    @if($pendiente)

                                        <span class="status-badge pending">

                                            <span class="status-dot"></span>

                                            Pendiente

                                        </span>


                                    @elseif($rechazado)

                                        <span class="status-badge rejected">

                                            <span class="status-dot"></span>

                                            Rechazado

                                        </span>


                                    @elseif($activo)

                                        <span class="status-badge active">

                                            <span class="status-dot"></span>

                                            Activo

                                        </span>


                                    @else

                                        <span class="status-badge inactive">

                                            <span class="status-dot"></span>

                                            Inactivo

                                        </span>

                                    @endif

                                </td>


                                {{-- =============================================
                                     ACCIONES
                                ============================================== --}}

                                <td>

                                    <div class="operario-actions">


                                        {{-- VER --}}

                                        @if(Route::has('operarios.show'))

                                            <a
                                                href="{{ route(
                                                    'operarios.show',
                                                    $operario
                                                ) }}"
                                                class="action-button view"
                                                title="Ver operario"
                                            >

                                                <i data-lucide="eye"></i>

                                            </a>

                                        @endif


                                        {{-- EDITAR --}}

                                        @if(Route::has('operarios.edit'))

                                            <a
                                                href="{{ route(
                                                    'operarios.edit',
                                                    $operario
                                                ) }}"
                                                class="action-button edit"
                                                title="{{
                                                    $rechazado
                                                        ? 'Corregir solicitud'
                                                        : 'Editar operario'
                                                }}"
                                            >

                                                <i data-lucide="pencil"></i>

                                            </a>

                                        @endif


                                        {{-- =====================================
                                             ACTIVAR / DESACTIVAR
                                             Solamente aprobados.
                                        ====================================== --}}

                                        @if(
                                            $aprobado &&
                                            Route::has(
                                                'operarios.toggleEstado'
                                            )
                                        )

                                            <form
                                                action="{{ route(
                                                    'operarios.toggleEstado',
                                                    $operario
                                                ) }}"
                                                method="POST"
                                            >

                                                @csrf
                                                @method('PATCH')


                                                <button
                                                    type="submit"
                                                    class="action-button toggle"
                                                    title="{{
                                                        $activo
                                                            ? 'Desactivar'
                                                            : 'Activar'
                                                    }}"
                                                >

                                                    <i
                                                        data-lucide="{{
                                                            $activo
                                                                ? 'user-round-x'
                                                                : 'user-round-check'
                                                        }}"
                                                    ></i>

                                                </button>

                                            </form>

                                        @endif


                                        {{-- =====================================
                                             REENVIAR SOLICITUD
                                             Solo rechazados.
                                        ====================================== --}}

                                        @if(
                                            $rechazado &&
                                            Route::has(
                                                'operarios.solicitud.reenviar'
                                            )
                                        )

                                            <form
                                                action="{{ route(
                                                    'operarios.solicitud.reenviar',
                                                    $operario
                                                ) }}"
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        '¿Deseas enviar nuevamente esta solicitud al Super Administrador?'
                                                    );
                                                "
                                            >

                                                @csrf
                                                @method('PATCH')


                                                <button
                                                    type="submit"
                                                    class="action-button resend"
                                                    title="Reenviar solicitud"
                                                >

                                                    <i data-lucide="send"></i>

                                                </button>

                                            </form>

                                        @endif


                                        {{-- ELIMINAR --}}

                                        @if(Route::has('operarios.destroy'))

                                            <form
                                                action="{{ route(
                                                    'operarios.destroy',
                                                    $operario
                                                ) }}"
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        '¿Seguro que deseas eliminar este operario?'
                                                    );
                                                "
                                            >

                                                @csrf
                                                @method('DELETE')


                                                <button
                                                    type="submit"
                                                    class="action-button delete"
                                                    title="Eliminar"
                                                >

                                                    <i data-lucide="trash-2"></i>

                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            {{-- =================================================
                 PAGINACIÓN
            ================================================== --}}

            @if($operarios->hasPages())

                <div class="pagination-container">

                    {{ $operarios->links() }}

                </div>

            @endif


        @else

            {{-- =================================================
                 EMPTY
            ================================================== --}}

            <div class="operarios-empty">

                <div class="empty-icon">

                    @if(
                        request()->filled('buscar') ||
                        request()->filled('estado') ||
                        request()->filled('tipo_operario')
                    )

                        <i data-lucide="search-x"></i>

                    @else

                        <i data-lucide="users-round"></i>

                    @endif

                </div>


                @if(
                    request()->filled('buscar') ||
                    request()->filled('estado') ||
                    request()->filled('tipo_operario')
                )

                    <h3 class="empty-title">

                        No encontramos resultados

                    </h3>


                    <p class="empty-description">

                        No existen operarios que coincidan
                        con los filtros seleccionados.

                    </p>


                    <a
                        href="{{ route('operarios.index') }}"
                        class="empty-button"
                    >

                        <i data-lucide="rotate-ccw"></i>

                        Limpiar filtros

                    </a>


                @else

                    <h3 class="empty-title">

                        Aún no hay operarios

                    </h3>


                    <p class="empty-description">

                        Envía la primera solicitud de operario
                        para comenzar a administrar tu equipo.

                    </p>


                    <a
                        href="{{ route('operarios.create') }}"
                        class="empty-button"
                    >

                        <i data-lucide="user-plus"></i>

                        Nuevo operario

                    </a>

                @endif

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