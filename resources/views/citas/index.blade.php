@extends('layouts.app')

@section('content')

<style>

    .citas-page {
        padding: 32px 26px;
    }

    /* =========================================================
       ENCABEZADO
       ========================================================= */

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


    /* =========================================================
       RESUMEN
       ========================================================= */

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
        font-size: 20px;
        z-index: 2;
    }

    .summary-card.green .summary-icon {
        color: #34d399;
        background: rgba(52, 211, 153, .10);
    }


    /* =========================================================
       ALERTAS
       ========================================================= */

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


    /* =========================================================
       FILTROS
       ========================================================= */

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

    .btn-filter {
        height: 43px;
        border: 0;
        border-radius: 10px;
        padding: 0 17px;
        background: #38bdf8;
        color: #07111f;
        font-weight: 800;
    }

    .btn-filter:hover {
        background: #67cef9;
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
    }

    .btn-clear:hover {
        color: #f8fafc;
        border-color: #64748b;
        background: rgba(148, 163, 184, .06);
    }


    /* =========================================================
       TABLA
       ========================================================= */

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

    .operario-name {
        color: #cbd5e1;
        font-weight: 600;
    }

    .date-main {
        color: #f8fafc;
        font-weight: 650;
    }

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

    .motivo {
        color: #94a3b8;
        max-width: 180px;
    }

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
        font-size: 27px;
        margin-bottom: 16px;
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
       ========================================================= */

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
         ========================================================= --}}

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
            <i class="bi bi-calendar-plus"></i>
            Nueva cita
        </a>

    </div>


    {{-- =========================================================
         MENSAJES
         ========================================================= --}}

    @if(session('exito'))

        <div class="citas-alert citas-alert-success">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('exito') }}
        </div>

    @endif


    @if($errors->any())

        <div class="citas-alert citas-alert-danger">

            <div class="fw-bold mb-1">
                <i class="bi bi-exclamation-triangle me-2"></i>
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
         ========================================================= --}}

    <div class="citas-summary">

        {{-- CITAS MOSTRADAS --}}

        <div class="summary-card">

            <div class="summary-label">
                Citas mostradas
            </div>

            <div class="summary-number">
                {{ $citas->total() }}
            </div>

            <div class="summary-icon">
                <i class="bi bi-calendar3"></i>
            </div>

        </div>


        {{-- OPERARIOS DISPONIBLES --}}

        <div class="summary-card green">

            <div class="summary-label">
                Operarios disponibles
            </div>

            <div class="summary-number">
                {{ $operarios->count() }}
            </div>

            <div class="summary-icon">
                <i class="bi bi-people"></i>
            </div>

        </div>

    </div>


    {{-- =========================================================
         FILTROS
         ========================================================= --}}

    <div class="citas-card filters-card">

        <div class="card-heading">

            <h2 class="card-heading-title">
                <i class="bi bi-funnel me-2 text-info"></i>
                Filtrar citas
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
                                <i class="bi bi-search me-1"></i>
                                Filtrar
                            </button>

                            <a
                                href="{{ route('citas.index') }}"
                                class="btn-clear"
                            >
                                <i class="bi bi-arrow-counterclockwise"></i>
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
         ========================================================= --}}

    <div class="citas-card">

        <div class="table-header">

            <div>

                <h2 class="table-title">
                    Citas registradas
                </h2>

                <span class="table-count">
                    {{ $citas->total() }}
                    {{ $citas->total() == 1 ? 'cita encontrada' : 'citas encontradas' }}
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
                            Motivo
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

                                            <i class="bi bi-building me-1"></i>

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

                                        <i class="bi bi-person me-1 text-info"></i>

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

                                    <i class="bi bi-clock"></i>

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


                            {{-- MOTIVO --}}

                            <td>

                                <span
                                    class="motivo"
                                    title="{{ $cita->motivo }}"
                                >
                                    {{ $cita->motivo ?: 'Sin motivo' }}
                                </span>

                            </td>


                            {{-- ACCIONES --}}

                            <td>

                                <div class="actions">

                                    <a
                                        href="{{ route('citas.show', $cita->id_cita) }}"
                                        class="action-btn action-view"
                                        title="Ver cita"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>


                                    <a
                                        href="{{ route('citas.edit', $cita->id_cita) }}"
                                        class="action-btn action-edit"
                                        title="Editar cita"
                                    >
                                        <i class="bi bi-pencil"></i>
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
                                            class="action-btn action-delete"
                                            title="Eliminar cita"
                                        >
                                            <i class="bi bi-trash"></i>
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
                                        <i class="bi bi-calendar-x"></i>
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

@endsection