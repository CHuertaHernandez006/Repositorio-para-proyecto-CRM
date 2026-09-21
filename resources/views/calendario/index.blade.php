{{-- resources/views/calendario/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Calendario - CRM')

@section('content')
@php
    $buscar = $buscar ?? '';
    if (method_exists($citas, 'appends')) {
        $citas->appends(['buscar' => $buscar]);
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

    @if (session('success'))
        <div role="status" class="cc-alert" style="background: #143f39; border-color: #286556; color: #a7f3d0;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="cc-alert">{{ session('error') }}</div>
    @endif

    <div class="cc-card">
        <div class="cc-card-heading">
            <span class="cc-heading-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></span>
            <div>
                <h2 class="cc-card-title">Agenda de Citas</h2>
                <p class="cc-card-note">Listado de compromisos y reuniones programadas.</p>
            </div>
        </div>

        <div class="cc-toolbar">
            <form action="{{ Route::has('calendario.index') ? route('calendario.index') : '#' }}" method="GET" class="cc-search-form" role="search">
                <div class="cc-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="buscar" class="cc-input" placeholder="Motivo, asunto, cliente..."
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
                <p class="cc-filter-note">Resultados para <strong>“{{ $buscar }}”</strong></p>
            @endif
        </div>

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
                                    {{ isset($cita->fecha_hora_inicio) ? \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('d/m/Y H:i') : ($cita->fecha ?? 'N/A') }}
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

        <footer class="cc-footer">
            <p>{{ isset($citas) ? $citas->count() : 0 }} {{ (isset($citas) && $citas->count() === 1) ? 'cita en esta página' : 'citas en esta página' }} · Desliza la tabla para ver todos los datos.</p>
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
</section>
@endsection