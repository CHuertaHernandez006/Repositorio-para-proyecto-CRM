@extends('layouts.app')

@section('title', 'Llamadas - CRM')
@section('header-title', 'Llamadas')

@section('content')

@php
    $totalLlamadasVista = $totalLlamadas ?? 0;
    $entrantesVista = $llamadasEntrantes ?? 0;
    $salientesVista = $llamadasSalientes ?? 0;
    $enCursoVista = $llamadasEnCurso ?? 0;
    $finalizadasVista = $llamadasFinalizadas ?? 0;
    $duracionPromedioVista = $duracionPromedio ?? 0;

    $formatearDuracion = function ($segundos) {
        if ($segundos === null) {
            return '—';
        }

        $segundos = (int) $segundos;
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        $restantes = $segundos % 60;

        return $horas > 0
            ? sprintf('%02d:%02d:%02d', $horas, $minutos, $restantes)
            : sprintf('%02d:%02d', $minutos, $restantes);
    };

    $nombreCliente = function ($cliente) {
        if (!$cliente) {
            return 'Cliente no disponible';
        }

        if (!empty($cliente->nombre_completo)) {
            return $cliente->nombre_completo;
        }

        return trim(
            ($cliente->nombre ?? '') . ' ' .
            ($cliente->apellido_paterno ?? '') . ' ' .
            ($cliente->apellido_materno ?? '')
        );
    };

    $nombreUsuario = function ($usuario) {
        if (!$usuario) {
            return 'COMI';
        }

        if (!empty($usuario->name)) {
            return $usuario->name;
        }

        return trim(
            ($usuario->nombre ?? '') . ' ' .
            ($usuario->apellido_paterno ?? '') . ' ' .
            ($usuario->apellido_materno ?? '')
        );
    };
@endphp

