@extends('layouts.app')

@section('content')

<style>

    .citas-page {
        padding: 32px 26px;
    }

    /* =========================================================
       ENCABEZADO
    ========================================================== */

    .citas-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 28px;
    }

    .citas-kicker {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #38bdf8;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .citas-kicker-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #38bdf8;
        box-shadow: 0 0 12px rgba(56, 189, 248, .7);
    }

    .citas-title {
        color: #f8fafc;
        font-size: 34px;
        font-weight: 800;
        margin: 0;
        letter-spacing: -.5px;
    }

    .citas-subtitle {
        color: #94a3b8;
        margin: 7px 0 0;
        font-size: 15px;
    }

    .btn-nueva-cita {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 12px 20px;
        border-radius: 12px;
        border: 1px solid rgba(56, 189, 248, .35);
        background: rgba(56, 189, 248, .10);
        color: #38bdf8;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
        white-space: nowrap;
    }

    .btn-nueva-cita:hover {
        background: #38bdf8;
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(56, 189, 248, .18);
    }

    .btn-nueva-cita svg {
        width: 17px;
        height: 17px;
    }


    /* =========================================================
       RESUMEN
    ========================================================== */

    .citas-summary {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
        margin-bottom: 24px;
    }

    .summary-card {
        position: relative;
        overflow: hidden;
        background: #111c30;
        border: 1px solid #24344c;
        border-radius: 16px;
        padding: 21px 22px;
        min-height: 125px;
    }

    .summary-card::after {
        content: "";
        position: absolute;
        right: -35px;
        bottom: -55px;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: rgba(56, 189, 248, .045);
    }

    .summary-label {
        color: #7f91aa;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        margin-bottom: 9px;
    }

    .summary-number {
        color: #f8fafc;
        font-size: 30px;
        line-height: 1;
        font-weight: 800;
    }

    .summary-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #38bdf8;
        background: rgba(56, 189, 248, .10);
        z-index: 2;
    }

    .summary-icon svg {
        width: 21px;
        height: 21px;
    }

    .summary-card.green .summary-icon {
        color: #34d399;
        background: rgba(52, 211, 153, .10);
    }


    /* =========================================================
       ALERTAS
    ========================================================== */

    .citas-alert {
        border-radius: 12px;
        border: 1px solid;
        padding: 13px 17px;
        margin-bottom: 20px;
    }

    .citas-alert-success {
        background: rgba(34, 197, 94, .08);
        border-color: rgba(34, 197, 94, .25);
        color: #86efac;
    }

    .citas-alert-danger {
        background: rgba(239, 68, 68, .08);
        border-color: rgba(239, 68, 68, .25);
        color: #fca5a5;
    }

    .citas-alert svg {
        width: 16px;
        height: 16px;
        vertical-align: middle;
    }


    /* =========================================================
       FILTROS
    ========================================================== */

    .citas-card {
        background: #111c30;
        border: 1px solid #24344c;
        border-radius: 16px;
        overflow: hidden;
    }

    .filters-card {
        margin-bottom: 22px;
    }

    .card-heading {
        padding: 19px 22px;
        border-bottom: 1px solid #24344c;
    }

    .card-heading-title {
        color: #f8fafc;
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .card-heading-title svg {
        width: 17px;
        height: 17px;
        color: #38bdf8;
    }

    .card-heading-subtitle {
        color: #71829a;
        font-size: 13px;
        margin: 4px 0 0;
    }

    .filters-body {
        padding: 20px 22px;
    }

    .filter-label {
        display: block;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        margin-bottom: 8px;
    }

    .filter-control {
        width: 100%;
        height: 43px;
        box-sizing: border-box;
        border-radius: 10px;
        border: 1px solid #334155;
        background: #0b1424;
        color: #f8fafc;
        padding: 0 13px;
        outline: none;
        transition: .2s ease;
    }

    .filter-control:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, .08);
    }

    .filter-control::placeholder {
        color: #52627a;
    }

    .filter-control option {
        background: #0b1424;
        color: #f8fafc;
    }

    .btn-filter {
        height: 43px;
        border: 0;
        border-radius: 10px;
        padding: 0 17px;
        background: #38bdf8;
        color: #07111f;
        font-weight: 800;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn-filter:hover {
        background: #67cef9;
    }

    .btn-filter svg {
        width: 15px;
        height: 15px;
        vertical-align: middle;
    }

    .btn-clear {
        height: 43px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 15px;
        border-radius: 10px;
        border: 1px solid #334155;
        color: #94a3b8;
        text-decoration: none;
        font-weight: 600;
        transition: .2s ease;
    }

    .btn-clear:hover {
        color: #f8fafc;
        border-color: #64748b;
        background: rgba(148, 163, 184, .06);
    }

    .btn-clear svg {
        width: 15px;
        height: 15px;
    }


    /* =========================================================
       TABLA
    ========================================================== */

    .table-header {
        padding: 20px 22px;
        border-bottom: 1px solid #24344c;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        color: #f8fafc;
        font-size: 17px;
        font-weight: 750;
        margin: 0;
    }

    .table-count {
        color: #64748b;
        font-size: 13px;
    }

    .citas-table {
        width: 100%;
        border-collapse: collapse;
    }

    .citas-table thead th {
        background: #0d1728;
        color: #71829a;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.1px;
        text-transform: uppercase;
        padding: 14px 18px;
        border-bottom: 1px solid #24344c;
        white-space: nowrap;
    }

    .citas-table tbody tr {
        border-bottom: 1px solid rgba(51, 65, 85, .55);
        transition: .15s ease;
    }

    .citas-table tbody tr:last-child {
        border-bottom: 0;
    }

    .citas-table tbody tr:hover {
        background: rgba(56, 189, 248, .025);
    }

    .citas-table tbody td {
        padding: 17px 18px;
        color: #cbd5e1;
        font-size: 13px;
        vertical-align: middle;
    }


    /* =========================================================
       CLIENTE
    ========================================================== */

    .cliente-name {
        color: #f8fafc;
        font-weight: 700;
        font-size: 14px;
    }

    .cliente-company {
        color: #71829a;
        font-size: 12px;
        margin-top: 3px;
    }

    .cliente-company svg {
        width: 13px;
        height: 13px;
        vertical-align: -2px;
    }


    /* =========================================================
       OPERARIO
    ========================================================== */

    .operario-name {
        color: #cbd5e1;
        font-weight: 600;
    }

    .operario-name svg {
        width: 14px;
        height: 14px;
        color: #38bdf8;
        vertical-align: -2px;
    }


    /* =========================================================
       FECHA
    ========================================================== */

    .date-main {
        color: #f8fafc;
        font-weight: 650;
    }


    /* =========================================================
       HORARIO
    ========================================================== */

    .time-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #38bdf8;
        background: rgba(56, 189, 248, .08);
        border: 1px solid rgba(56, 189, 248, .15);
        padding: 6px 9px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 12px;
    }

    .time-badge svg {
        width: 13px;
        height: 13px;
    }


    /* =========================================================
       ESTADO
    ========================================================== */

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(56, 189, 248, .09);
        border: 1px solid rgba(56, 189, 248, .16);
        color: #7dd3fc;
        font-size: 11px;
        font-weight: 750;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }


    /* =========================================================
       DESCRIPCIÓN
    ========================================================== */

    .descripcion-main {
        color: #f8fafc;
        font-weight: 650;
        font-size: 13px;
        max-width: 240px;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .descripcion-secondary {
        color: #71829a;
        font-size: 11px;
        margin-top: 4px;
        max-width: 240px;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .descripcion-label {
        color: #52627a;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-right: 4px;
    }

    .sin-descripcion {
        color: #64748b;
        font-weight: 500;
        font-style: italic;
    }


    /* =========================================================
       ACCIONES
    ========================================================== */

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 6px;
    }

    .action-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        text-decoration: none;
        background: transparent;
        border: 1px solid #334155;
        transition: .18s ease;
        cursor: pointer;
        box-sizing: border-box;
    }

    .action-btn svg {
        width: 15px;
        height: 15px;
    }

    .action-view {
        color: #38bdf8;
    }

    .action-view:hover {
        background: rgba(56, 189, 248, .10);
        border-color: rgba(56, 189, 248, .4);
    }

    .action-edit {
        color: #fbbf24;
    }

    .action-edit:hover {
        background: rgba(251, 191, 36, .10);
        border-color: rgba(251, 191, 36, .4);
    }

    .action-delete {
        color: #fb7185;
    }

    .action-delete:hover {
        background: rgba(251, 113, 133, .10);
        border-color: rgba(251, 113, 133, .4);
    }


    /* =========================================================
       VACÍO
    ========================================================== */

    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 66px;
        height: 66px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: rgba(56, 189, 248, .07);
        border: 1px solid rgba(56, 189, 248, .12);
        color: #38bdf8;
        margin-bottom: 16px;
    }

    .empty-icon svg {
        width: 27px;
        height: 27px;
    }

    .empty-title {
        color: #f8fafc;
        font-weight: 750;
        font-size: 17px;
        margin-bottom: 5px;
    }

    .empty-text {
        color: #64748b;
        font-size: 13px;
        margin: 0;
    }

    .pagination-wrapper {
        padding: 18px 22px;
        border-top: 1px solid #24344c;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 1100px) {

        .citas-summary {
            grid-template-columns: 1fr;
        }

        .citas-header {
            align-items: stretch;
            flex-direction: column;
        }

        .btn-nueva-cita {
            align-self: flex-start;
        }
    }

    @media (max-width: 768px) {

        .citas-page {
            padding: 22px 15px;
        }

        .citas-title {
            font-size: 28px;
        }

        .filters-body {
            padding: 16px;
        }

        .table-header {
            padding: 17px;
        }

        .citas-table {
            min-width: 950px;
        }
    }

