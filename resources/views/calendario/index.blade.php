{{-- resources/views/calendario/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Calendario - CRM')
@section('header-title', 'Calendario')

@section('content')
@php
    $buscar = $buscar ?? '';
    $totalResultados = isset($citas) ? (method_exists($citas, 'total') ? $citas->total() : $citas->count()) : 0;
    if (isset($citas) && method_exists($citas, 'appends')) {
        $citas->appends(['buscar' => $buscar]);
    }

    // Sólo se serializan las citas autorizadas que ya envió el controlador.
    // No se realizan consultas adicionales ni se alteran filtros de empresa/agente.
    $eventosCalendario = [];
    $sinFechaCalendario = 0;
    $zonaCalendario = config('app.timezone', 'UTC');
    foreach (($citas ?? []) as $citaCalendario) {
        $valorInicio = $citaCalendario->fecha_hora_inicio ?? $citaCalendario->fecha ?? null;
        if (!$valorInicio) { $sinFechaCalendario++; continue; }
        try {
            $inicio = \Carbon\Carbon::parse($valorInicio, $zonaCalendario)->setTimezone($zonaCalendario);
        } catch (\Throwable $errorFecha) {
            $sinFechaCalendario++;
            continue;
        }
        $soloFecha = is_string($valorInicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($valorInicio));
        $fin = null;
        // Si el modelo tiene una hora de fin, se respeta; no se inventa una duración real.
        if (!empty($citaCalendario->fecha_hora_fin)) {
            try {
                $finCandidato = \Carbon\Carbon::parse($citaCalendario->fecha_hora_fin, $zonaCalendario)->setTimezone($zonaCalendario);
                if ($finCandidato->greaterThan($inicio)) { $fin = $finCandidato; }
            } catch (\Throwable $errorFechaFin) { /* Se muestra únicamente el inicio. */ }
        }
        $idCalendario = $citaCalendario->id_cita ?? $citaCalendario->id;
        $eventosCalendario[] = [
            'id' => (string) $idCalendario,
            'title' => $citaCalendario->motivo ?? $citaCalendario->asunto ?? 'Sin asunto',
            // Fechas sin offset para conservar la hora de la aplicación en cualquier navegador.
            'start' => $soloFecha ? $inicio->format('Y-m-d') : $inicio->format('Y-m-d\TH:i:s'),
            'end' => (!$soloFecha && $fin) ? $fin->format('Y-m-d\TH:i:s') : null,
            'allDay' => (bool) $soloFecha,
            'extendedProps' => [
                'cliente' => trim(($citaCalendario->cliente->nombre ?? '') . ' ' . ($citaCalendario->cliente->apellido_paterno ?? '')) ?: 'Sin cliente',
                'estado' => (string) ($citaCalendario->estadoCita->nombre ?? $citaCalendario->estado ?? 'Pendiente'),
                'observaciones' => $citaCalendario->observaciones ?? 'Sin observaciones',
                'fechaTexto' => $inicio->format($soloFecha ? 'd/m/Y' : 'd/m/Y H:i') . ((!$soloFecha && $fin) ? ' — ' . $fin->format('d/m/Y H:i') : ''),
                'sinHora' => (bool) $soloFecha,
                'editUrl' => Route::has('calendario.edit') ? route('calendario.edit', $idCalendario) : null,
                'deleteUrl' => Route::has('calendario.destroy') ? route('calendario.destroy', $idCalendario) : null,
            ],
        ];
    }
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Estilos limitados a esta vista basados en la estructura de comi-clientes */
    .comi-calendario {
        --cc-bg: #0f172a;
        --cc-panel: #1e293b;
        --cc-border: #334155;
        --cc-text: #f1f5f9;
        --cc-muted: #a5b4c8;
        --cc-accent: #38bdf8;
        box-sizing: border-box;
        min-width: 0;
        padding: clamp(16px, 3vw, 36px);
        background: var(--cc-bg);
        color: var(--cc-text);
        border-radius: 16px;
        font-family: inherit;
        line-height: 1.5;
        color-scheme: dark;
    }
    .comi-calendario *, .comi-calendario *::before, .comi-calendario *::after { box-sizing: border-box; }
    .comi-calendario a { text-decoration: none; }
    .comi-calendario button, .comi-calendario input { font: inherit; }
    .comi-calendario .cc-eyebrow { margin: 0 0 10px; color: var(--cc-accent); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-calendario .cc-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 26px; flex-wrap: wrap; }
    .comi-calendario .cc-title { color: var(--cc-text); margin: 0; font-size: clamp(26px, 3vw, 34px); font-weight: 700; letter-spacing: -.035em; line-height: 1.2; }
    .comi-calendario .cc-subtitle { margin: 10px 0 0; color: var(--cc-muted); font-size: 14px; }
    .comi-calendario .cc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background-color .15s, border-color .15s; }
    .comi-calendario .cc-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-calendario .cc-btn-primary:hover { background: #0369a1; border-color: #38bdf8; }
    .comi-calendario .cc-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; }
    .comi-calendario .cc-btn-secondary:hover { background: #26364d; border-color: #38bdf8; }
    .comi-calendario .cc-card { min-width: 0; background: var(--cc-panel); border: 1px solid var(--cc-border); border-radius: 13px; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 12%); }
    .comi-calendario .cc-card-heading { display: flex; align-items: center; gap: 12px; padding: 22px 24px 0; }
    .comi-calendario .cc-heading-icon { width: 40px; height: 40px; display: grid; place-items: center; border: 1px solid #31516a; border-radius: 10px; background: #17364a; color: var(--cc-accent); font-size: 19px; flex-shrink: 0; }
    .comi-calendario .cc-card-title { color: var(--cc-text); font-size: 16px; font-weight: 600; margin: 0; }
    .comi-calendario .cc-card-note { color: var(--cc-muted); font-size: 12px; margin: 3px 0 0; }
    .comi-calendario .cc-toolbar { padding: 22px 24px; }
    .comi-calendario .cc-search-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin: 0; }
    .comi-calendario .cc-search { position: relative; flex: 1 1 260px; max-width: 470px; }
    .comi-calendario .cc-search > i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--cc-muted); pointer-events: none; }
    .comi-calendario .cc-input { width: 100%; min-height: 44px; padding: 10px 14px 10px 42px; border: 1px solid #475569; border-radius: 9px; background: var(--cc-bg); color: var(--cc-text); font-size: 13px; }
    .comi-calendario .cc-input::placeholder { color: #94a3b8; opacity: 1; }
    .comi-calendario :is(a, button, input, [tabindex]):focus-visible { outline: 2px solid var(--cc-accent); outline-offset: 3px; }
    .comi-calendario .cc-clear { color: #cbd5e1; font-size: 13px; padding: 10px; text-decoration: underline; text-underline-offset: 4px; }
    .comi-calendario .cc-filter-note { margin: 13px 0 0; color: var(--cc-muted); font-size: 13px; overflow-wrap: anywhere; }
    .comi-calendario .cc-filter-note strong { color: var(--cc-accent); }
    .comi-calendario .cc-alert { padding: 12px 16px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; margin: 0 0 18px; font-size: 13px; }
    .comi-calendario .cc-error { margin: 10px 0 0; color: #fda4af; font-size: 13px; }
    .comi-calendario .cc-table-scroll { overflow-x: auto; scrollbar-width: thin; scrollbar-color: #52647f #0f172a; }
    .comi-calendario .cc-table { width: 100%; min-width: 1000px; border-collapse: collapse; text-align: left; }
    .comi-calendario .cc-table th { background: #172235; padding: 14px 18px; border-top: 1px solid var(--cc-border); border-bottom: 1px solid var(--cc-border); color: #aebed2; font-size: 10px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; white-space: nowrap; }
    .comi-calendario .cc-table td { padding: 19px 18px; border-bottom: 1px solid #2d3b50; color: #d6e0ed; font-size: 13px; vertical-align: middle; }
    .comi-calendario .cc-table tbody tr:hover { background: #243248; }
    .comi-calendario .cc-table tbody tr:last-child td { border-bottom: 0; }
    .comi-calendario .cc-wrap { max-width: 240px; overflow-wrap: anywhere; }
    .comi-calendario .cc-nowrap { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .comi-calendario .cc-badge { display: inline-flex; align-items: center; gap: 7px; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; white-space: nowrap; background: #123e59; color: #7dd3fc; }
    .comi-calendario .cc-badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
    .comi-calendario .cc-actions { display: flex; align-items: center; justify-content: center; gap: 7px; }
    .comi-calendario .cc-actions form { margin: 0; }
    .comi-calendario .cc-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #475569; border-radius: 8px; background: #172235; color: #cbd5e1; cursor: pointer; }
    .comi-calendario .cc-edit:hover { background: #153f54; color: #7dd3fc; border-color: #38bdf8; }
    .comi-calendario .cc-delete:hover { background: #482635; color: #fda4af; border-color: #fb7185; }
    .comi-calendario .cc-actions-title { text-align: center; }
    .comi-calendario .cc-footer { border-top: 1px solid var(--cc-border); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
    .comi-calendario .cc-footer p { margin: 0; color: var(--cc-muted); font-size: 12px; }
    .comi-calendario .cc-pagination { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .comi-calendario .cc-pagination .cc-btn { min-height: 38px; padding: 7px 12px; font-size: 12px; }
    .comi-calendario .cc-disabled { opacity: .45; cursor: default; }
    .comi-calendario .cc-page-current { padding: 7px 10px; color: #7dd3fc; font-size: 12px; }
    .comi-calendario .cc-empty { padding: 55px 24px; text-align: center; border-top: 1px solid var(--cc-border); }
    .comi-calendario .cc-empty > i { font-size: 32px; color: #38bdf8; }
    .comi-calendario .cc-empty h3 { margin: 14px 0 7px; color: var(--cc-text); font-size: 18px; }
    .comi-calendario .cc-empty p { margin: 0 0 20px; color: var(--cc-muted); font-size: 13px; overflow-wrap: anywhere; }
    @media (max-width: 640px) {
        .comi-calendario { padding: 18px 12px; border-radius: 10px; }
        .comi-calendario .cc-header { align-items: stretch; gap: 18px; }
        .comi-calendario .cc-header > .cc-btn { width: 100%; }
        .comi-calendario .cc-toolbar, .comi-calendario .cc-footer { padding: 16px; }
        .comi-calendario .cc-card-heading { padding: 18px 16px 0; }
        .comi-calendario .cc-search { flex-basis: 100%; max-width: none; }
        .comi-calendario .cc-footer { align-items: flex-start; flex-direction: column; }
    }

    .comi-calendario .cal-shell { padding: 20px 24px 24px; border-top: 1px solid #334155; }
    .comi-calendario .cal-controls { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin-bottom: 18px; }
    .comi-calendario .cal-controls label { color: #a5b4c8; font-size: 12px; }
    .comi-calendario .cal-date { padding: 8px 10px; border: 1px solid #475569; border-radius: 8px; background: #0f172a; color: #f1f5f9; font: inherit; font-size: 12px; }
    .comi-calendario .cal-zone { color: #a5b4c8; font-size: 12px; margin-left: auto; }
    .comi-calendario .cal-note { margin: 12px 0; color: #a5b4c8; font-size: 12px; }
    .comi-calendario .cal-notice { padding: 12px 15px; border: 1px solid #756339; border-radius: 8px; color: #fde68a; background: #3d3423; margin: 0 0 16px; font-size: 13px; }
    .comi-calendario .cal-list-summary { padding: 18px 24px; border-top: 1px solid #334155; cursor: pointer; color: #7dd3fc; font-size: 13px; font-weight: 600; }
    .comi-calendario .fc { --fc-border-color: #334155; --fc-page-bg-color: #1e293b; --fc-neutral-bg-color: #172235; --fc-neutral-text-color: #cbd5e1; --fc-today-bg-color: rgba(56,189,248,.06); --fc-list-event-hover-bg-color: #26364d; --fc-event-bg-color: #075985; --fc-event-border-color: #38bdf8; --fc-event-text-color: #f0f9ff; --fc-now-indicator-color: #fb7185; --fc-button-bg-color: #172337; --fc-button-border-color: #475569; --fc-button-text-color: #e2e8f0; --fc-button-hover-bg-color: #26364d; --fc-button-hover-border-color: #38bdf8; --fc-button-active-bg-color: #075985; --fc-button-active-border-color: #38bdf8; font-size: 12px; }
    .comi-calendario .fc .fc-toolbar-title { font-size: 20px; font-weight: 650; color: #f1f5f9; }
    .comi-calendario .fc .fc-button { padding: 9px 12px; font-size: 12px; box-shadow: none; }
    .comi-calendario .fc .fc-col-header-cell { background: #172235; }
    .comi-calendario .fc .fc-col-header-cell-cushion { padding: 14px 5px; color: #cbd5e1; font-weight: 500; }
    .comi-calendario .fc .fc-day-today .fc-col-header-cell-cushion { color: #7dd3fc; font-weight: 700; }
    .comi-calendario .fc .fc-timegrid-slot { height: 28px; }
    .comi-calendario .fc .fc-timegrid-slot-label { color: #a5b4c8; font-size: 11px; }
    .comi-calendario .fc .fc-event { cursor: pointer; border-radius: 5px; }
    .comi-calendario .fc .fc-timegrid-event .fc-event-main { padding: 3px 5px; }
    .comi-calendario .fc .fc-event-title { font-weight: 600; }
    .comi-calendario .fc .fc-daygrid-day-number { color: #cbd5e1; padding: 8px; }
    .comi-calendario .fc .fc-day-today .fc-daygrid-day-number { background: #0284c7; color: #fff; border-radius: 50%; margin: 4px; min-width: 30px; text-align: center; }
    .comi-calendario .fc .fc-popover { background: #1e293b; color: #f1f5f9; }
    .comi-calendario .cal-dialog { width: min(520px, calc(100vw - 32px)); max-height: 85vh; padding: 24px; background: #1e293b; color: #f1f5f9; border: 1px solid #475569; border-radius: 14px; box-shadow: 0 25px 80px #0008; }
    .comi-calendario .cal-dialog::backdrop { background: #020617b3; }
    .comi-calendario .cal-dialog-top { display: flex; justify-content: space-between; align-items: start; gap: 16px; }
    .comi-calendario .cal-dialog h2 { font-size: 21px; margin: 0; overflow-wrap: anywhere; }
    .comi-calendario .cal-dialog dl { margin: 24px 0; }
    .comi-calendario .cal-dialog dt { font-size: 11px; color: #94a3b8; margin-top: 16px; text-transform: uppercase; letter-spacing: .08em; }
    .comi-calendario .cal-dialog dd { margin: 5px 0 0; font-size: 14px; white-space: pre-wrap; overflow-wrap: anywhere; }
    .comi-calendario .cal-dialog-actions { display: flex; flex-wrap: wrap; gap: 10px; padding-top: 18px; border-top: 1px solid #334155; }
    .comi-calendario [hidden] { display: none !important; }
    @media(max-width: 800px) { .comi-calendario .fc .fc-toolbar { flex-wrap: wrap; gap: 12px; } .comi-calendario .cal-shell { padding: 16px 10px; } .comi-calendario .cal-zone { margin-left: 0; } }
    .comi-calendario .cal-search-feedback { margin-top: 16px; padding: 14px 16px; border: 1px solid #31516a; border-radius: 9px; background: #17364a; color: #bae6fd; font-size: 13px; overflow-wrap: anywhere; }
    .comi-calendario .cal-search-feedback p { margin: 5px 0 0; color: #b6c8df; font-size: 12px; }
</style>

<section class="comi-calendario" aria-labelledby="calendario-title">
    <header class="cc-header">
        <div>
            <p class="cc-eyebrow">COMICenter / Panel de gestión</p>
            <h1 class="cc-title" id="calendario-title">Calendario</h1>
            <p class="cc-subtitle">Gestión de reuniones y seguimientos agendados con prospectos.</p>
        </div>
        @if (Route::has('calendario.create'))
            <a href="{{ route('calendario.create') }}" class="cc-btn cc-btn-primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Agendar cita
            </a>
        @endif
    </header>

    @if (session('success') || session('exito'))
        <div role="status" class="cc-alert" style="background: #143f39; border-color: #286556; color: #a7f3d0;">{{ session('success') ?? session('exito') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="cc-alert">{{ session('error') }}</div>
    @endif

    <div class="cc-card">
        <div class="cc-card-heading">
            <span class="cc-heading-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></span>
            <div>
                <h2 class="cc-card-title">Agenda de Citas</h2>
                <p class="cc-card-note">Organiza tu semana y consulta los detalles de cada cita.</p>
            </div>
        </div>

        <div class="cc-toolbar">
            <form action="{{ Route::has('calendario.index') ? route('calendario.index') : '#' }}" method="GET" class="cc-search-form" role="search">
                <div class="cc-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="buscar" class="cc-input" placeholder="Motivo, observaciones o cliente..."
                        aria-label="Buscar citas" value="{{ $buscar }}" maxlength="100"
                        @error('buscar') aria-invalid="true" aria-describedby="cc-search-error" @enderror>
                </div>
                <button type="submit" class="cc-btn cc-btn-secondary">Buscar</button>
                @if ($buscar !== '')
                    <a href="{{ route('calendario.index') }}" class="cc-clear">Limpiar búsqueda</a>
                @endif
            </form>
            @error('buscar')
                <p class="cc-error" id="cc-search-error" role="alert">{{ $message }}</p>
            @enderror
            @if ($buscar !== '')
                <div class="cal-search-feedback" role="status">
                    @if ($totalResultados === 0)
                        <strong>No se encontraron citas para “{{ $buscar }}”.</strong>
                        <p>Prueba con otro nombre, motivo u observación, o limpia la búsqueda.</p>
                    @else
                        <strong>{{ $totalResultados === 1 ? 'Se encontró 1 cita' : 'Se encontraron ' . $totalResultados . ' citas' }} para “{{ $buscar }}”.</strong>
                        <p>El listado inferior contiene los resultados. El calendario se abre en la primera cita con fecha válida.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="cal-shell">
            @if (isset($citas) && method_exists($citas, 'hasPages') && $citas->hasPages())
                <p class="cal-notice">Vista parcial: se muestran las citas de la página {{ $citas->currentPage() }} del listado. Hay más citas en otras páginas; utiliza la navegación inferior para consultarlas.</p>
            @endif
            @if ($sinFechaCalendario > 0)
                <p class="cal-notice">{{ $sinFechaCalendario }} cita(s) sin fecha válida no pudieron ubicarse. Consúltalas en el listado inferior.</p>
            @endif
            <div class="cal-controls">
                <label for="cal-ir-fecha">Ir a una fecha</label>
                <input type="date" id="cal-ir-fecha" class="cal-date">
                <span class="cal-zone">Horario de la aplicación · {{ $zonaCalendario }}</span>
            </div>
            <p id="cal-load-error" class="cal-notice" hidden>No se pudo cargar el calendario. Revisa tu conexión o consulta el listado inferior.</p>
            <noscript><p class="cal-notice">Activa JavaScript para ver el calendario. Tus citas también están en el listado inferior.</p></noscript>
            <div id="comi-agenda" aria-label="Calendario de citas"></div>
            <p class="cal-note" id="cal-range-status" role="status"></p>
            <p class="cal-note">Pulsa una cita para ver sus detalles. Las citas sin hora de fin ocupan 30 minutos únicamente como referencia visual.</p>
        </div>
        <details id="cal-listado" @if ($buscar !== '') open @endif>
            <summary class="cal-list-summary">Ver listado y acciones de las citas cargadas</summary>
        @if (isset($citas) && $citas->count() > 0)
            <div class="cc-table-scroll" tabindex="0" role="region" aria-label="Tabla de citas; desplaza horizontalmente para ver todas las columnas">
                <table class="cc-table" aria-label="Agenda de Citas">
                    <thead>
                        <tr>
                            <th scope="col">Fecha &amp; Hora</th>
                            <th scope="col">Motivo / Asunto</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Observaciones</th>
                            <th scope="col" class="cc-actions-title">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($citas as $cita)
                            @php
                                $nombreCliente = trim(($cita->cliente->nombre ?? '') . ' ' . ($cita->cliente->apellido_paterno ?? ''));
                            @endphp
                            <tr>
                                <td class="cc-nowrap">
                                    {{ $cita->fecha_hora_inicio ?? $cita->fecha ?? 'Sin fecha' }}
                                </td>
                                <td class="cc-wrap font-medium" style="color: #f1f5f9; font-weight: 600;">
                                    {{ $cita->motivo ?? $cita->asunto ?? 'Sin asunto' }}
                                </td>
                                <td class="cc-wrap">
                                    {{ $nombreCliente ?: ($cita->cliente->nombre ?? 'N/A') }}
                                </td>
                                <td>
                                    <span class="cc-badge">
                                        {{ $cita->estadoCita->nombre ?? $cita->estado ?? 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="cc-wrap">
                                    {{ $cita->observaciones ?? '-' }}
                                </td>
                                <td>
                                    <div class="cc-actions">
                                        @if (Route::has('calendario.edit'))
                                            <a href="{{ route('calendario.edit', $cita->id_cita ?? $cita->id) }}" class="cc-icon-btn cc-edit"
                                                title="Editar cita" aria-label="Editar cita">
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if (Route::has('calendario.destroy'))
                                            <form action="{{ route('calendario.destroy', $cita->id_cita ?? $cita->id) }}" method="POST"
                                                onsubmit="return confirm('¿Estás seguro de eliminar esta cita?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="cc-icon-btn cc-delete"
                                                    title="Eliminar cita" aria-label="Eliminar cita">
                                                    <i class="bi bi-trash" aria-hidden="true"></i>
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
        @else
            <div class="cc-empty">
                <i class="bi bi-{{ $buscar !== '' ? 'search' : 'calendar-event' }}" aria-hidden="true"></i>
                <h3>{{ $buscar !== '' ? 'No encontramos coincidencias' : 'No tienes citas agendadas registradas' }}</h3>
                <p>{{ $buscar !== '' ? 'No se encontraron citas para “' . $buscar . '”. Prueba con otro término.' : 'Agrega una cita para comenzar a gestionar tus reuniones con prospectos.' }}</p>
                @if ($buscar !== '')
                    <a href="{{ route('calendario.index') }}" class="cc-btn cc-btn-secondary">Ver todas las citas</a>
                @elseif (Route::has('calendario.create'))
                    <a href="{{ route('calendario.create') }}" class="cc-btn cc-btn-primary">Agendar cita</a>
                @endif
            </div>
        @endif

        </details>
        <footer class="cc-footer">
            <p>{{ isset($citas) ? $citas->count() : 0 }} {{ (isset($citas) && $citas->count() === 1) ? 'cita cargada' : 'citas cargadas' }} · El calendario muestra las citas cargadas con fecha válida.</p>
            @if (isset($citas) && method_exists($citas, 'hasPages') && $citas->hasPages())
                <nav class="cc-pagination" aria-label="Paginación de citas">
                    @if ($citas->onFirstPage())
                        <span class="cc-btn cc-btn-secondary cc-disabled" aria-disabled="true">Anterior</span>
                    @else
                        <a class="cc-btn cc-btn-secondary" href="{{ $citas->previousPageUrl() }}" rel="prev">Anterior</a>
                    @endif
                    <span class="cc-page-current" aria-current="page">Página {{ $citas->currentPage() }}</span>
                    @if ($citas->hasMorePages())
                        <a class="cc-btn cc-btn-secondary" href="{{ $citas->nextPageUrl() }}" rel="next">Siguiente</a>
                    @else
                        <span class="cc-btn cc-btn-secondary cc-disabled" aria-disabled="true">Siguiente</span>
                    @endif
                </nav>
            @endif
        </footer>
    </div>
    <dialog class="cal-dialog" id="cal-detalle" aria-labelledby="cal-detalle-title">
        <div class="cal-dialog-top">
            <div><p class="cc-eyebrow">Detalle de la cita</p><h2 id="cal-detalle-title"></h2></div>
            <button type="button" class="cc-icon-btn" id="cal-cerrar" aria-label="Cerrar detalles">✕</button>
        </div>
        <dl>
            <dt>Fecha y hora</dt><dd id="cal-detalle-fecha"></dd>
            <dt>Cliente</dt><dd id="cal-detalle-cliente"></dd>
            <dt>Estado</dt><dd id="cal-detalle-estado"></dd>
            <dt>Observaciones</dt><dd id="cal-detalle-notas"></dd>
        </dl>
        <div class="cal-dialog-actions">
            <a id="cal-editar" class="cc-btn cc-btn-primary" hidden>Editar cita</a>
            <form id="cal-eliminar" method="POST" hidden onsubmit="return confirm('¿Estás seguro de eliminar esta cita?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="cc-btn cc-btn-secondary">Eliminar cita</button>
            </form>
        </div>
    </dialog>
</section>
{{-- FullCalendar 6 fijado a una versión concreta; incluye las vistas semana/mes/día/lista. --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.21/index.global.min.js"></script>
<script>
(() => {
    const events = {{ \Illuminate\Support\Js::from($eventosCalendario) }};
    const isSearching = {{ \Illuminate\Support\Js::from($buscar !== '') }};
    const firstResult = [...events].sort((a, b) => a.start.localeCompare(b.start))[0];
    const serverToday = {{ \Illuminate\Support\Js::from(now($zonaCalendario)->format('Y-m-d')) }};
    const serverNow = {{ \Illuminate\Support\Js::from(now($zonaCalendario)->format('Y-m-d\TH:i:s')) }};
    const pageLoadedAt = Date.now();
    const dateInput = document.getElementById('cal-ir-fecha');
    const status = document.getElementById('cal-range-status');
    const dialog = document.getElementById('cal-detalle');
    if (!window.FullCalendar) {
        document.getElementById('cal-load-error').hidden = false;
        document.getElementById('cal-listado').open = true;
        return;
    }
    let lastTrigger = null;
    function showDetails(event, trigger) {
        lastTrigger = trigger;
        const p = event.extendedProps;
        document.getElementById('cal-detalle-title').textContent = event.title;
        document.getElementById('cal-detalle-fecha').textContent = p.fechaTexto + (p.sinHora ? ' · Sin hora especificada' : '');
        document.getElementById('cal-detalle-cliente').textContent = p.cliente;
        document.getElementById('cal-detalle-estado').textContent = p.estado;
        document.getElementById('cal-detalle-notas').textContent = p.observaciones;
        const edit = document.getElementById('cal-editar');
        edit.hidden = !p.editUrl;
        if (p.editUrl) edit.href = p.editUrl; else edit.removeAttribute('href');
        const remove = document.getElementById('cal-eliminar');
        remove.hidden = !p.deleteUrl;
        if (p.deleteUrl) remove.action = p.deleteUrl; else remove.removeAttribute('action');
        dialog.showModal();
    }
    document.getElementById('cal-cerrar').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { if (lastTrigger?.isConnected) lastTrigger.focus(); });
    const calendar = new FullCalendar.Calendar(document.getElementById('comi-agenda'), {
        locale: 'es', firstDay: 1,
        initialView: window.matchMedia('(max-width: 640px)').matches ? 'timeGridDay' : 'timeGridWeek',
        initialDate: isSearching && firstResult ? firstResult.start.slice(0, 10) : serverToday,
        timeZone: 'local',
        now: () => new Date(new Date(serverNow).getTime() + Date.now() - pageLoadedAt),
        nowIndicator: true,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Agenda' },
        allDayText: 'Sin hora', noEventsText: 'No hay citas cargadas en este período.',
        moreLinkText: n => '+' + n + ' más',
        height: 700, scrollTime: isSearching && firstResult && !firstResult.allDay ? firstResult.start.slice(11, 19) : '07:00:00', slotMinTime: '00:00:00', slotMaxTime: '24:00:00',
        slotDuration: '00:30:00', defaultTimedEventDuration: '00:30:00',
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        dayHeaderFormat: { weekday: 'short', day: 'numeric', month: 'short' },
        editable: false, selectable: false, dayMaxEvents: 3, navLinks: true,
        events,
        eventClick: info => showDetails(info.event, info.el),
        eventDidMount: info => {
            info.el.tabIndex = 0;
            info.el.setAttribute('role', 'button');
            info.el.setAttribute('aria-label', info.event.title + ' · ' + info.event.extendedProps.fechaTexto);
            info.el.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); showDetails(info.event, info.el); }
            });
        },
        datesSet: info => {
            const count = events.filter(e => {
                const start = new Date(e.start.length === 10 ? e.start + 'T00:00:00' : e.start);
                const end = e.end ? new Date(e.end) : new Date(start.getTime() + (e.allDay ? 86400000 : 1800000));
                return start < info.end && end > info.start;
            }).length;
            status.textContent = count + ' cita(s) cargada(s) en este período. ' + events.length + ' en el conjunto actual.';
            dateInput.value = info.view.calendar.getDate().getFullYear() + '-' + String(info.view.calendar.getDate().getMonth()+1).padStart(2,'0') + '-' + String(info.view.calendar.getDate().getDate()).padStart(2,'0');
        }
    });
    calendar.render();
    dateInput.addEventListener('change', () => { if (dateInput.value) calendar.gotoDate(dateInput.value); });
})();
</script>
@endsection