{{-- resources/views/empresas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Empresas - CRM')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Estilos exclusivos de esta vista. */
    .comi-empresas {
        --ce-bg: #0f172a;
        --ce-panel: #1e293b;
        --ce-border: #334155;
        --ce-text: #f1f5f9;
        --ce-muted: #a5b4c8;
        --ce-accent: #38bdf8;
        min-width: 0;
        padding: clamp(16px, 3vw, 36px);
        background: var(--ce-bg);
        color: var(--ce-text);
        border-radius: 16px;
        font-family: inherit;
        line-height: 1.5;
        color-scheme: dark;
    }
    .comi-empresas, .comi-empresas *, .comi-empresas *::before, .comi-empresas *::after { box-sizing: border-box; }
    .comi-empresas a { text-decoration: none; }
    .comi-empresas button { font: inherit; }
    .comi-empresas .ce-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 26px; flex-wrap: wrap; }
    .comi-empresas .ce-eyebrow { margin: 0 0 10px; color: var(--ce-accent); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-empresas .ce-title { margin: 0; color: var(--ce-text); font-size: clamp(26px, 3vw, 34px); font-weight: 700; letter-spacing: -.035em; line-height: 1.2; }
    .comi-empresas .ce-subtitle { margin: 10px 0 0; color: var(--ce-muted); font-size: 14px; }
    .comi-empresas .ce-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background-color .15s, border-color .15s; }
    .comi-empresas .ce-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-empresas .ce-btn-primary:hover { background: #0369a1; border-color: #38bdf8; }
    .comi-empresas .ce-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; }
    .comi-empresas .ce-btn-secondary:hover { background: #26364d; border-color: #38bdf8; }
    .comi-empresas :is(a, button, [tabindex]):focus-visible { outline: 2px solid var(--ce-accent); outline-offset: 3px; }
    .comi-empresas .ce-card { min-width: 0; background: var(--ce-panel); border: 1px solid var(--ce-border); border-radius: 13px; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 12%); }
    .comi-empresas .ce-card-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 22px 24px; }
    .comi-empresas .ce-heading-group { display: flex; align-items: center; gap: 12px; }
    .comi-empresas .ce-heading-icon { width: 42px; height: 42px; display: grid; place-items: center; border: 1px solid #31516a; border-radius: 10px; background: #17364a; color: var(--ce-accent); font-size: 20px; flex-shrink: 0; }
    .comi-empresas .ce-card-title { color: var(--ce-text); font-size: 16px; font-weight: 600; margin: 0; }
    .comi-empresas .ce-card-note { color: var(--ce-muted); font-size: 12px; margin: 3px 0 0; }
    .comi-empresas .ce-count { padding: 6px 10px; border: 1px solid #475569; border-radius: 7px; background: #172235; color: #cbd5e1; font-size: 12px; white-space: nowrap; }
    .comi-empresas .ce-count strong { color: #7dd3fc; font-weight: 600; }
    .comi-empresas .ce-alert { padding: 12px 16px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; margin: 0 0 18px; font-size: 13px; }
    .comi-empresas .ce-alert-success { background: #143f39; border-color: #286556; color: #a7f3d0; }
    .comi-empresas .ce-table-scroll { overflow-x: auto; scrollbar-width: thin; scrollbar-color: #52647f #0f172a; }
    .comi-empresas .ce-table { width: 100%; min-width: 800px; border-collapse: collapse; text-align: left; }
    .comi-empresas .ce-table th { background: #172235; padding: 14px 24px; border-top: 1px solid var(--ce-border); border-bottom: 1px solid var(--ce-border); color: #aebed2; font-size: 10px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; white-space: nowrap; }
    .comi-empresas .ce-table td { padding: 20px 24px; border-bottom: 1px solid #2d3b50; color: #d6e0ed; font-size: 13px; vertical-align: middle; }
    .comi-empresas .ce-table tbody tr:hover { background: #243248; }
    .comi-empresas .ce-table tbody tr:last-child td { border-bottom: 0; }
    .comi-empresas .ce-id { color: #91a4bd; font-size: 12px; font-variant-numeric: tabular-nums; }
    .comi-empresas .ce-company { display: flex; gap: 12px; align-items: center; min-width: 200px; }
    .comi-empresas .ce-avatar { width: 40px; height: 40px; flex-shrink: 0; display: grid; place-items: center; border-radius: 10px; background: #153f54; border: 1px solid #285569; color: #7dd3fc; font-size: 13px; font-weight: 700; }
    .comi-empresas .ce-name { color: #f1f5f9; font-weight: 600; max-width: 340px; overflow-wrap: anywhere; }
    .comi-empresas .ce-slug { display: inline-block; max-width: 260px; padding: 5px 9px; border-radius: 6px; border: 1px solid #35465d; background: #162235; color: #b6c8df; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 12px; overflow-wrap: anywhere; }
    .comi-empresas .ce-badge { display: inline-flex; align-items: center; gap: 7px; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .comi-empresas .ce-badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
    .comi-empresas .ce-active { background: #143f39; color: #6ee7b7; }
    .comi-empresas .ce-inactive { background: #482635; color: #fda4af; }
    .comi-empresas .ce-actions-title { text-align: center; }
    .comi-empresas .ce-actions { display: flex; align-items: center; justify-content: center; gap: 8px; }
    .comi-empresas .ce-actions form { margin: 0; }
    .comi-empresas .ce-action { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 38px; padding: 8px 11px; border: 1px solid #475569; border-radius: 8px; background: #172235; color: #cbd5e1; font-size: 12px; font-weight: 500; cursor: pointer; white-space: nowrap; }
    .comi-empresas .ce-edit:hover { background: #153f54; color: #7dd3fc; border-color: #38bdf8; }
    .comi-empresas .ce-admin { background: #17364a; color: #7dd3fc; border-color: #31516a; }
    .comi-empresas .ce-admin:hover { background: #164e63; color: #e0f2fe; border-color: #38bdf8; }
    .comi-empresas .ce-delete:hover { background: #482635; color: #fda4af; border-color: #fb7185; }
    .comi-empresas .ce-footer { border-top: 1px solid var(--ce-border); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
    .comi-empresas .ce-footer p { margin: 0; color: var(--ce-muted); font-size: 12px; }
    .comi-empresas .ce-pagination { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .comi-empresas .ce-pagination .ce-btn { min-height: 38px; padding: 7px 12px; font-size: 12px; }
    .comi-empresas .ce-disabled { opacity: .45; cursor: default; }
    .comi-empresas .ce-page-current { padding: 7px 10px; color: #7dd3fc; font-size: 12px; }
    .comi-empresas .ce-empty { padding: 55px 24px; text-align: center; border-top: 1px solid var(--ce-border); }
    .comi-empresas .ce-empty > i { font-size: 36px; color: var(--ce-accent); }
    .comi-empresas .ce-empty h3 { margin: 14px 0 7px; color: var(--ce-text); font-size: 18px; }
    .comi-empresas .ce-empty p { margin: 0 auto 22px; max-width: 420px; color: var(--ce-muted); font-size: 13px; }
    @media (max-width: 640px) {
        .comi-empresas { padding: 18px 12px; border-radius: 10px; }
        .comi-empresas .ce-header { align-items: stretch; gap: 18px; }
        .comi-empresas .ce-header > .ce-btn { width: 100%; }
        .comi-empresas .ce-card-heading, .comi-empresas .ce-footer { padding: 18px 16px; }
        .comi-empresas .ce-footer { align-items: flex-start; flex-direction: column; }
    }
</style>

<section class="comi-empresas" aria-labelledby="empresas-title">
    <header class="ce-header">
        <div>
            <p class="ce-eyebrow">COMICenter / Administración</p>
            <h1 class="ce-title" id="empresas-title">Empresas</h1>
            <p class="ce-subtitle">Administra las empresas registradas y consulta su estado.</p>
        </div>
        <a href="{{ route('empresas.create') }}" class="ce-btn ce-btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva empresa
        </a>
    </header>

    @if (session('success'))
        <div role="status" class="ce-alert ce-alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="ce-alert">{{ session('error') }}</div>
    @endif

    <div class="ce-card">
        <div class="ce-card-heading">
            <div class="ce-heading-group">
                <span class="ce-heading-icon"><i class="bi bi-buildings" aria-hidden="true"></i></span>
                <div>
                    <h2 class="ce-card-title">Directorio de empresas</h2>
                    <p class="ce-card-note">Organizaciones que forman parte de la plataforma.</p>
                </div>
            </div>
            <span class="ce-count"><strong>{{ $empresas->count() }}</strong> {{ $empresas->count() === 1 ? 'empresa en esta página' : 'empresas en esta página' }}</span>
        </div>

        @if ($empresas->count() > 0)
            <div class="ce-table-scroll" tabindex="0" role="region" aria-label="Tabla de empresas; desplaza horizontalmente para ver todas las columnas">
                <table class="ce-table" aria-label="Directorio de empresas">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre de la empresa</th>
                            <th scope="col">Slug (identificador)</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="ce-actions-title">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            @php
                                $iniciales = mb_strtoupper(mb_substr(trim($empresa->nombre ?? ''), 0, 2));
                            @endphp
                            <tr>
                                <td><span class="ce-id">#{{ $empresa->id_empresa }}</span></td>
                                <td>
                                    <div class="ce-company">
                                        <span class="ce-avatar" aria-hidden="true">{{ $iniciales ?: '?' }}</span>
                                        <span class="ce-name">{{ $empresa->nombre }}</span>
                                    </div>
                                </td>
                                <td><span class="ce-slug">{{ $empresa->slug }}</span></td>
                                <td>
                                    @if ($empresa->estado)
                                        <span class="ce-badge ce-active">Activa</span>
                                    @else
                                        <span class="ce-badge ce-inactive">Inactiva</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ce-actions">
                                        <a href="{{ route('empresas.edit', $empresa->id_empresa) }}" class="ce-action ce-edit"
                                            title="Editar empresa" aria-label="Editar empresa {{ $empresa->nombre }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i> Empresa
                                        </a>
                                        <a href="{{ route('empresas.admin.edit', $empresa->id_empresa) }}" class="ce-action ce-admin"
                                            title="Editar administrador" aria-label="Editar administrador de {{ $empresa->nombre }}">
                                            <i class="bi bi-person-gear" aria-hidden="true"></i> Admin
                                        </a>
                                        <form action="{{ route('empresas.destroy', $empresa->id_empresa) }}" method="POST"
                                            onsubmit="return confirm('¿Estás seguro de eliminar esta empresa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ce-action ce-delete" aria-label="Eliminar empresa {{ $empresa->nombre }}">
                                                <i class="bi bi-trash" aria-hidden="true"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="ce-empty">
                <i class="bi bi-buildings" aria-hidden="true"></i>
                @if ($empresas->onFirstPage())
                    <h3>Aún no hay empresas registradas</h3>
                    <p>Registra la primera empresa para comenzar a administrarla desde COMICenter.</p>
                    <a href="{{ route('empresas.create') }}" class="ce-btn ce-btn-primary">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva empresa
                    </a>
                @else
                    <h3>No hay empresas en esta página</h3>
                    <p>Regresa a la primera página para consultar el directorio.</p>
                    <a href="{{ $empresas->url(1) }}" class="ce-btn ce-btn-secondary">Volver al directorio</a>
                @endif
            </div>
        @endif

        <footer class="ce-footer">
            <p>Directorio de empresas · COMICenter</p>
            @if ($empresas->hasPages())
                <nav class="ce-pagination" aria-label="Paginación de empresas">
                    @if ($empresas->onFirstPage())
                        <span class="ce-btn ce-btn-secondary ce-disabled" aria-disabled="true">Anterior</span>
                    @else
                        <a class="ce-btn ce-btn-secondary" href="{{ $empresas->previousPageUrl() }}" rel="prev">Anterior</a>
                    @endif
                    <span class="ce-page-current" aria-current="page">Página {{ $empresas->currentPage() }}</span>
                    @if ($empresas->hasMorePages())
                        <a class="ce-btn ce-btn-secondary" href="{{ $empresas->nextPageUrl() }}" rel="next">Siguiente</a>
                    @else
                        <span class="ce-btn ce-btn-secondary ce-disabled" aria-disabled="true">Siguiente</span>
                    @endif
                </nav>
            @endif
        </footer>
    </div>
</section>
@endsection