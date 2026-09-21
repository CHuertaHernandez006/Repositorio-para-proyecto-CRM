{{-- resources/views/empresas/edit_admin.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar administrador - CRM')
@section('header-title', 'Empresas')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Estilos exclusivos de esta vista. */
    .comi-admin-edit {
        --ed-bg: #0f172a;
        --ed-panel: #1e293b;
        --ed-border: #334155;
        --ed-text: #f1f5f9;
        --ed-muted: #a5b4c8;
        --ed-accent: #38bdf8;
        min-width: 0;
        padding: clamp(18px, 3vw, 36px);
        border-radius: 16px;
        background: var(--ed-bg);
        color: var(--ed-text);
        font-family: inherit;
        line-height: 1.5;
        color-scheme: dark;
    }
    .comi-admin-edit, .comi-admin-edit *, .comi-admin-edit *::before, .comi-admin-edit *::after { box-sizing: border-box; }
    .comi-admin-edit a { text-decoration: none; }
    .comi-admin-edit button, .comi-admin-edit input { font: inherit; }
    .comi-admin-edit .ed-back { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; color: #b6c8df; font-size: 13px; margin-bottom: 20px; }
    .comi-admin-edit .ed-back:hover { color: var(--ed-accent); }
    .comi-admin-edit .ed-header { margin-bottom: 28px; }
    .comi-admin-edit .ed-eyebrow { margin: 0 0 10px; color: var(--ed-accent); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-admin-edit .ed-title { margin: 0; color: var(--ed-text); font-size: clamp(26px, 3vw, 34px); font-weight: 700; line-height: 1.2; letter-spacing: -.035em; }
    .comi-admin-edit .ed-subtitle { margin: 10px 0 0; color: var(--ed-muted); font-size: 14px; }
    .comi-admin-edit .ed-card { max-width: 780px; background: var(--ed-panel); border: 1px solid var(--ed-border); border-radius: 13px; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 12%); }
    .comi-admin-edit .ed-card-heading { display: flex; align-items: center; gap: 13px; padding: 24px 28px; border-bottom: 1px solid var(--ed-border); }
    .comi-admin-edit .ed-heading-icon { display: grid; place-items: center; width: 44px; height: 44px; flex-shrink: 0; border: 1px solid #31516a; border-radius: 11px; background: #17364a; color: var(--ed-accent); font-size: 21px; }
    .comi-admin-edit .ed-heading-copy { min-width: 0; flex: 1; }
    .comi-admin-edit .ed-card-title { margin: 0; color: var(--ed-text); font-size: 16px; font-weight: 600; }
    .comi-admin-edit .ed-card-note { margin: 4px 0 0; color: var(--ed-muted); font-size: 12px; overflow-wrap: anywhere; }
    .comi-admin-edit .ed-id { padding: 5px 9px; border: 1px solid #475569; border-radius: 7px; background: #172235; color: #b6c8df; font-size: 11px; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .comi-admin-edit .ed-form { margin: 0; }
    .comi-admin-edit .ed-body { padding: 28px; }
    .comi-admin-edit .ed-label { display: block; color: #e2e8f0; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
    .comi-admin-edit .ed-required { color: #fda4af; margin-left: 3px; }
    .comi-admin-edit .ed-field { position: relative; }
    .comi-admin-edit .ed-field > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 17px; pointer-events: none; }
    .comi-admin-edit .ed-input { display: block; width: 100%; min-height: 50px; padding: 12px 15px 12px 44px; border: 1px solid #475569; border-radius: 9px; background: var(--ed-bg); color: var(--ed-text); font-size: 14px; }
    .comi-admin-edit .ed-input::placeholder { color: #94a3b8; opacity: 1; }
    .comi-admin-edit .ed-input:focus { outline: 2px solid var(--ed-accent); outline-offset: 2px; border-color: var(--ed-accent); }
    .comi-admin-edit .ed-field:focus-within > i { color: var(--ed-accent); }
    .comi-admin-edit .ed-input[aria-invalid="true"] { border-color: #fb7185; }
    .comi-admin-edit .ed-help { margin: 10px 0 0; color: var(--ed-muted); font-size: 12px; }
    .comi-admin-edit .ed-error { display: flex; align-items: flex-start; gap: 7px; margin: 10px 0 0; color: #fda4af; font-size: 12px; }
    .comi-admin-edit .ed-footer { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap; padding: 20px 28px; border-top: 1px solid var(--ed-border); background: #1a2639; }
    .comi-admin-edit .ed-required-note { margin: 0; color: var(--ed-muted); font-size: 11px; }
    .comi-admin-edit .ed-actions { display: flex; align-items: center; gap: 10px; }
    .comi-admin-edit .ed-btn { display: inline-flex; justify-content: center; align-items: center; gap: 8px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; text-align: center; }
    .comi-admin-edit .ed-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-admin-edit .ed-btn-primary:hover { background: #0369a1; border-color: var(--ed-accent); }
    .comi-admin-edit .ed-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; }
    .comi-admin-edit .ed-btn-secondary:hover { background: #26364d; border-color: #94a3b8; }
    .comi-admin-edit :is(a, button):focus-visible { outline: 2px solid var(--ed-accent); outline-offset: 3px; }
    .comi-admin-edit .ed-alert { max-width: 780px; padding: 14px 16px; margin: 0 0 18px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; font-size: 13px; }
    .comi-admin-edit .ed-alert ul { margin: 8px 0 0; padding-left: 20px; }
    @media (max-width: 640px) {
        .comi-admin-edit { padding: 18px 12px; border-radius: 10px; }
        .comi-admin-edit .ed-card-heading, .comi-admin-edit .ed-body { padding: 20px 18px; }
        .comi-admin-edit .ed-card-heading { flex-wrap: wrap; }
        .comi-admin-edit .ed-footer { padding: 18px; align-items: stretch; flex-direction: column; }
        .comi-admin-edit .ed-actions { flex-wrap: wrap; }
        .comi-admin-edit .ed-actions .ed-btn { flex: 1 1 150px; }
    }
    .comi-admin-edit .ed-fields { display: grid; gap: 24px; }
    .comi-admin-edit .ed-subtitle strong { color: #e2e8f0; overflow-wrap: anywhere; }
</style>

<section class="comi-admin-edit" aria-labelledby="admin-edit-title">
    <a href="{{ route('empresas.index') }}" class="ed-back">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a empresas
    </a>
    <header class="ed-header">
        <p class="ed-eyebrow">COMICenter / Administración</p>
        <h1 class="ed-title" id="admin-edit-title">Editar administrador</h1>
        <p class="ed-subtitle">Actualiza al encargado principal de la empresa <strong>{{ $empresa->nombre }}</strong>.</p>
    </header>

    @if (session('error'))
        <div class="ed-alert" role="alert">{{ session('error') }}</div>
    @endif
    {{-- Los errores de los campos aparecen junto a ellos; aquí se muestran los demás. --}}
    @if (count(array_diff($errors->keys(), ['nombre_admin', 'correo_admin'])) > 0)
        <div class="ed-alert" role="alert">
            No se pudieron guardar los cambios. Revisa lo siguiente:
            <ul>
                @foreach ($errors->getMessages() as $campo => $mensajes)
                    @if (!in_array($campo, ['nombre_admin', 'correo_admin'], true))
                        @foreach ($mensajes as $mensaje)
                            <li>{{ $mensaje }}</li>
                        @endforeach
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ed-card">
        <div class="ed-card-heading">
            <span class="ed-heading-icon"><i class="bi bi-person-gear" aria-hidden="true"></i></span>
            <div class="ed-heading-copy">
                <h2 class="ed-card-title">Datos del administrador</h2>
                <p class="ed-card-note">{{ $empresa->nombre }}</p>
            </div>
            <span class="ed-id">Admin Cliente</span>
        </div>
        <form action="{{ route('empresas.admin.update', $empresa->id_empresa) }}" method="POST" class="ed-form">
            @csrf
            @method('PUT')
            <div class="ed-body">
                <div class="ed-fields">
                    <div>
                        <label for="nombre_admin" class="ed-label">
                            Nombre completo del administrador <span class="ed-required" aria-hidden="true">*</span>
                        </label>
                        <div class="ed-field">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <input type="text" name="nombre_admin" id="nombre_admin" class="ed-input"
                                value="{{ old('nombre_admin', $admin->name) }}"
                                placeholder="Ej. Carlos Martínez" autocomplete="name" required
                                @error('nombre_admin') aria-invalid="true" aria-describedby="nombre-admin-error" @enderror>
                        </div>
                        @error('nombre_admin')
                            <p class="ed-error" id="nombre-admin-error" role="alert">
                                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label for="correo_admin" class="ed-label">
                            Correo de acceso <span class="ed-required" aria-hidden="true">*</span>
                        </label>
                        <div class="ed-field">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input type="email" name="correo_admin" id="correo_admin" class="ed-input"
                                value="{{ old('correo_admin', $admin->email) }}"
                                placeholder="correo@empresa.com" autocomplete="email" required
                                aria-describedby="correo-admin-ayuda @error('correo_admin') correo-admin-error @enderror"
                                @error('correo_admin') aria-invalid="true" @enderror>
                        </div>
                        <p class="ed-help" id="correo-admin-ayuda">Correo que utilizará el administrador para iniciar sesión en el CRM.</p>
                        @error('correo_admin')
                            <p class="ed-error" id="correo-admin-error" role="alert">
                                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="ed-footer">
                <p class="ed-required-note"><span class="ed-required" aria-hidden="true">*</span> Campos obligatorios</p>
                <div class="ed-actions">
                    <a href="{{ route('empresas.index') }}" class="ed-btn ed-btn-secondary">Cancelar</a>
                    <button type="submit" class="ed-btn ed-btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection