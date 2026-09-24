@extends('layouts.app')

@section('title', 'Agendar Nueva Cita - Calendario')

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
    .comi-cita-form .cf-field > i { position: absolute; left: 15px; top: 16px; color: #94a3b8; font-size: 17px; pointer-events: none; z-index: 2; }
    .comi-cita-form .cf-field-single > i { top: 50%; transform: translateY(-50%); }
    .comi-cita-form .cf-input { display: block; width: 100%; min-height: 50px; padding: 12px 15px 12px 44px; border: 1px solid #475569; border-radius: 9px; background: var(--cf-bg); color: var(--cf-text); font-size: 14px; transition: border-color .15s, box-shadow .15s; }
    .comi-cita-form textarea.cf-input { min-height: 120px; resize: vertical; padding-top: 15px; }
    .comi-cita-form .cf-input::placeholder { color: #94a3b8; opacity: 1; }
    .comi-cita-form .cf-input:focus { outline: 2px solid var(--cf-accent); outline-offset: 2px; border-color: var(--cf-accent); }
    .comi-cita-form .cf-field:focus-within > i { color: var(--cf-accent); }
    .comi-cita-form .cf-footer { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap; padding: 20px 28px; border-top: 1px solid var(--cf-border); background: #1a2639; }
    .comi-cita-form .cf-required-note { margin: 0; color: var(--cf-muted); font-size: 11px; }
    .comi-cita-form .cf-actions { display: flex; align-items: center; gap: 10px; }
    .comi-cita-form .cf-btn { display: inline-flex; justify-content: center; align-items: center; gap: 8px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background-color .15s, border-color .15s; }
    .comi-cita-form .cf-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-cita-form .cf-btn-primary:hover { background: #0369a1; border-color: var(--cf-accent); }
    .comi-cita-form .cf-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; text-decoration: none; }
    .comi-cita-form .cf-btn-secondary:hover { background: #26364d; border-color: #94a3b8; color: #fff; }
    .comi-cita-form .cf-alert { max-width: 940px; padding: 14px 16px; margin: 0 0 18px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; font-size: 13px; }
    .comi-cita-form .cf-alert ul { margin: 8px 0 0; padding-left: 20px; }
    .comi-cita-form .cf-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
    .comi-cita-form .cf-grid-full { grid-template-columns: 1fr; }
    .comi-cita-form .cf-section { margin: 0; padding: 0; border: 0; min-width: 0; }
    .comi-cita-form .cf-section + .cf-section { margin-top: 30px; padding-top: 26px; border-top: 1px solid var(--cf-border); }
    .comi-cita-form .cf-legend { display: flex; align-items: center; gap: 10px; padding: 0; margin-bottom: 6px; color: #f1f5f9; font-size: 15px; font-weight: 600; }
    .comi-cita-form .cf-step { display: inline-grid; place-items: center; width: 27px; height: 27px; background: #17364a; color: #7dd3fc; border: 1px solid #31516a; border-radius: 8px; font-size: 12px; }
    .comi-cita-form .invalid-feedback { color: #fda4af; font-size: 12px; margin-top: 6px; display: block; }

    /* Estilos TomSelect */
    .ts-wrapper .ts-control { background-color: var(--cf-bg) !important; border: 1px solid #475569 !important; color: var(--cf-text) !important; border-radius: 9px !important; min-height: 50px !important; padding: 10px 14px 10px 40px !important; font-size: 14px !important; box-shadow: none !important; }
    .ts-wrapper.focus .ts-control { border-color: var(--cf-accent) !important; box-shadow: 0 0 0 2px var(--cf-accent) !important; }
    .ts-wrapper .ts-control input { color: var(--cf-text) !important; }
    .ts-wrapper .ts-dropdown { background-color: var(--cf-panel) !important; border: 1px solid var(--cf-border) !important; color: var(--cf-text) !important; border-radius: 9px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.5) !important; }
    .ts-wrapper .ts-dropdown .option { color: var(--cf-muted) !important; padding: 10px 14px !important; }
    .ts-wrapper .ts-dropdown .option.active, .ts-wrapper .ts-dropdown .option:hover { background-color: #334155 !important; color: #ffffff !important; }

    @media (max-width: 840px) { .comi-cita-form .cf-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .comi-cita-form { padding: 18px 12px; border-radius: 10px; }
        .comi-cita-form .cf-card-heading, .comi-cita-form .cf-body { padding: 20px 18px; }
        .comi-cita-form .cf-footer { padding: 18px; flex-direction: column; align-items: stretch; }
        .comi-cita-form .cf-actions { flex-wrap: wrap; }
        .comi-cita-form .cf-actions .cf-btn { flex: 1 1 150px; }
    }
</style>

<section class="comi-cita-form" aria-labelledby="cita-create-title">
    <a href="{{ route('calendario.index') }}" class="cf-back">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a la agenda
    </a>

    <header class="cf-header">
        <p class="cf-eyebrow">COMICenter / Panel de Gestión</p>
        <h1 class="cf-title" id="cita-create-title">Agendar nueva cita</h1>
        <p class="cf-subtitle">Programa una cita para atender las solicitudes y dar seguimiento.</p>
    </header>

    @if ($errors->any())
        <div class="cf-alert" role="alert">
            <strong>
                <i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>
                No se pudo registrar la cita.
            </strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cf-card">
        <div class="cf-card-heading">
            <span class="cf-heading-icon"><i class="bi bi-calendar-plus" aria-hidden="true"></i></span>
            <div>
                <h2 class="cf-card-title">Registrar nueva cita</h2>
                <p class="cf-card-note">Selecciona el cliente y los detalles de programación de la consulta.</p>
            </div>
        </div>

        <form action="{{ route('calendario.store') }}" method="POST" class="cf-form">
            @csrf
            
            <div class="cf-body">

                <!-- Sección 1: Participantes -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">1</span> Participantes</legend>
                    <p class="cf-card-note" style="margin-bottom: 20px;">Selecciona el cliente para esta cita.</p>

                    @if(auth()->user()->id_rol == 3)
                        {{-- ID de usuario autoseleccionado en segundo plano --}}
                        <input type="hidden" name="id_usuario" value="{{ auth()->user()->id_usuario ?? auth()->user()->id }}">
                    @endif

                    <div class="cf-grid {{ auth()->user()->id_rol == 3 ? 'cf-grid-full' : '' }}">
                        <!-- CLIENTE -->
                        <div>
                            <label for="id_cliente" class="cf-label">
                                Cliente <span class="cf-required" aria-hidden="true">*</span>
                            </label>
                            <div class="cf-field">
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <select name="id_cliente" id="id_cliente" required>
                                    <option value="" disabled selected>Selecciona un cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id_cliente }}" {{ old('id_cliente') == $cliente->id_cliente ? 'selected' : '' }}>
                                            {{ $cliente->nombre }} {{ $cliente->apellido_paterno }}
                                            @if($cliente->apellido_materno) {{ $cliente->apellido_materno }} @endif
                                            @if($cliente->empresa) — {{ $cliente->empresa }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('id_cliente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- OPERARIO (Visibilidad sólo para Administradores) -->
                        @if(auth()->user()->id_rol != 3)
                            <div>
                                <label for="id_usuario" class="cf-label">
                                    Operario Asignado <span class="cf-required" aria-hidden="true">*</span>
                                </label>
                                <div class="cf-field">
                                    <i class="bi bi-headset" aria-hidden="true"></i>
                                    <select name="id_usuario" id="id_usuario" required>
                                        <option value="" disabled selected>Selecciona un operario</option>
                                        @foreach($operarios as $operario)
                                            <option value="{{ $operario->id }}" {{ old('id_usuario') == $operario->id ? 'selected' : '' }}>
                                                {{ $operario->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('id_usuario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </fieldset>

                <!-- Sección 2: Programación -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">2</span> Programación</legend>
                    <p class="cf-card-note" style="margin-bottom: 20px;">Define cuándo está programada la cita.</p>

                    <div class="cf-grid">
                        <!-- FECHA Y HORA -->
                        <div>
                            <label for="fecha_hora_inicio" class="cf-label">
                                Fecha y hora de la cita <span class="cf-required" aria-hidden="true">*</span>
                            </label>
                            <div class="cf-field cf-field-single">
                                <i class="bi bi-calendar-event" aria-hidden="true"></i>
                                <input type="datetime-local" name="fecha_hora_inicio" id="fecha_hora_inicio" class="cf-input" value="{{ old('fecha_hora_inicio') }}" required>
                            </div>
                            @error('fecha_hora_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- NOTA SOBRE FINALIZACIÓN -->
                        <div>
                            <label class="cf-label">
                                Finalización
                            </label>
                            <div class="cf-input" style="display: flex; align-items: center; color: var(--cf-muted) !important; padding-left: 44px; position: relative; font-size: 13px;">
                                <i class="bi bi-clock-history" aria-hidden="true" style="position: absolute; left: 15px; color: #94a3b8; font-size: 17px;"></i>
                                La hora de finalización se registrará cuando el operario termine la llamada con el cliente.
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Sección 3: Detalles -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">3</span> Detalles</legend>
                    <p class="cf-card-note" style="margin-bottom: 20px;">Añade información adicional relacionada con la cita.</p>

                    <!-- MOTIVO -->
                    <div style="margin-bottom: 20px;">
                        <label for="motivo" class="cf-label">Motivo</label>
                        <div class="cf-field cf-field-single">
                            <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                            <input type="text" name="motivo" id="motivo" class="cf-input" value="{{ old('motivo') }}" maxlength="255" placeholder="Motivo de la cita">
                        </div>
                        @error('motivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- OBSERVACIONES -->
                    <div>
                        <label for="observaciones" class="cf-label">Observaciones</label>
                        <div class="cf-field">
                            <i class="bi bi-journal-text" aria-hidden="true"></i>
                            <textarea name="observaciones" id="observaciones" class="cf-input" maxlength="1000" placeholder="Agrega observaciones, acuerdos o información importante sobre la cita...">{{ old('observaciones') }}</textarea>
                        </div>
                        <p class="cf-card-note" style="margin-top: 6px;">Máximo 1000 caracteres.</p>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </fieldset>

            </div>

            <div class="cf-footer">
                <p class="cf-required-note"><span class="cf-required" aria-hidden="true">*</span> Campos obligatorios</p>
                <div class="cf-actions">
                    <a href="{{ route('calendario.index') }}" class="cf-btn cf-btn-secondary">
                        <i class="bi bi-x-lg" aria-hidden="true"></i> Cancelar
                    </a>
                    <button type="submit" class="cf-btn cf-btn-primary">
                        <i class="bi bi-calendar-check" aria-hidden="true"></i> Registrar cita
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('id_cliente')) {
            new TomSelect('#id_cliente', {
                create: false,
                placeholder: 'Selecciona o busca un cliente...',
                maxOptions: 50,
            });
        }
        if (document.getElementById('id_usuario')) {
            new TomSelect('#id_usuario', {
                create: false,
                placeholder: 'Selecciona o busca un operario...',
                maxOptions: 50,
            });
        }
    });
</script>
@endpush
@endsection