<style>
    .llamadas-page {
        max-width: 1450px;
        margin: 0 auto;
        color: #e8eef7;
    }

    .llamadas-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 26px;
    }

    .llamadas-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 9px;
        color: #35c6ff;
        font-size: 11px;
        font-weight: 750;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .llamadas-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 10px rgba(53, 198, 255, .65);
    }

    .llamadas-title {
        margin: 0;
        color: #fff;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .llamadas-description {
        max-width: 760px;
        margin: 9px 0 0;
        color: #8190a7;
        font-size: 14px;
        line-height: 1.6;
    }

    .live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid rgba(52, 211, 153, .20);
        border-radius: 11px;
        background: rgba(52, 211, 153, .07);
        color: #34d399;
        font-size: 12px;
        font-weight: 700;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #34d399;
        box-shadow: 0 0 12px rgba(52, 211, 153, .65);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .stat-card {
        position: relative;
        overflow: hidden;
        min-height: 118px;
        padding: 18px;
        border: 1px solid rgba(148, 163, 184, .12);
        border-radius: 16px;
        background:
            linear-gradient(145deg, rgba(21, 34, 55, .96), rgba(13, 24, 42, .96));
        box-shadow: 0 12px 28px rgba(0, 0, 0, .16);
    }

    .stat-card::after {
        content: "";
        position: absolute;
        top: -38px;
        right: -38px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(53, 198, 255, .06);
        filter: blur(2px);
    }

    .stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 15px;
    }

    .stat-label {
        color: #8d9bb0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .stat-icon {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: rgba(53, 198, 255, .08);
        color: #35c6ff;
    }

    .stat-value {
        color: #fff;
        font-size: 26px;
        line-height: 1;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .stat-helper {
        margin-top: 8px;
        color: #68788f;
        font-size: 11px;
    }

    .filters-card,
    .table-card {
        border: 1px solid rgba(148, 163, 184, .12);
        border-radius: 16px;
        background: rgba(18, 30, 49, .94);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .15);
    }

    .filters-card {
        padding: 18px;
        margin-bottom: 22px;
    }

    .filters-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 15px;
        color: #f8fafc;
        font-size: 13px;
        font-weight: 700;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: 2fr repeat(5, minmax(145px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .filter-group label {
        display: block;
        margin-bottom: 7px;
        color: #8391a6;
        font-size: 11px;
        font-weight: 650;
    }

    .llamadas-input,
    .llamadas-select {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #2c3d55;
        border-radius: 10px;
        outline: none;
        background: #0e1b2d;
        color: #e7edf6;
        font-size: 13px;
        transition: .2s ease;
    }

    .llamadas-input::placeholder {
        color: #54657b;
    }

    .llamadas-input:focus,
    .llamadas-select:focus {
        border-color: rgba(53, 198, 255, .70);
        box-shadow: 0 0 0 3px rgba(53, 198, 255, .08);
    }

    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 14px;
    }

    .btn-filter,
    .btn-clear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 15px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-filter {
        border: 1px solid rgba(53, 198, 255, .36);
        background: rgba(53, 198, 255, .12);
        color: #5dd3ff;
    }

    .btn-filter:hover {
        background: rgba(53, 198, 255, .20);
        color: #fff;
    }

    .btn-clear {
        border: 1px solid rgba(148, 163, 184, .16);
        background: rgba(148, 163, 184, .06);
        color: #9aa8ba;
    }

    .btn-clear:hover {
        background: rgba(148, 163, 184, .12);
        color: #fff;
    }

    .table-card {
        overflow: hidden;
    }

    .table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid rgba(148, 163, 184, .10);
    }

    .table-card-title {
        margin: 0;
        color: #fff;
        font-size: 15px;
        font-weight: 750;
    }

    .table-card-count {
        color: #68788f;
        font-size: 12px;
    }

    .calls-table-wrap {
        overflow-x: auto;
    }

    .calls-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: collapse;
    }

    .calls-table thead th {
        padding: 12px 16px;
        border-bottom: 1px solid rgba(148, 163, 184, .10);
        background: rgba(10, 19, 33, .52);
        color: #718198;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .09em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .calls-table tbody td {
        padding: 15px 16px;
        border-bottom: 1px solid rgba(148, 163, 184, .08);
        color: #cbd5e1;
        font-size: 12px;
        vertical-align: middle;
    }

    .calls-table tbody tr {
        transition: .18s ease;
    }

    .calls-table tbody tr:hover {
        background: rgba(53, 198, 255, .025);
    }

    .calls-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .client-cell {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 200px;
    }

    .client-avatar {
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 11px;
        background: linear-gradient(145deg, rgba(53,198,255,.16), rgba(79,70,229,.10));
        color: #4dd0ff;
        font-size: 15px;
    }

    .client-name {
        color: #eef4fb;
        font-size: 12px;
        font-weight: 700;
    }

    .client-phone {
        margin-top: 3px;
        color: #67778c;
        font-size: 11px;
    }

    .operator-comi {
        color: #a78bfa;
        font-weight: 700;
    }

    .badge-call {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 28px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 750;
        white-space: nowrap;
    }

    .badge-incoming {
        border: 1px solid rgba(52,211,153,.18);
        background: rgba(52,211,153,.08);
        color: #34d399;
    }

    .badge-outgoing {
        border: 1px solid rgba(96,165,250,.18);
        background: rgba(96,165,250,.08);
        color: #60a5fa;
    }

    .badge-status {
        border: 1px solid rgba(251,191,36,.18);
        background: rgba(251,191,36,.07);
        color: #fbbf24;
    }

    .badge-result {
        border: 1px solid rgba(167,139,250,.18);
        background: rgba(167,139,250,.07);
        color: #c4b5fd;
    }

    .date-main {
        color: #d8e1ec;
        white-space: nowrap;
    }

    .date-sub {
        margin-top: 3px;
        color: #66768b;
        font-size: 10px;
    }

    .duration {
        color: #e7edf6;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .btn-detail {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 34px;
        padding: 0 11px;
        border: 1px solid rgba(53,198,255,.20);
        border-radius: 9px;
        background: rgba(53,198,255,.07);
        color: #55d0ff;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: .18s ease;
        white-space: nowrap;
    }

    .btn-detail:hover {
        background: rgba(53,198,255,.15);
        color: #fff;
    }

    .empty-state {
        padding: 54px 20px;
        text-align: center;
    }

    .empty-icon {
        display: grid;
        place-items: center;
        width: 58px;
        height: 58px;
        margin: 0 auto 15px;
        border-radius: 17px;
        background: rgba(53,198,255,.07);
        color: #35c6ff;
        font-size: 24px;
    }

    .empty-title {
        margin: 0 0 6px;
        color: #edf4fb;
        font-size: 15px;
        font-weight: 750;
    }

    .empty-text {
        margin: 0;
        color: #6f8096;
        font-size: 12px;
    }

    .pagination-zone {
        display: flex;
        justify-content: center;
        padding: 17px 20px;
        border-top: 1px solid rgba(148,163,184,.08);
    }

    .pagination-zone .pagination {
        margin: 0;
    }

    .pagination-zone .page-link {
        border-color: #2a3a50;
        background: #0e1b2d;
        color: #8ca0b7;
    }

    .pagination-zone .page-item.active .page-link {
        border-color: #35c6ff;
        background: #35c6ff;
        color: #06131f;
    }

    .pagination-zone .page-item.disabled .page-link {
        border-color: #26374b;
        background: #0c1727;
        color: #4e5f73;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .filters-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .filter-search {
            grid-column: span 2;
        }
    }

    @media (max-width: 768px) {
        .llamadas-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .filter-search {
            grid-column: auto;
        }

        .filters-actions {
            justify-content: stretch;
        }

        .btn-filter,
        .btn-clear {
            flex: 1;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="llamadas-page">

    <div class="llamadas-header">
        <div>
            <div class="llamadas-eyebrow">
                <span class="llamadas-eyebrow-dot"></span>
                Telefonía y seguimiento
            </div>

            <h1 class="llamadas-title">
                Historial de llamadas
            </h1>

            <p class="llamadas-description">
                Consulta llamadas registradas por Asterisk y COMI, su estado, duración,
                cliente relacionado y operario asignado.
            </p>
        </div>

        <div class="live-indicator">
            <span class="live-dot"></span>
            Módulo de llamadas
        </div>
    </div>


    {{-- KPIs --}}
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total</span>
                <span class="stat-icon">
                    <i class="bi bi-telephone"></i>
                </span>
            </div>
            <div class="stat-value">{{ number_format($totalLlamadasVista) }}</div>
            <div class="stat-helper">Llamadas registradas</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Entrantes</span>
                <span class="stat-icon">
                    <i class="bi bi-telephone-inbound"></i>
                </span>
            </div>
            <div class="stat-value">{{ number_format($entrantesVista) }}</div>
            <div class="stat-helper">Recibidas por el sistema</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Salientes</span>
                <span class="stat-icon">
                    <i class="bi bi-telephone-outbound"></i>
                </span>
            </div>
            <div class="stat-value">{{ number_format($salientesVista) }}</div>
            <div class="stat-helper">Originadas desde CRM</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">En curso</span>
                <span class="stat-icon">
                    <i class="bi bi-broadcast"></i>
                </span>
            </div>
            <div class="stat-value">{{ number_format($enCursoVista) }}</div>
            <div class="stat-helper">Sin fecha de finalización</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Finalizadas</span>
                <span class="stat-icon">
                    <i class="bi bi-check2-circle"></i>
                </span>
            </div>
            <div class="stat-value">{{ number_format($finalizadasVista) }}</div>
            <div class="stat-helper">Con cierre registrado</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Promedio</span>
                <span class="stat-icon">
                    <i class="bi bi-stopwatch"></i>
                </span>
            </div>
            <div class="stat-value">{{ $formatearDuracion($duracionPromedioVista) }}</div>
            <div class="stat-helper">Duración promedio</div>
        </div>

    </div>


    {{-- FILTROS --}}
    <div class="filters-card">

        <div class="filters-title">
            <i class="bi bi-funnel"></i>
            Buscar y filtrar
        </div>

        <form method="GET" action="{{ route('llamadas.index') }}">

            <div class="filters-grid">

                <div class="filter-group filter-search">
                    <label for="buscar">Buscar</label>
                    <input
                        id="buscar"
                        type="text"
                        name="buscar"
                        class="llamadas-input"
                        value="{{ request('buscar') }}"
                        placeholder="Cliente, teléfono, operario, ID Asterisk..."
                    >
                </div>

                <div class="filter-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="llamadas-select">
                        <option value="">Todos</option>
                        <option value="entrante" @selected(request('tipo') === 'entrante')>
                            Entrante
                        </option>
                        <option value="saliente" @selected(request('tipo') === 'saliente')>
                            Saliente
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" class="llamadas-select">
                        <option value="">Todos</option>

                        @foreach($estados as $estado)
                            <option
                                value="{{ $estado->id_estado_llamada }}"
                                @selected((string) request('estado') === (string) $estado->id_estado_llamada)
                            >
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="resultado">Resultado</label>
                    <select id="resultado" name="resultado" class="llamadas-select">
                        <option value="">Todos</option>

                        @foreach($resultados as $resultado)
                            <option
                                value="{{ $resultado->id_resultado }}"
                                @selected((string) request('resultado') === (string) $resultado->id_resultado)
                            >
                                {{ $resultado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user() && (int) auth()->user()->id_rol !== 3)
                    <div class="filter-group">
                        <label for="operario">Operario</label>
                        <select id="operario" name="operario" class="llamadas-select">
                            <option value="">Todos</option>

                            @foreach($operarios as $operario)
                                <option
                                    value="{{ $operario->id_usuario }}"
                                    @selected((string) request('operario') === (string) $operario->id_usuario)
                                >
                                    {{ $nombreUsuario($operario) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="filter-group">
                    <label for="fecha_desde">Desde</label>
                    <input
                        id="fecha_desde"
                        type="date"
                        name="fecha_desde"
                        class="llamadas-input"
                        value="{{ request('fecha_desde') }}"
                    >
                </div>

                <div class="filter-group">
                    <label for="fecha_hasta">Hasta</label>
                    <input
                        id="fecha_hasta"
                        type="date"
                        name="fecha_hasta"
                        class="llamadas-input"
                        value="{{ request('fecha_hasta') }}"
                    >
                </div>

            </div>

            <div class="filters-actions">

                <a href="{{ route('llamadas.index') }}" class="btn-clear">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Limpiar
                </a>

                <button type="submit" class="btn-filter">
                    <i class="bi bi-search"></i>
                    Aplicar filtros
                </button>

            </div>

        </form>
    </div>


    {{-- HISTORIAL --}}
    <div class="table-card">

        <div class="table-card-header">
            <h2 class="table-card-title">
                Registro de llamadas
            </h2>

            <span class="table-card-count">
                {{ $llamadas->total() }} resultado{{ $llamadas->total() === 1 ? '' : 's' }}
            </span>
        </div>

        @if($llamadas->count())

            <div class="calls-table-wrap">

                <table class="calls-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Operario / COMI</th>
                            <th>Estado</th>
                            <th>Inicio</th>
                            <th>Duración</th>
                            <th>Resultado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($llamadas as $llamada)

                            <tr>

                                <td>
                                    <div class="client-cell">
                                        <div class="client-avatar">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <div>
                                            <div class="client-name">
                                                {{ $nombreCliente($llamada->cliente) }}
                                            </div>

                                            <div class="client-phone">
                                                {{ $llamada->numero_origen ?: ($llamada->cliente->telefono_principal ?? 'Sin número') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($llamada->tipo_llamada === 'saliente')
                                        <span class="badge-call badge-outgoing">
                                            <i class="bi bi-telephone-outbound"></i>
                                            Saliente
                                        </span>
                                    @else
                                        <span class="badge-call badge-incoming">
                                            <i class="bi bi-telephone-inbound"></i>
                                            Entrante
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($llamada->usuario)
                                        {{ $nombreUsuario($llamada->usuario) }}
                                    @else
                                        <span class="operator-comi">
                                            <i class="bi bi-robot me-1"></i>
                                            COMI
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge-call badge-status">
                                        {{ $llamada->estadoLlamada->nombre ?? 'Sin estado' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="date-main">
                                        {{ $llamada->fecha_inicio?->format('d/m/Y') ?? '—' }}
                                    </div>

                                    <div class="date-sub">
                                        {{ $llamada->fecha_inicio?->format('H:i:s') ?? '—' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="duration">
                                        {{ $formatearDuracion($llamada->duracion) }}
                                    </span>
                                </td>

                                <td>
                                    @if($llamada->resultado)
                                        <span class="badge-call badge-result">
                                            {{ $llamada->resultado->nombre }}
                                        </span>
                                    @else
                                        <span style="color:#64748b;">Pendiente</span>
                                    @endif
                                </td>

                                <td>
                                    <a
                                        href="{{ route('llamadas.show', $llamada->id_llamada) }}"
                                        class="btn-detail"
                                    >
                                        <i class="bi bi-eye"></i>
                                        Ver
                                    </a>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>
                </table>

            </div>

            @if($llamadas->hasPages())
                <div class="pagination-zone">
                    {{ $llamadas->links() }}
                </div>
            @endif

        @else

            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-telephone-x"></i>
                </div>

                <h3 class="empty-title">
                    No hay llamadas para mostrar
                </h3>

                <p class="empty-text">
                    Cuando Asterisk o COMI registren llamadas, aparecerán aquí.
                </p>
            </div>

        @endif

    </div>

</div>

@endsection
