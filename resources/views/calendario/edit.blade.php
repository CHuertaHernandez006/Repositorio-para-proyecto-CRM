@extends('layouts.app')

@section('title', 'Editar Cita - CRM')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .comi-cita-form {
        --cf-bg: #0f172a;
        --cf-panel: #1e293b;
        --cf-border: #334155;
        --cf-text: #f1f5f9;
        --cf-muted: #a5b4c8;
        --cf-accent: #38bdf8;
        min-width: 0;
        padding: clamp(18px, 3vw, 36px);
        border-radius: 16px;
        background: var(--cf-bg);
        color: var(--cf-text);
        font-family: inherit;
        line-height: 1.5;
        color-scheme: dark;
    }
    .comi-cita-form, .comi-cita-form *, .comi-cita-form *::before, .comi-cita-form *::after { box-sizing: border-box; }
    .comi-cita-form a { text-decoration: none; }
    .comi-cita-form button, .comi-cita-form input, .comi-cita-form select, .comi-cita-form textarea { font: inherit; }
    .comi-cita-form .cf-back { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; color: #b6c8df; font-size: 13px; margin-bottom: 20px; transition: color .15s; }
    .comi-cita-form .cf-back:hover { color: var(--cf-accent); }
    .comi-cita-form .cf-header { margin-bottom: 28px; }
    .comi-cita-form .cf-eyebrow { margin: 0 0 10px; color: var(--cf-accent); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-cita-form .cf-title { margin: 0; color: var(--cf-text); font-size: clamp(26px, 3vw, 34px); font-weight: 700; line-height: 1.2; letter-spacing: -.035em; }
    .comi-cita-form .cf-subtitle { margin: 10px 0 0; color: var(--cf-muted); font-size: 14px; max-width: 650px; }
    .comi-cita-form .cf-card { max-width: 940px; background: var(--cf-panel); border: 1px solid var(--cf-border); border-radius: 13px; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 12%); }
    .comi-cita-form .cf-card-heading { display: flex; align-items: center; gap: 13px; padding: 24px 28px; border-bottom: 1px solid var(--cf-border); }
    .comi-cita-form .cf-heading-icon { display: grid; place-items: center; width: 44px; height: 44px; flex-shrink: 0; border: 1px solid #31516a; border-radius: 11px; background: #17364a; color: var(--cf-accent); font-size: 21px; }
    .comi-cita-form .cf-card-title { margin: 0; color: var(--cf-text); font-size: 16px; font-weight: 600; }
    .comi-cita-form .cf-card-note { margin: 4px 0 0; color: var(--cf-muted); font-size: 12px; }
    .comi-cita-form .cf-form { margin: 0; }
    .comi-cita-form .cf-body { padding: 28px; }
    .comi-cita-form .cf-label { display: block; color: #e2e8f0; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
    .comi-cita-form .cf-required { color: #fda4af; margin-left: 3px; }
    .comi-cita-form .cf-field { position: relative; }
    .comi-cita-form .cf-field > i { position: absolute; left: 15px; top: 16px; color: #94a3b8; font-size: 17px; pointer-events: none; }
    .comi-cita-form .cf-field-single > i { top: 50%; transform: translateY(-50%); }
    .comi-cita-form .cf-input { display: block; width: 100%; min-height: 50px; padding: 12px 15px 12px 44px; border: 1px solid #475569; border-radius: 9px; background: var(--cf-bg); color: var(--cf-text); font-size: 14px; transition: border-color .15s, box-shadow .15s; }
    .comi-cita-form textarea.cf-input { min-height: 90px; resize: vertical; }
    .comi-cita-form .cf-input::placeholder { color: #94a3b8; opacity: 1; }
    .comi-cita-form .cf-input:focus { outline: 2px solid var(--cf-accent); outline-offset: 2px; border-color: var(--cf-accent); }
    .comi-cita-form .cf-field:focus-within > i { color: var(--cf-accent); }
    .comi-cita-form .cf-select { padding-left: 14px; }
    .comi-cita-form .cf-select option { background: var(--cf-bg); color: var(--cf-text); }
    .comi-cita-form .cf-footer { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap; padding: 20px 28px; border-top: 1px solid var(--cf-border); background: #1a2639; }
    .comi-cita-form .cf-required-note { margin: 0; color: var(--cf-muted); font-size: 11px; }
    .comi-cita-form .cf-actions { display: flex; align-items: center; gap: 10px; }
    .comi-cita-form .cf-btn { display: inline-flex; justify-content: center; align-items: center; gap: 8px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background-color .15s, border-color .15s; }
    .comi-cita-form .cf-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-cita-form .cf-btn-primary:hover { background: #0369a1; border-color: var(--cf-accent); }
    .comi-cita-form .cf-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; }
    .comi-cita-form .cf-btn-secondary:hover { background: #26364d; border-color: #94a3b8; }
    .comi-cita-form .cf-alert { max-width: 940px; padding: 14px 16px; margin: 0 0 18px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; font-size: 13px; }
    .comi-cita-form .cf-alert ul { margin: 8px 0 0; padding-left: 20px; }
    .comi-cita-form .cf-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
    .comi-cita-form .cf-section { margin: 0; padding: 0; border: 0; min-width: 0; }
    .comi-cita-form .cf-section + .cf-section { margin-top: 30px; padding-top: 26px; border-top: 1px solid var(--cf-border); }
    .comi-cita-form .cf-legend { display: flex; align-items: center; gap: 10px; padding: 0; margin-bottom: 18px; color: #f1f5f9; font-size: 15px; font-weight: 600; }
    .comi-cita-form .cf-step { display: inline-grid; place-items: center; width: 27px; height: 27px; background: #17364a; color: #7dd3fc; border: 1px solid #31516a; border-radius: 8px; font-size: 12px; }

    @media (max-width: 840px) { .comi-cita-form .cf-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .comi-cita-form { padding: 18px 12px; border-radius: 10px; }
        .comi-cita-form .cf-card-heading, .comi-cita-form .cf-body { padding: 20px 18px; }
        .comi-cita-form .cf-footer { padding: 18px; flex-direction: column; align-items: stretch; }
        .comi-cita-form .cf-actions { flex-wrap: wrap; }
        .comi-cita-form .cf-actions .cf-btn { flex: 1 1 150px; }
    }
</style>

<section class="comi-cita-form" aria-labelledby="cita-edit-title">
    <a href="{{ route('calendario.index') }}" class="cf-back">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a la agenda
    </a>

    <header class="cf-header">
        <p class="cf-eyebrow">COMICenter / Panel de Gestión</p>
        <h1 class="cf-title" id="cita-edit-title">Actualizar cita</h1>
        <p class="cf-subtitle">Modifica el horario, la información del solicitante o el estado de la cita.</p>
    </header>

    @if (session('error'))
        <div class="cf-alert" role="alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="cf-alert" role="alert">
            No se pudo actualizar la cita. Revisa los datos ingresados.
            <ul>
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cf-card">
        <div class="cf-card-heading">
            <span class="cf-heading-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
            <div>
                <h2 class="cf-card-title">Ficha de Consulta #{{ $cita->id_cita }}</h2>
                <p class="cf-card-note">Actualiza los datos del solicitante, fecha y estatus operativo.</p>
            </div>
        </div>

        <form action="{{ route('calendario.update', $cita->id_cita) }}" method="POST" class="cf-form">
            @csrf
            @method('PUT')
            
            <div class="cf-body">

                <!-- Sección 1: Información del Solicitante -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">1</span> Datos del solicitante</legend>
                    <div class="cf-grid">
                        <div>
                            <label for="nombre_solicitante" class="cf-label">Nombre del solicitante <span class="cf-required" aria-hidden="true">*</span></label>
                            <div class="cf-field cf-field-single">
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <input type="text" name="nombre_solicitante" id="nombre_solicitante" class="cf-input" 
                                    value="{{ old('nombre_solicitante', $cita->nombre_solicitante ?? $cita->nombre_cliente) }}" required>
                            </div>
                        </div>

                        <div>
                            <label for="telefono_solicitante" class="cf-label">Teléfono de contacto</label>
                            <div class="cf-field cf-field-single">
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                <input type="tel" name="telefono_solicitante" id="telefono_solicitante" class="cf-input" 
                                    value="{{ old('telefono_solicitante', $cita->telefono_solicitante ?? $cita->telefono) }}">
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <label for="correo_solicitante" class="cf-label">Correo electrónico (Opcional)</label>
                        <div class="cf-field cf-field-single">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input type="email" name="correo_solicitante" id="correo_solicitante" class="cf-input" 
                                value="{{ old('correo_solicitante', $cita->correo_solicitante ?? $cita->correo) }}">
                        </div>
                    </div>
                </fieldset>

                <!-- Sección 2: Programación, Consulta y Estado -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">2</span> Programación y estado</legend>

                    <div style="margin-bottom: 20px;">
                        <label for="motivo" class="cf-label">Motivo / Consulta <span class="cf-required" aria-hidden="true">*</span></label>
                        <div class="cf-field">
                            <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                            <textarea name="motivo" id="motivo" class="cf-input" rows="2" required>{{ old('motivo', $cita->motivo) }}</textarea>
                        </div>
                    </div>

                    <div class="cf-grid" style="margin-bottom: 20px;">
                        <div>
                            <label for="fecha_hora_inicio" class="cf-label">Fecha y hora inicio <span class="cf-required" aria-hidden="true">*</span></label>
                            <div class="cf-field cf-field-single">
                                <i class="bi bi-clock" aria-hidden="true"></i>
                                <input type="datetime-local" name="fecha_hora_inicio" id="fecha_hora_inicio" class="cf-input" 
                                    value="{{ old('fecha_hora_inicio', \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('Y-m-d\TH:i')) }}" required>
                            </div>
                        </div>

                        <div>
                            <label for="fecha_hora_fin" class="cf-label">Fecha y hora fin (Opcional)</label>
                            <div class="cf-field cf-field-single">
                                <i class="bi bi-clock-history" aria-hidden="true"></i>
                                <input type="datetime-local" name="fecha_hora_fin" id="fecha_hora_fin" class="cf-input" 
                                    value="{{ old('fecha_hora_fin', $cita->fecha_hora_fin ? \Carbon\Carbon::parse($cita->fecha_hora_fin)->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="id_estado_cita" class="cf-label">Estado de la cita <span class="cf-required" aria-hidden="true">*</span></label>
                        <select name="id_estado_cita" id="id_estado_cita" class="cf-input cf-select" required>
                            @foreach($estadosCita as $estado)
                                <option value="{{ $estado->id_estado_cita }}" {{ old('id_estado_cita', $cita->id_estado_cita) == $estado->id_estado_cita ? 'selected' : '' }}>
                                    {{ $estado->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>

                <!-- Sección 3: Observaciones -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">3</span> Notas adicionales</legend>
                    <div>
                        <label for="observaciones" class="cf-label">Observaciones</label>
                        <div class="cf-field">
                            <i class="bi bi-card-text" aria-hidden="true"></i>
                            <textarea name="observaciones" id="observaciones" class="cf-input" rows="3">{{ old('observaciones', $cita->observaciones) }}</textarea>
                        </div>
                    </div>
                </fieldset>

            </div>

            <div class="cf-footer">
                <p class="cf-required-note"><span class="cf-required" aria-hidden="true">*</span> Campos obligatorios</p>
                <div class="cf-actions">
                    <a href="{{ route('calendario.index') }}" class="cf-btn cf-btn-secondary">Cancelar</a>
                    <button type="submit" class="cf-btn cf-btn-primary">
                        <i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualizar cita
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection