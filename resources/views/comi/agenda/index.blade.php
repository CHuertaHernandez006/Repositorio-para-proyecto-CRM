@extends('layouts.app')

@section('title', 'Agenda COMI - COMICenter')

@section('content')

<style>

    .comi-agenda-page,
    .comi-agenda-page *,
    .comi-agenda-page *::before,
    .comi-agenda-page *::after {
        box-sizing: border-box;
    }

    .comi-agenda-page {
        width: 100%;
        padding: 28px;
        color: #e2e8f0;
    }

    .ca-header {
        margin-bottom: 26px;
    }

    .ca-eyebrow {
        margin: 0 0 5px;
        color: #38bdf8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12em;
    }

    .ca-title {
        margin: 0;
        color: #f8fafc;
        font-size: 28px;
        font-weight: 750;
    }

    .ca-description {
        max-width: 720px;
        margin: 7px 0 0;
        color: #94a3b8;
        font-size: 14px;
        line-height: 1.5;
    }

    .ca-alert {
        margin-bottom: 18px;
        padding: 13px 15px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
    }

    .ca-alert-success {
        background: rgba(16, 185, 129, .12);
        color: #6ee7b7;
        border: 1px solid rgba(16, 185, 129, .30);
    }

    .ca-alert-error {
        background: rgba(239, 68, 68, .12);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, .30);
    }

    .ca-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .ca-summary-card {
        padding: 18px;
        background: #131e30;
        border: 1px solid #2b3a50;
        border-radius: 12px;
    }

    .ca-summary-label {
        display: block;
        margin-bottom: 8px;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 600;
    }

    .ca-summary-value {
        color: #f8fafc;
        font-size: 25px;
        font-weight: 750;
    }

    .ca-filter-card {
        margin-bottom: 22px;
        padding: 18px;
        background: #131e30;
        border: 1px solid #2b3a50;
        border-radius: 12px;
    }

    .ca-filter-form {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr))
            auto
            auto;
        gap: 12px;
        align-items: end;
    }

    .ca-field label {
        display: block;
        margin-bottom: 7px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .ca-input,
    .ca-select {
        width: 100%;
        height: 41px;
        padding: 0 12px;
        background: #0f172a;
        color: #e2e8f0;
        border: 1px solid #334155;
        border-radius: 8px;
        outline: none;
    }

    .ca-button {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        padding: 0 13px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 650;
        cursor: pointer;
    }

    .ca-button-primary {
        background: #0284c7;
        color: white;
        border: 1px solid #0284c7;
    }

    .ca-button-secondary {
        background: #1e293b;
        color: #cbd5e1;
        border: 1px solid #334155;
    }

    .ca-button-confirm {
        background: rgba(16, 185, 129, .14);
        color: #6ee7b7;
        border: 1px solid rgba(16, 185, 129, .34);
    }

    .ca-button-reject {
        background: rgba(239, 68, 68, .12);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, .30);
    }

    .ca-button-reprogram {
        background: rgba(56, 189, 248, .12);
        color: #7dd3fc;
        border: 1px solid rgba(56, 189, 248, .30);
    }

    .ca-card {
        overflow: hidden;
        background: #131e30;
        border: 1px solid #2b3a50;
        border-radius: 12px;
    }

    .ca-table-wrapper {
        overflow-x: auto;
    }

    .ca-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ca-table th {
        padding: 13px 16px;
        background: #172235;
        color: #94a3b8;
        border-bottom: 1px solid #2b3a50;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ca-table td {
        padding: 15px 16px;
        border-bottom: 1px solid #243247;
        vertical-align: middle;
        font-size: 13px;
    }

    .ca-primary {
        display: block;
        color: #f8fafc;
        font-weight: 650;
    }

    .ca-secondary {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 11px;
    }

    .ca-status {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .ca-status-pendiente {
        background: rgba(245, 158, 11, .12);
        color: #fbbf24;
    }

    .ca-status-confirmada {
        background: rgba(16, 185, 129, .12);
        color: #6ee7b7;
    }

    .ca-status-rechazada,
    .ca-status-cancelada {
        background: rgba(239, 68, 68, .12);
        color: #fca5a5;
    }

    .ca-status-reprogramada {
        background: rgba(56, 189, 248, .12);
        color: #7dd3fc;
    }

    .ca-actions {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .ca-actions form {
        margin: 0;
    }

    .ca-empty {
        padding: 50px 24px;
        text-align: center;
    }

    .ca-empty h2 {
        color: #e2e8f0;
    }

    .ca-empty p {
        color: #64748b;
    }

    .ca-pagination {
        display: flex;
        justify-content: space-between;
        padding: 15px;
        border-top: 1px solid #2b3a50;
    }

    .ca-page-link {
        display: inline-flex;
        padding: 8px 12px;
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 7px;
        color: #cbd5e1;
        text-decoration: none;
    }

    @media (max-width: 1100px) {
        .ca-summary {
            grid-template-columns: repeat(2, 1fr);
        }

        .ca-filter-form {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 650px) {
        .ca-summary,
        .ca-filter-form {
            grid-template-columns: 1fr;
        }
    }

</style>


<section class="comi-agenda-page">

    <header class="ca-header">

        <p class="ca-eyebrow">
            COMICenter / Integración COMI
        </p>

        <h1 class="ca-title">
            Agenda COMI
        </h1>

        <p class="ca-description">
            Consulta y administra las citas generadas por COMI.
        </p>

    </header>


    @if(session('success'))

        <div class="ca-alert ca-alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="ca-alert ca-alert-error">
            {{ session('error') }}
        </div>

    @endif


    @include('comi.agenda._legacy')


    @if(!$agendas->isEmpty())

        <div style="margin: 26px 0 14px;">
            <p class="ca-eyebrow">
                Nuevo modelo multi-proyecto
            </p>

            <h2 style="margin: 0; color: #f8fafc; font-size: 20px;">
                Agenda multi-proyecto
            </h2>
        </div>

    <div class="ca-summary">

        <div class="ca-summary-card">
            <span class="ca-summary-label">
                Total
            </span>
            <span class="ca-summary-value">
                {{ $resumen['total'] }}
            </span>
        </div>

        <div class="ca-summary-card">
            <span class="ca-summary-label">
                Pendientes
            </span>
            <span class="ca-summary-value">
                {{ $resumen['pendientes'] }}
            </span>
        </div>

        <div class="ca-summary-card">
            <span class="ca-summary-label">
                Confirmadas
            </span>
            <span class="ca-summary-value">
                {{ $resumen['confirmadas'] }}
            </span>
        </div>

        <div class="ca-summary-card">
            <span class="ca-summary-label">
                Reprogramadas
            </span>
            <span class="ca-summary-value">
                {{ $resumen['reprogramadas'] }}
            </span>
        </div>

    </div>


    <div class="ca-filter-card">

        <form
            method="GET"
            action="{{ route('comi.agenda.index') }}"
            class="ca-filter-form"
        >

            <div class="ca-field">
                <label>Proyecto</label>

                <select
                    name="proyecto"
                    class="ca-select"
                >
                    <option value="">
                        Todos
                    </option>

                    @foreach($proyectos as $proyecto)

                        <option
                            value="{{ $proyecto->id_proyecto }}"
                            @selected(
                                request('proyecto')
                                == $proyecto->id_proyecto
                            )
                        >
                            {{ $proyecto->nombre }}
                        </option>

                    @endforeach
                </select>
            </div>


            <div class="ca-field">
                <label>Agenda</label>

                <select
                    name="agenda"
                    class="ca-select"
                >
                    <option value="">
                        Todas
                    </option>

                    @foreach($agendas as $agenda)

                        <option
                            value="{{ $agenda->id_agenda }}"
                            @selected(
                                request('agenda')
                                == $agenda->id_agenda
                            )
                        >
                            {{ $agenda->nombre }}
                        </option>

                    @endforeach
                </select>
            </div>


            <div class="ca-field">
                <label>Estado</label>

                <select
                    name="estado"
                    class="ca-select"
                >
                    <option value="">
                        Todos
                    </option>

                    <option
                        value="pendiente_aprobacion"
                        @selected(
                            request('estado')
                            === 'pendiente_aprobacion'
                        )
                    >
                        Pendiente
                    </option>

                    <option
                        value="confirmada"
                        @selected(
                            request('estado')
                            === 'confirmada'
                        )
                    >
                        Confirmada
                    </option>

                    <option
                        value="rechazada"
                        @selected(
                            request('estado')
                            === 'rechazada'
                        )
                    >
                        Rechazada
                    </option>

                    <option
                        value="cancelada"
                        @selected(
                            request('estado')
                            === 'cancelada'
                        )
                    >
                        Cancelada
                    </option>

                    <option
                        value="reprogramada"
                        @selected(
                            request('estado')
                            === 'reprogramada'
                        )
                    >
                        Reprogramada
                    </option>
                </select>
            </div>


            <div class="ca-field">
                <label>Fecha</label>

                <input
                    type="date"
                    name="fecha"
                    value="{{ request('fecha') }}"
                    class="ca-input"
                >
            </div>


            <button
                type="submit"
                class="ca-button ca-button-primary"
            >
                Filtrar
            </button>


            <a
                href="{{ route('comi.agenda.index') }}"
                class="ca-button ca-button-secondary"
            >
                Limpiar
            </a>

        </form>

    </div>


    <div class="ca-card">

        @if($agendas->isEmpty())

            <div class="ca-empty">

                <h2>
                    No tienes agendas asignadas
                </h2>

                <p>
                    Tu usuario todavía no tiene permiso
                    para visualizar una agenda COMI.
                </p>

            </div>

        @elseif($citas->isEmpty())

            <div class="ca-empty">

                <h2>
                    No hay citas para mostrar
                </h2>

                <p>
                    No existen citas que coincidan
                    con los filtros seleccionados.
                </p>

            </div>

        @else

            <div class="ca-table-wrapper">

                <table class="ca-table">

                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Proyecto</th>
                            <th>Agenda</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($citas as $cita)

                            @php

                                $clienteNombre = trim(
                                    ($cita->cliente?->nombre ?? '')
                                    . ' '
                                    . ($cita->cliente?->apellido_paterno ?? '')
                                    . ' '
                                    . ($cita->cliente?->apellido_materno ?? '')
                                );

                                if ($clienteNombre === '') {

                                    $clienteNombre =
                                        $cita
                                            ->solicitudServicio
                                            ?->nombre_contacto
                                        ?? 'Sin cliente';

                                }

                                $estadoTexto = match(
                                    $cita->estado
                                ) {
                                    'pendiente_aprobacion'
                                        => 'Pendiente',

                                    'confirmada'
                                        => 'Confirmada',

                                    'rechazada'
                                        => 'Rechazada',

                                    'cancelada'
                                        => 'Cancelada',

                                    'reprogramada'
                                        => 'Reprogramada',

                                    default
                                        => $cita->estado,
                                };

                                $estadoClase = match(
                                    $cita->estado
                                ) {
                                    'pendiente_aprobacion'
                                        => 'ca-status-pendiente',

                                    'confirmada'
                                        => 'ca-status-confirmada',

                                    'rechazada'
                                        => 'ca-status-rechazada',

                                    'cancelada'
                                        => 'ca-status-cancelada',

                                    'reprogramada'
                                        => 'ca-status-reprogramada',

                                    default
                                        => '',
                                };

                                $puedeGestionar =
                                    $agendasGestionables
                                        ->contains(
                                            (int) $cita->id_agenda
                                        );

                            @endphp


                            <tr>

                                <td>

                                    <span class="ca-primary">
                                        {{
                                            $cita
                                                ->fecha_hora_inicio
                                                ?->timezone(
                                                    $cita->agenda
                                                        ?->zona_horaria
                                                    ?? config(
                                                        'app.timezone'
                                                    )
                                                )
                                                ->format(
                                                    'd/m/Y'
                                                )
                                        }}
                                    </span>

                                    <span class="ca-secondary">
                                        {{
                                            $cita
                                                ->fecha_hora_inicio
                                                ?->timezone(
                                                    $cita->agenda
                                                        ?->zona_horaria
                                                    ?? config(
                                                        'app.timezone'
                                                    )
                                                )
                                                ->format(
                                                    'H:i'
                                                )
                                        }}
                                    </span>

                                </td>


                                <td>
                                    <span class="ca-primary">
                                        {{ $clienteNombre }}
                                    </span>

                                    @if($cita->cliente?->correo)
                                        <span class="ca-secondary">
                                            {{ $cita->cliente->correo }}
                                        </span>
                                    @endif
                                </td>


                                <td>
                                    {{
                                        $cita->proyecto?->nombre
                                        ?? 'Sin proyecto'
                                    }}
                                </td>


                                <td>
                                    {{
                                        $cita->agenda?->nombre
                                        ?? 'Sin agenda'
                                    }}
                                </td>


                                <td>
                                    {{
                                        $cita->motivo
                                        ?: 'Sin motivo'
                                    }}
                                </td>


                                <td>

                                    <span
                                        class="
                                            ca-status
                                            {{ $estadoClase }}
                                        "
                                    >
                                        {{ $estadoTexto }}
                                    </span>

                                </td>


                                <td>

                                    @if(
                                        $puedeGestionar
                                        &&
                                        $cita->estado ===
                                            'pendiente_aprobacion'
                                        &&
                                        $cita->id_conversacion
                                    )

                                        <div class="ca-actions">

                                            <form
                                                method="POST"
                                                action="{{
                                                    route(
                                                        'comi.agenda.estado',
                                                        $cita->id_cita
                                                    )
                                                }}"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <input
                                                    type="hidden"
                                                    name="estado"
                                                    value="confirmada"
                                                >

                                                <button
                                                    class="
                                                        ca-button
                                                        ca-button-confirm
                                                    "
                                                    type="submit"
                                                >
                                                    Confirmar
                                                </button>
                                            </form>


                                            <form
                                                method="POST"
                                                action="{{
                                                    route(
                                                        'comi.agenda.estado',
                                                        $cita->id_cita
                                                    )
                                                }}"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <input
                                                    type="hidden"
                                                    name="estado"
                                                    value="rechazada"
                                                >

                                                <button
                                                    class="
                                                        ca-button
                                                        ca-button-reject
                                                    "
                                                    type="submit"
                                                >
                                                    Rechazar
                                                </button>
                                            </form>


                                            <a
                                                href="{{
                                                    route(
                                                        'comi.agenda.reprogramar.form',
                                                        $cita->id_cita
                                                    )
                                                }}"
                                                class="
                                                    ca-button
                                                    ca-button-reprogram
                                                "
                                            >
                                                Reprogramar
                                            </a>

                                        </div>

                                    @elseif(
                                        $cita->id_solicitud
                                    )

                                        <a
                                            href="{{
                                                route(
                                                    'comi.servicio.index'
                                                )
                                            }}"
                                            class="
                                                ca-button
                                                ca-button-secondary
                                            "
                                        >
                                            Ver servicio
                                        </a>

                                    @else

                                        <span class="ca-secondary">
                                            Sin acciones pendientes
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div class="ca-pagination">

                <span>
                    Página
                    {{ $citas->currentPage() }}
                    de
                    {{ $citas->lastPage() }}
                </span>

                <div>

                    @if($citas->previousPageUrl())

                        <a
                            href="{{ $citas->previousPageUrl() }}"
                            class="ca-page-link"
                        >
                            Anterior
                        </a>

                    @endif

                    @if($citas->nextPageUrl())

                        <a
                            href="{{ $citas->nextPageUrl() }}"
                            class="ca-page-link"
                        >
                            Siguiente
                        </a>

                    @endif

                </div>

            </div>

        @endif

    </div>

    @endif

</section>

@endsection