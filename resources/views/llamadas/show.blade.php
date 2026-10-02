@extends('layouts.app')

@section('title', 'Detalle de llamada - CRM')
@section('header-title', 'Detalle de llamada')

@section('content')

@php
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

    $numeroPrincipal = $llamada->tipo_llamada === 'saliente'
        ? $llamada->numero_destino
        : $llamada->numero_origen;
@endphp

<style>
    .call-detail-page {
        max-width: 1320px;
        margin: 0 auto;
        color: #e8eef7;
    }

    .detail-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 25px;
    }

    .detail-eyebrow {
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

    .detail-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 10px rgba(53,198,255,.65);
    }

    .detail-title {
        margin: 0;
        color: #fff;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .detail-description {
        margin: 9px 0 0;
        color: #8190a7;
        font-size: 14px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid rgba(148,163,184,.16);
        border-radius: 10px;
        background: rgba(148,163,184,.06);
        color: #a4b2c3;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-back:hover {
        color: #fff;
        background: rgba(148,163,184,.12);
    }

    .hero-call {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 22px;
        align-items: center;
        padding: 24px;
        margin-bottom: 20px;
        border: 1px solid rgba(53,198,255,.14);
        border-radius: 18px;
        background:
            radial-gradient(circle at top right, rgba(53,198,255,.08), transparent 35%),
            linear-gradient(145deg, rgba(21,34,55,.98), rgba(12,23,40,.98));
        box-shadow: 0 16px 36px rgba(0,0,0,.18);
    }

    .hero-left {
        display: flex;
        align-items: center;
        gap: 17px;
    }

    .hero-icon {
        display: grid;
        place-items: center;
        width: 62px;
        height: 62px;
        flex-shrink: 0;
        border-radius: 18px;
        background: rgba(53,198,255,.10);
        color: #41ccff;
        font-size: 25px;
        box-shadow: inset 0 0 0 1px rgba(53,198,255,.10);
    }

    .hero-number {
        margin: 0;
        color: #fff;
        font-size: 24px;
        font-weight: 750;
        letter-spacing: -.02em;
    }

    .hero-client {
        margin-top: 6px;
        color: #91a1b5;
        font-size: 13px;
    }

    .hero-badges {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .detail-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 31px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 750;
    }

    .badge-in {
        border: 1px solid rgba(52,211,153,.18);
        background: rgba(52,211,153,.08);
        color: #34d399;
    }

    .badge-out {
        border: 1px solid rgba(96,165,250,.18);
        background: rgba(96,165,250,.08);
        color: #60a5fa;
    }

    .badge-state {
        border: 1px solid rgba(251,191,36,.18);
        background: rgba(251,191,36,.07);
        color: #fbbf24;
    }

    .badge-result {
        border: 1px solid rgba(167,139,250,.18);
        background: rgba(167,139,250,.07);
        color: #c4b5fd;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        gap: 20px;
    }

    .detail-card {
        border: 1px solid rgba(148,163,184,.12);
        border-radius: 16px;
        background: rgba(18,30,49,.94);
        box-shadow: 0 12px 28px rgba(0,0,0,.14);
        overflow: hidden;
    }

    .detail-card + .detail-card {
        margin-top: 20px;
    }

    .detail-card-header {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 16px 18px;
        border-bottom: 1px solid rgba(148,163,184,.09);
        color: #f4f8fc;
        font-size: 13px;
        font-weight: 750;
    }

    .detail-card-header i {
        color: #35c6ff;
    }

    .detail-card-body {
        padding: 18px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .info-item {
        min-width: 0;
        padding: 13px 14px;
        border: 1px solid rgba(148,163,184,.08);
        border-radius: 12px;
        background: rgba(8,17,30,.30);
    }

    .info-label {
        margin-bottom: 7px;
        color: #718198;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .info-value {
        color: #e8eef7;
        font-size: 12px;
        font-weight: 650;
        overflow-wrap: anywhere;
    }

    .info-sub {
        margin-top: 4px;
        color: #607087;
        font-size: 10px;
    }

    .timeline {
        position: relative;
        padding-left: 24px;
    }

    .timeline::before {
        content: "";
        position: absolute;
        top: 7px;
        bottom: 7px;
        left: 7px;
        width: 1px;
        background: #2d4058;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        top: 4px;
        left: -21px;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 10px rgba(53,198,255,.45);
    }

    .timeline-title {
        color: #e8eef7;
        font-size: 12px;
        font-weight: 700;
    }

    .timeline-value {
        margin-top: 4px;
        color: #718198;
        font-size: 11px;
    }

    .notes-box {
        min-height: 100px;
        padding: 15px;
        border: 1px solid rgba(148,163,184,.08);
        border-radius: 12px;
        background: rgba(8,17,30,.30);
        color: #b8c5d4;
        font-size: 12px;
        line-height: 1.65;
        white-space: pre-wrap;
    }

    .recording-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 15px;
        border: 1px solid rgba(52,211,153,.12);
        border-radius: 12px;
        background: rgba(52,211,153,.04);
    }

    .recording-title {
        color: #dce7f2;
        font-size: 12px;
        font-weight: 700;
    }

    .recording-sub {
        margin-top: 4px;
        color: #67778c;
        font-size: 10px;
        overflow-wrap: anywhere;
    }

    .btn-recording {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 34px;
        padding: 0 11px;
        border: 1px solid rgba(52,211,153,.18);
        border-radius: 9px;
        background: rgba(52,211,153,.08);
        color: #34d399;
        font-size: 11px;
        font-weight: 750;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-recording:hover {
        background: rgba(52,211,153,.15);
        color: #fff;
    }

    .citas-list {
        display: grid;
        gap: 10px;
    }

    .cita-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 13px 14px;
        border: 1px solid rgba(148,163,184,.08);
        border-radius: 12px;
        background: rgba(8,17,30,.30);
    }

    .cita-date {
        color: #e5edf6;
        font-size: 12px;
        font-weight: 700;
    }

    .cita-motive {
        margin-top: 4px;
        color: #718198;
        font-size: 10px;
    }

    .cita-status {
        color: #35c6ff;
        font-size: 10px;
        font-weight: 750;
        text-align: right;
    }

    .empty-small {
        padding: 20px 5px;
        color: #66768b;
        font-size: 11px;
        text-align: center;
    }

    @media (max-width: 950px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .detail-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .hero-call {
            grid-template-columns: 1fr;
        }

        .hero-badges {
            justify-content: flex-start;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .recording-box,
        .cita-item {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="call-detail-page">

    <div class="detail-header">

        <div>
            <div class="detail-eyebrow">
                <span class="detail-eyebrow-dot"></span>
                Registro telefónico
            </div>

            <h1 class="detail-title">
                Llamada #{{ $llamada->id_llamada }}
            </h1>

            <p class="detail-description">
                Información registrada por el CRM, Asterisk y COMI.
            </p>
        </div>

        <a href="{{ route('llamadas.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
            Volver al historial
        </a>

    </div>


    {{-- RESUMEN --}}
    <div class="hero-call">

        <div class="hero-left">

            <div class="hero-icon">
                @if($llamada->tipo_llamada === 'saliente')
                    <i class="bi bi-telephone-outbound"></i>
                @else
                    <i class="bi bi-telephone-inbound"></i>
                @endif
            </div>

            <div>
                <h2 class="hero-number">
                    {{ $numeroPrincipal ?: 'Número no disponible' }}
                </h2>

                <div class="hero-client">
                    {{ $nombreCliente($llamada->cliente) }}
                </div>
            </div>

        </div>

        <div class="hero-badges">

            @if($llamada->tipo_llamada === 'saliente')
                <span class="detail-badge badge-out">
                    <i class="bi bi-telephone-outbound"></i>
                    Saliente
                </span>
            @else
                <span class="detail-badge badge-in">
                    <i class="bi bi-telephone-inbound"></i>
                    Entrante
                </span>
            @endif

            <span class="detail-badge badge-state">
                {{ $llamada->estadoLlamada->nombre ?? 'Sin estado' }}
            </span>

            @if($llamada->resultado)
                <span class="detail-badge badge-result">
                    {{ $llamada->resultado->nombre }}
                </span>
            @endif

        </div>

    </div>


    <div class="detail-grid">

        {{-- COLUMNA PRINCIPAL --}}
        <div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-info-circle"></i>
                    Información general
                </div>

                <div class="detail-card-body">

                    <div class="info-grid">

                        <div class="info-item">
                            <div class="info-label">Cliente</div>
                            <div class="info-value">
                                {{ $nombreCliente($llamada->cliente) }}
                            </div>
                            <div class="info-sub">
                                ID {{ $llamada->id_cliente }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Atendida por</div>
                            <div class="info-value">
                                @if($llamada->usuario)
                                    {{ $nombreUsuario($llamada->usuario) }}
                                @else
                                    <span style="color:#a78bfa;">
                                        <i class="bi bi-robot me-1"></i>
                                        COMI
                                    </span>
                                @endif
                            </div>
                            <div class="info-sub">
                                {{ $llamada->usuario ? 'Operario CRM' : 'Agente IA / sin operario asignado' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Número origen</div>
                            <div class="info-value">
                                {{ $llamada->numero_origen ?: '—' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Número destino</div>
                            <div class="info-value">
                                {{ $llamada->numero_destino ?: '—' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Campaña</div>
                            <div class="info-value">
                                {{ $llamada->campana->nombre ?? 'Sin campaña' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Empresa</div>
                            <div class="info-value">
                                {{ $llamada->empresa->nombre ?? '—' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Identificador Asterisk</div>
                            <div class="info-value">
                                {{ $llamada->identificador_asterisk ?: 'No registrado' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Duración</div>
                            <div class="info-value">
                                {{ $formatearDuracion($llamada->duracion) }}
                            </div>
                            <div class="info-sub">
                                {{ $llamada->duracion !== null ? $llamada->duracion . ' segundos' : 'Aún no calculada' }}
                            </div>
                        </div>

                    </div>

                </div>
            </div>


            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-chat-left-text"></i>
                    Observaciones
                </div>

                <div class="detail-card-body">

                    <div class="notes-box">{{ $llamada->observaciones ?: 'No hay observaciones registradas para esta llamada.' }}</div>

                </div>
            </div>


            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-calendar-check"></i>
                    Citas relacionadas
                </div>

                <div class="detail-card-body">

                    @if($llamada->citas && $llamada->citas->count())

                        <div class="citas-list">

                            @foreach($llamada->citas as $cita)

                                <div class="cita-item">

                                    <div>
                                        <div class="cita-date">
                                            {{ $cita->fecha_hora_inicio?->format('d/m/Y H:i') ?? 'Fecha no disponible' }}
                                        </div>

                                        <div class="cita-motive">
                                            {{ $cita->motivo ?: 'Sin motivo registrado' }}
                                        </div>
                                    </div>

                                    <div class="cita-status">
                                        {{ $cita->estadoCita->nombre ?? 'Sin estado' }}
                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="empty-small">
                            Esta llamada no tiene citas relacionadas.
                        </div>

                    @endif

                </div>
            </div>

        </div>


        {{-- COLUMNA DERECHA --}}
        <div>

            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-clock-history"></i>
                    Línea de tiempo
                </div>

                <div class="detail-card-body">

                    <div class="timeline">

                        <div class="timeline-item">
                            <span class="timeline-dot"></span>

                            <div class="timeline-title">
                                Inicio de llamada
                            </div>

                            <div class="timeline-value">
                                {{ $llamada->fecha_inicio?->format('d/m/Y H:i:s') ?? '—' }}
                            </div>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-dot"></span>

                            <div class="timeline-title">
                                Estado actual
                            </div>

                            <div class="timeline-value">
                                {{ $llamada->estadoLlamada->nombre ?? 'Sin estado' }}
                            </div>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-dot"></span>

                            <div class="timeline-title">
                                Finalización
                            </div>

                            <div class="timeline-value">
                                {{ $llamada->fecha_fin?->format('d/m/Y H:i:s') ?? 'Llamada todavía abierta o sin cierre' }}
                            </div>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-dot"></span>

                            <div class="timeline-title">
                                Resultado
                            </div>

                            <div class="timeline-value">
                                {{ $llamada->resultado->nombre ?? 'Pendiente' }}
                            </div>
                        </div>

                    </div>

                </div>
            </div>


            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-mic"></i>
                    Grabación
                </div>

                <div class="detail-card-body">

                    @if($llamada->grabacion_url)

                        <div class="recording-box">

                            <div>
                                <div class="recording-title">
                                    Audio disponible
                                </div>

                                <div class="recording-sub">
                                    {{ $llamada->grabacion_url }}
                                </div>
                            </div>

                            <a
                                href="{{ $llamada->grabacion_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-recording"
                            >
                                <i class="bi bi-play-fill"></i>
                                Abrir
                            </a>

                        </div>

                    @else

                        <div class="empty-small">
                            Asterisk / COMI todavía no ha registrado una grabación.
                        </div>

                    @endif

                </div>
            </div>


            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-diagram-3"></i>
                    Datos técnicos
                </div>

                <div class="detail-card-body">

                    <div class="info-grid">

                        <div class="info-item">
                            <div class="info-label">Folio de llamada</div>
                            <div class="info-value">
                                #{{ $llamada->id_llamada }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Empresa</div>
                            <div class="info-value">
                                {{ $llamada->empresa?->nombre ?? 'Sin empresa' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Estado</div>
                            <div class="info-value">
                                {{ $llamada->estadoLlamada?->nombre ?? 'Sin estado' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Resultado</div>
                            <div class="info-value">
                                {{ $llamada->resultado?->nombre ?? 'Pendiente' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Registrada</div>
                            <div class="info-value">
                                {{ $llamada->created_at?->format('d/m/Y H:i:s') ?? '—' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Última actualización</div>
                            <div class="info-value">
                                {{ $llamada->updated_at?->format('d/m/Y H:i:s') ?? '—' }}
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>

</div>

@endsection