</style>


<div class="citas-page">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}

    <div class="citas-header">

        <div>

            <div class="citas-kicker">
                <span class="citas-kicker-dot"></span>
                Gestión de citas
            </div>

            <h1 class="citas-title">
                Citas
            </h1>

            <p class="citas-subtitle">
                Consulta y administra las citas de los operarios de tu empresa.
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

            {{ session('exito') }}

        </div>

    @endif


    @if($errors->any())

        <div class="citas-alert citas-alert-danger">

            <div class="fw-bold mb-1">

                <i data-lucide="triangle-alert"></i>

                Revisa los siguientes datos:

            </div>

            <ul class="mb-0 ps-4">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         RESUMEN
    ========================================================== --}}

    <div class="citas-summary">

        <div class="summary-card">

            <div class="summary-label">
                Citas mostradas
            </div>

            <div class="summary-number">
                {{ $citas->total() }}
            </div>

            <div class="summary-icon">
                <i data-lucide="calendar-days"></i>
            </div>

        </div>


        <div class="summary-card green">

            <div class="summary-label">
                Operarios disponibles
            </div>

            <div class="summary-number">
                {{ $operarios->count() }}
            </div>

            <div class="summary-icon">
                <i data-lucide="users"></i>
            </div>

        </div>

    </div>


    {{-- =========================================================
         FILTROS
    ========================================================== --}}

    <div class="citas-card filters-card">

        <div class="card-heading">

            <h2 class="card-heading-title">

                <i data-lucide="funnel"></i>

                <span style="margin-left:8px;">
                    Filtrar citas
                </span>

            </h2>

            <p class="card-heading-subtitle">
                Utiliza los filtros para localizar una cita específica.
            </p>

        </div>


        <div class="filters-body">

            <form
                method="GET"
                action="{{ route('citas.index') }}"
            >

                <div class="row g-3">

                    {{-- BUSCAR --}}

                    <div class="col-12 col-lg-4">

                        <label class="filter-label">
                            Buscar cliente
                        </label>

                        <input
                            type="text"
                            name="buscar"
                            value="{{ $buscar }}"
                            class="filter-control"
                            placeholder="Nombre, empresa, correo o teléfono"
                        >

                    </div>


                    {{-- OPERARIO --}}

                    <div class="col-12 col-md-6 col-lg-2">

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
                                    {{ request('operario') == $operario->id ? 'selected' : '' }}
                                >
                                    {{ $operario->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- ESTADO --}}

                    <div class="col-12 col-md-6 col-lg-2">

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
                                    {{ request('estado') == $estado->id_estado_cita ? 'selected' : '' }}
                                >
                                    {{ $estado->nombre }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- DESDE --}}

                    <div class="col-12 col-md-6 col-lg-2">

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


                    {{-- HASTA --}}

                    <div class="col-12 col-md-6 col-lg-2">

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


                    {{-- BOTONES --}}

                    <div class="col-12">

                        <div class="d-flex flex-wrap gap-2">

                            <button
                                type="submit"
                                class="btn-filter"
                            >

                                <i data-lucide="search"></i>

                                <span style="margin-left:5px;">
                                    Filtrar
                                </span>

                            </button>


                            <a
                                href="{{ route('citas.index') }}"
                                class="btn-clear"
                            >

                                <i data-lucide="rotate-ccw"></i>

                                Limpiar

                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
         TABLA
    ========================================================== --}}

    <div class="citas-card">

        <div class="table-header">

            <div>

                <h2 class="table-title">
                    Citas registradas
                </h2>

                <span class="table-count">

                    {{ $citas->total() }}

                    {{ $citas->total() == 1
                        ? 'cita encontrada'
                        : 'citas encontradas'
                    }}

                </span>

            </div>

        </div>


        <div class="table-responsive">

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
                            Descripción
                        </th>

                        <th class="text-end">
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

                                    <div class="cliente-name">

                                        {{ $cita->cliente->nombre }}
                                        {{ $cita->cliente->apellido_paterno }}
                                        {{ $cita->cliente->apellido_materno }}

                                    </div>


                                    @if($cita->cliente->empresa)

                                        <div class="cliente-company">

                                            <i data-lucide="building-2"></i>

                                            {{ $cita->cliente->empresa }}

                                        </div>

                                    @endif

                                @else

                                    <span class="text-secondary">
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

                                    <span class="text-secondary">
                                        Sin asignar
                                    </span>

                                @endif

                            </td>


                            {{-- FECHA --}}

                            <td>

                                <span class="date-main">

                                    {{ $cita->fecha_hora_inicio->format('d/m/Y') }}

                                </span>

                            </td>


                            {{-- HORARIO --}}

                            <td>

                                <span class="time-badge">

                                    <i data-lucide="clock-3"></i>

                                    {{ $cita->fecha_hora_inicio->format('H:i') }}

                                    @if($cita->fecha_hora_fin)

                                        -

                                        {{ $cita->fecha_hora_fin->format('H:i') }}

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

                                    <span class="text-secondary">
                                        Sin estado
                                    </span>

                                @endif

                            </td>


                            {{-- DESCRIPCIÓN: MOTIVO + OBSERVACIONES --}}

                            <td>

                                @if($cita->motivo)

                                    <div
                                        class="descripcion-main"
                                        title="{{ $cita->motivo }}"
                                    >
                                        <span class="descripcion-label">
                                            Motivo:
                                        </span>

                                        {{ $cita->motivo }}
                                    </div>

                                @else

                                    <div class="descripcion-main sin-descripcion">

                                        Sin motivo

                                    </div>

                                @endif


                                @if($cita->observaciones)

                                    <div
                                        class="descripcion-secondary"
                                        title="{{ $cita->observaciones }}"
                                    >
                                        <span class="descripcion-label">
                                            Nota:
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
                                        href="{{ route('citas.show', $cita->id_cita) }}"
                                        class="action-btn action-view"
                                        title="Ver cita"
                                    >
                                        <i data-lucide="eye"></i>
                                    </a>


                                    {{-- EDITAR --}}

                                    <a
                                        href="{{ route('citas.edit', $cita->id_cita) }}"
                                        class="action-btn action-edit"
                                        title="Editar cita"
                                    >
                                        <i data-lucide="pencil"></i>
                                    </a>


                                    {{-- ELIMINAR --}}

                                    <form
                                        action="{{ route('citas.destroy', $cita->id_cita) }}"
                                        method="POST"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar esta cita?');"
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

                            <td
                                colspan="7"
                                class="p-0"
                            >

                                <div class="empty-state">

                                    <div class="empty-icon">

                                        <i data-lucide="calendar-x-2"></i>

                                    </div>

                                    <div class="empty-title">
                                        No hay citas registradas
                                    </div>

                                    <p class="empty-text">
                                        No se encontraron citas con los filtros seleccionados.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINACIÓN --}}

        @if($citas->hasPages())

            <div class="pagination-wrapper">

                {{ $citas->links() }}

            </div>

        @endif

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