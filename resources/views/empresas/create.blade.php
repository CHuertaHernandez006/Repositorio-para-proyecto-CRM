{{-- resources/views/empresas/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo inquilino - CRM')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Estilos limitados al formulario de empresas. */
    .comi-empresa-form {
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
    .comi-empresa-form, .comi-empresa-form *, .comi-empresa-form *::before, .comi-empresa-form *::after { box-sizing: border-box; }
    .comi-empresa-form a { text-decoration: none; }
    .comi-empresa-form button, .comi-empresa-form input, .comi-empresa-form select { font: inherit; }
    .comi-empresa-form .cf-back { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; color: #b6c8df; font-size: 13px; margin-bottom: 20px; }
    .comi-empresa-form .cf-back:hover { color: var(--cf-accent); }
    .comi-empresa-form .cf-header { margin-bottom: 28px; }
    .comi-empresa-form .cf-eyebrow { margin: 0 0 10px; color: var(--cf-accent); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-empresa-form .cf-title { margin: 0; color: var(--cf-text); font-size: clamp(26px, 3vw, 34px); font-weight: 700; line-height: 1.2; letter-spacing: -.035em; }
    .comi-empresa-form .cf-subtitle { margin: 10px 0 0; color: var(--cf-muted); font-size: 14px; max-width: 650px; }
    .comi-empresa-form .cf-card { max-width: 940px; background: var(--cf-panel); border: 1px solid var(--cf-border); border-radius: 13px; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 12%); }
    .comi-empresa-form .cf-card-heading { display: flex; align-items: center; gap: 13px; padding: 24px 28px; border-bottom: 1px solid var(--cf-border); }
    .comi-empresa-form .cf-heading-icon { display: grid; place-items: center; width: 44px; height: 44px; flex-shrink: 0; border: 1px solid #31516a; border-radius: 11px; background: #17364a; color: var(--cf-accent); font-size: 21px; }
    .comi-empresa-form .cf-card-title { margin: 0; color: var(--cf-text); font-size: 16px; font-weight: 600; }
    .comi-empresa-form .cf-card-note { margin: 4px 0 0; color: var(--cf-muted); font-size: 12px; }
    .comi-empresa-form .cf-form { margin: 0; }
    .comi-empresa-form .cf-body { padding: 28px; }
    .comi-empresa-form .cf-label { display: block; color: #e2e8f0; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
    .comi-empresa-form .cf-required { color: #fda4af; margin-left: 3px; }
    .comi-empresa-form .cf-field { position: relative; }
    .comi-empresa-form .cf-field > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 17px; pointer-events: none; }
    .comi-empresa-form .cf-input { display: block; width: 100%; min-height: 50px; padding: 12px 15px 12px 44px; border: 1px solid #475569; border-radius: 9px; background: var(--cf-bg); color: var(--cf-text); font-size: 14px; transition: border-color .15s, box-shadow .15s; }
    .comi-empresa-form .cf-input::placeholder { color: #94a3b8; opacity: 1; }
    .comi-empresa-form .cf-input:focus { outline: 2px solid var(--cf-accent); outline-offset: 2px; border-color: var(--cf-accent); }
    .comi-empresa-form .cf-field:focus-within > i { color: var(--cf-accent); }
    .comi-empresa-form .cf-input[aria-invalid="true"] { border-color: #fb7185; }
    .comi-empresa-form .cf-help { margin: 10px 0 0; color: var(--cf-muted); font-size: 12px; }
    .comi-empresa-form .cf-error { display: flex; align-items: flex-start; gap: 7px; margin: 10px 0 0; color: #fda4af; font-size: 12px; }
    .comi-empresa-form .cf-info { display: flex; align-items: flex-start; gap: 12px; margin-top: 26px; padding: 16px; border: 1px solid #31516a; border-radius: 9px; background: #172d41; }
    .comi-empresa-form .cf-info > i { flex-shrink: 0; color: var(--cf-accent); font-size: 17px; }
    .comi-empresa-form .cf-info-title { margin: 0 0 4px; color: #d7efff; font-size: 12px; font-weight: 600; }
    .comi-empresa-form .cf-info-text { margin: 0; color: #b6c8df; font-size: 12px; }
    .comi-empresa-form .cf-footer { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap; padding: 20px 28px; border-top: 1px solid var(--cf-border); background: #1a2639; }
    .comi-empresa-form .cf-required-note { margin: 0; color: var(--cf-muted); font-size: 11px; }
    .comi-empresa-form .cf-actions { display: flex; align-items: center; gap: 10px; }
    .comi-empresa-form .cf-btn { display: inline-flex; justify-content: center; align-items: center; gap: 8px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; white-space: nowrap; cursor: pointer; transition: background-color .15s, border-color .15s; }
    .comi-empresa-form .cf-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-empresa-form .cf-btn-primary:hover { background: #0369a1; border-color: var(--cf-accent); }
    .comi-empresa-form .cf-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; }
    .comi-empresa-form .cf-btn-secondary:hover { background: #26364d; border-color: #94a3b8; }
    .comi-empresa-form :is(a, button):focus-visible { outline: 2px solid var(--cf-accent); outline-offset: 3px; }
    .comi-empresa-form .cf-alert { max-width: 940px; padding: 14px 16px; margin: 0 0 18px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; font-size: 13px; }
    .comi-empresa-form .cf-alert ul { margin: 8px 0 0; padding-left: 20px; }
    @media (max-width: 640px) {
        .comi-empresa-form { padding: 18px 12px; border-radius: 10px; }
        .comi-empresa-form .cf-card-heading, .comi-empresa-form .cf-body { padding: 20px 18px; }
        .comi-empresa-form .cf-footer { padding: 18px; align-items: stretch; flex-direction: column; }
        .comi-empresa-form .cf-actions { flex-wrap: wrap; }
        .comi-empresa-form .cf-actions .cf-btn { flex: 1 1 150px; }
    }
    .comi-empresa-form .cf-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px; }
    .comi-empresa-form .cf-section { margin: 0; padding: 0; border: 0; min-width: 0; }
    .comi-empresa-form .cf-section + .cf-section { margin-top: 30px; padding-top: 26px; border-top: 1px solid var(--cf-border); }
    .comi-empresa-form .cf-legend { display: flex; align-items: center; gap: 10px; padding: 0; margin-bottom: 18px; color: #f1f5f9; font-size: 15px; font-weight: 600; }
    .comi-empresa-form .cf-step { display: inline-grid; place-items: center; width: 27px; height: 27px; background: #17364a; color: #7dd3fc; border: 1px solid #31516a; border-radius: 8px; font-size: 12px; }
    .comi-empresa-form .cf-section-note { margin: -4px 0 20px; color: var(--cf-muted); font-size: 12px; }
    .comi-empresa-form [hidden] { display: none !important; }
    .comi-empresa-form .cf-select { padding-left: 14px; }
    .comi-empresa-form .cf-btn { white-space: normal; text-align: center; }
    @media (max-width: 720px) { .comi-empresa-form .cf-grid { grid-template-columns: 1fr; } }
</style>

@php
    $tipoInquilino = old('tipo_cliente', 'empresa');
@endphp
<section class="comi-empresa-form" aria-labelledby="empresa-create-title">
    <a href="{{ route('empresas.index') }}" class="cf-back">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a empresas
    </a>
    <header class="cf-header">
        <p class="cf-eyebrow">COMICenter / Administración</p>
        <h1 class="cf-title" id="empresa-create-title">Registrar nuevo inquilino</h1>
        <p class="cf-subtitle">Crea el entorno de trabajo y la cuenta del Administrador Cliente en un solo paso.</p>
    </header>

    @if (session('error'))
        <div class="cf-alert" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="cf-alert" role="alert">
            No se pudo completar el registro. Revisa los datos del formulario.
            <ul>
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cf-card">
        <div class="cf-card-heading">
            <span class="cf-heading-icon"><i class="bi bi-building-add" aria-hidden="true"></i></span>
            <div>
                <h2 class="cf-card-title">Organización y administrador</h2>
                <p class="cf-card-note">Completa la información para dar de alta al nuevo inquilino.</p>
            </div>
        </div>
        <form action="{{ route('empresas.store') }}" method="POST" class="cf-form">
            @csrf
            <div class="cf-body">
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">1</span> Datos de la organización</legend>
                    <div class="cf-grid">
                        <div>
                            <label for="tipo_cliente" class="cf-label">Tipo de inquilino <span class="cf-required" aria-hidden="true">*</span></label>
                            <select name="tipo_cliente" id="tipo_cliente" class="cf-input cf-select" required autofocus
                                aria-describedby="tipo-ayuda @error('tipo_cliente') tipo-error @enderror"
                                @error('tipo_cliente') aria-invalid="true" @enderror>
                                <option value="empresa" {{ $tipoInquilino === 'empresa' ? 'selected' : '' }}>Empresa</option>
                                <option value="independiente" {{ $tipoInquilino === 'independiente' ? 'selected' : '' }}>Profesional independiente</option>
                            </select>
                            <p class="cf-help" id="tipo-ayuda">Selecciona si registrarás una empresa o un profesional independiente.</p>
                            @error('tipo_cliente') <p class="cf-error" id="tipo-error">{{ $message }}</p> @enderror
                        </div>
                        <div id="div_empresa" {{ $tipoInquilino === 'independiente' ? 'hidden' : '' }}>
                            <label for="nombre_empresa" class="cf-label">Nombre comercial <span class="cf-required" aria-hidden="true">*</span></label>
                            <div class="cf-field">
                                <i class="bi bi-building" aria-hidden="true"></i>
                                <input type="text" name="nombre_empresa" id="nombre_empresa" class="cf-input"
                                    placeholder="Ej. Nova Solutions" value="{{ old('nombre_empresa') }}" autocomplete="organization"
                                    {{ $tipoInquilino === 'independiente' ? 'disabled' : 'required' }}
                                    @error('nombre_empresa') aria-invalid="true" aria-describedby="empresa-error" @enderror>
                            </div>
                            @error('nombre_empresa') <p class="cf-error" id="empresa-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">2</span> Administrador inicial</legend>
                    <p class="cf-section-note">Esta persona será el Administrador Cliente del nuevo entorno de trabajo.</p>
                    <div class="cf-grid">
                        <div>
                            <label for="nombre_admin" class="cf-label">Nombre completo <span class="cf-required" aria-hidden="true">*</span></label>
                            <div class="cf-field">
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <input type="text" name="nombre_admin" id="nombre_admin" class="cf-input" required
                                    placeholder="Ej. Carlos Martínez" value="{{ old('nombre_admin') }}" autocomplete="name"
                                    @error('nombre_admin') aria-invalid="true" aria-describedby="admin-error" @enderror>
                            </div>
                            @error('nombre_admin') <p class="cf-error" id="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="correo_admin" class="cf-label">Correo para recibir credenciales <span class="cf-required" aria-hidden="true">*</span></label>
                            <div class="cf-field">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <input type="email" name="correo_admin" id="correo_admin" class="cf-input" required
                                    placeholder="correo@empresa.com" value="{{ old('correo_admin') }}" autocomplete="email"
                                    aria-describedby="correo-ayuda @error('correo_admin') correo-error @enderror"
                                    @error('correo_admin') aria-invalid="true" @enderror>
                            </div>
                            <p class="cf-help" id="correo-ayuda">Ingresa un correo real al que el administrador tenga acceso, personal o de trabajo.</p>
                            @error('correo_admin') <p class="cf-error" id="correo-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <aside class="cf-info" aria-labelledby="acceso-title">
                        <i class="bi bi-person-check" aria-hidden="true"></i>
                        <div>
                            <h3 class="cf-info-title" id="acceso-title">Cuenta del administrador</h3>
                            <p class="cf-info-text">Las credenciales se generarán y enviarán automáticamente al correo indicado al completar el registro.</p>
                        </div>
                    </aside>
                </fieldset>
            </div>
            <div class="cf-footer">
                <p class="cf-required-note"><span class="cf-required" aria-hidden="true">*</span> Campos obligatorios</p>
                <div class="cf-actions">
                    <a href="{{ route('empresas.index') }}" class="cf-btn cf-btn-secondary">Cancelar</a>
                    <button type="submit" class="cf-btn cf-btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i> Guardar inquilino y generar cuenta
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
    (() => {
        const tipo = document.getElementById('tipo_cliente');
        const contenedor = document.getElementById('div_empresa');
        const nombre = document.getElementById('nombre_empresa');
        if (!tipo || !contenedor || !nombre) return;

        function actualizarEmpresa() {
            const esEmpresa = tipo.value === 'empresa';
            contenedor.hidden = !esEmpresa;
            nombre.required = esEmpresa;
            nombre.disabled = !esEmpresa;
        }

        tipo.addEventListener('change', actualizarEmpresa);
        window.addEventListener('pageshow', actualizarEmpresa);
        actualizarEmpresa();
    })();
</script>
@endsection
