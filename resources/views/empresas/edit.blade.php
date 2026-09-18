{{-- resources/views/empresas/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar empresa - CRM')
@section('header-title', 'Empresas')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Estilos exclusivos de esta vista. */
    .comi-empresa-edit {
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
    .comi-empresa-edit, .comi-empresa-edit *, .comi-empresa-edit *::before, .comi-empresa-edit *::after { box-sizing: border-box; }
    .comi-empresa-edit a { text-decoration: none; }
    .comi-empresa-edit button, .comi-empresa-edit input { font: inherit; }
    .comi-empresa-edit .ed-back { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; color: #b6c8df; font-size: 13px; margin-bottom: 20px; }
    .comi-empresa-edit .ed-back:hover { color: var(--ed-accent); }
    .comi-empresa-edit .ed-header { margin-bottom: 28px; }
    .comi-empresa-edit .ed-eyebrow { margin: 0 0 10px; color: var(--ed-accent); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-empresa-edit .ed-title { margin: 0; color: var(--ed-text); font-size: clamp(26px, 3vw, 34px); font-weight: 700; line-height: 1.2; letter-spacing: -.035em; }
    .comi-empresa-edit .ed-subtitle { margin: 10px 0 0; color: var(--ed-muted); font-size: 14px; }
    .comi-empresa-edit .ed-card { max-width: 780px; background: var(--ed-panel); border: 1px solid var(--ed-border); border-radius: 13px; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 12%); }
    .comi-empresa-edit .ed-card-heading { display: flex; align-items: center; gap: 13px; padding: 24px 28px; border-bottom: 1px solid var(--ed-border); }
    .comi-empresa-edit .ed-heading-icon { display: grid; place-items: center; width: 44px; height: 44px; flex-shrink: 0; border: 1px solid #31516a; border-radius: 11px; background: #17364a; color: var(--ed-accent); font-size: 21px; }
    .comi-empresa-edit .ed-heading-copy { min-width: 0; flex: 1; }
    .comi-empresa-edit .ed-card-title { margin: 0; color: var(--ed-text); font-size: 16px; font-weight: 600; }
    .comi-empresa-edit .ed-card-note { margin: 4px 0 0; color: var(--ed-muted); font-size: 12px; overflow-wrap: anywhere; }
    .comi-empresa-edit .ed-id { padding: 5px 9px; border: 1px solid #475569; border-radius: 7px; background: #172235; color: #b6c8df; font-size: 11px; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .comi-empresa-edit .ed-form { margin: 0; }
    .comi-empresa-edit .ed-body { padding: 28px; }
    .comi-empresa-edit .ed-label { display: block; color: #e2e8f0; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
    .comi-empresa-edit .ed-required { color: #fda4af; margin-left: 3px; }
    .comi-empresa-edit .ed-field { position: relative; }
    .comi-empresa-edit .ed-field > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 17px; pointer-events: none; }
    .comi-empresa-edit .ed-input { display: block; width: 100%; min-height: 50px; padding: 12px 15px 12px 44px; border: 1px solid #475569; border-radius: 9px; background: var(--ed-bg); color: var(--ed-text); font-size: 14px; }
    .comi-empresa-edit .ed-input::placeholder { color: #94a3b8; opacity: 1; }
    .comi-empresa-edit .ed-input:focus { outline: 2px solid var(--ed-accent); outline-offset: 2px; border-color: var(--ed-accent); }
    .comi-empresa-edit .ed-field:focus-within > i { color: var(--ed-accent); }
    .comi-empresa-edit .ed-input[aria-invalid="true"] { border-color: #fb7185; }
    .comi-empresa-edit .ed-help { margin: 10px 0 0; color: var(--ed-muted); font-size: 12px; }
    .comi-empresa-edit .ed-error { display: flex; align-items: flex-start; gap: 7px; margin: 10px 0 0; color: #fda4af; font-size: 12px; }
    .comi-empresa-edit .ed-footer { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap; padding: 20px 28px; border-top: 1px solid var(--ed-border); background: #1a2639; }
    .comi-empresa-edit .ed-required-note { margin: 0; color: var(--ed-muted); font-size: 11px; }
    .comi-empresa-edit .ed-actions { display: flex; align-items: center; gap: 10px; }
    .comi-empresa-edit .ed-btn { display: inline-flex; justify-content: center; align-items: center; gap: 8px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; text-align: center; }
    .comi-empresa-edit .ed-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-empresa-edit .ed-btn-primary:hover { background: #0369a1; border-color: var(--ed-accent); }
    .comi-empresa-edit .ed-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; }
    .comi-empresa-edit .ed-btn-secondary:hover { background: #26364d; border-color: #94a3b8; }
    .comi-empresa-edit :is(a, button):focus-visible { outline: 2px solid var(--ed-accent); outline-offset: 3px; }
    .comi-empresa-edit .ed-alert { max-width: 780px; padding: 14px 16px; margin: 0 0 18px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; font-size: 13px; }
    .comi-empresa-edit .ed-alert ul { margin: 8px 0 0; padding-left: 20px; }
    @media (max-width: 640px) {
        .comi-empresa-edit { padding: 18px 12px; border-radius: 10px; }
        .comi-empresa-edit .ed-card-heading, .comi-empresa-edit .ed-body { padding: 20px 18px; }
        .comi-empresa-edit .ed-card-heading { flex-wrap: wrap; }
        .comi-empresa-edit .ed-footer { padding: 18px; align-items: stretch; flex-direction: column; }
        .comi-empresa-edit .ed-actions { flex-wrap: wrap; }
        .comi-empresa-edit .ed-actions .ed-btn { flex: 1 1 150px; }
    }
</style>

<section class="comi-empresa-edit" aria-labelledby="empresa-edit-title">
    <a href="{{ route('empresas.index') }}" class="ed-back">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a empresas
    </a>
    <header class="ed-header">
        <p class="ed-eyebrow">COMICenter / Administración</p>
        <h1 class="ed-title" id="empresa-edit-title">Editar inquilino</h1>
        <p class="ed-subtitle">Modifica los datos de la organización.</p>
    </header>

    @if (session('error'))
        <div class="ed-alert" role="alert">{{ session('error') }}</div>
    @endif
    {{-- El error de nombre aparece junto al campo; aquí se muestran otros errores. --}}
    @if (count(array_diff($errors->keys(), ['nombre'])) > 0)
        <div class="ed-alert" role="alert">
            No se pudo actualizar la empresa. Revisa lo siguiente:
            <ul>
                @foreach ($errors->getMessages() as $campo => $mensajes)
                    @if ($campo !== 'nombre')
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
            <span class="ed-heading-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
            <div class="ed-heading-copy">
                <h2 class="ed-card-title">Información de la empresa</h2>
                <p class="ed-card-note">{{ $empresa->nombre }}</p>
            </div>
            <span class="ed-id">ID #{{ $empresa->id_empresa }}</span>
        </div>

        <form action="{{ route('empresas.update', $empresa->id_empresa) }}" method="POST" class="ed-form">
            @csrf
            @method('PUT')
            <div class="ed-body">
                <label for="nombre" class="ed-label">
                    Nombre comercial o razón social <span class="ed-required" aria-hidden="true">*</span>
                </label>
                <div class="ed-field">
                    <i class="bi bi-building" aria-hidden="true"></i>
                    <input type="text" name="nombre" id="nombre" class="ed-input"
                        value="{{ old('nombre', $empresa->nombre) }}"
                        placeholder="Ej. Nova Solutions SA de CV" autocomplete="organization" required
                        aria-describedby="nombre-ayuda @error('nombre') nombre-error @enderror"
                        @error('nombre') aria-invalid="true" @enderror>
                </div>
                <p class="ed-help" id="nombre-ayuda">Revisa el nombre de la organización antes de guardar los cambios.</p>
                @error('nombre')
                    <p class="ed-error" id="nombre-error" role="alert">
                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>
            <div class="ed-footer">
                <p class="ed-required-note"><span class="ed-required" aria-hidden="true">*</span> Campo obligatorio</p>
                <div class="ed-actions">
                    <a href="{{ route('empresas.index') }}" class="ed-btn ed-btn-secondary">Cancelar</a>
                    <button type="submit" class="ed-btn ed-btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i> Actualizar empresa
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
