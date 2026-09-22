@extends('layouts.app')

@section('title', 'Dashboard - COMICenter')
@section('header-title', 'Dashboard')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .comi-dashboard { --db-muted: #a5b4c8; --db-border: #334155; padding: clamp(18px, 3vw, 36px); background: #0f172a; color: #f1f5f9; border-radius: 16px; min-width: 0; font-family: inherit; line-height: 1.5; color-scheme: dark; }
    .comi-dashboard, .comi-dashboard *, .comi-dashboard *::before { box-sizing: border-box; }
    .comi-dashboard .db-header { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 28px; }
    .comi-dashboard .db-eyebrow { margin: 0 0 10px; color: #38bdf8; font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-dashboard .db-title { margin: 0; color: #f1f5f9; font-size: clamp(26px, 3vw, 34px); font-weight: 700; letter-spacing: -.035em; line-height: 1.2; overflow-wrap: anywhere; }
    .comi-dashboard .db-description { margin: 10px 0 0; color: var(--db-muted); font-size: 14px; max-width: 630px; }
    .comi-dashboard .db-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 44px; padding: 10px 17px; border: 1px solid #0284c7; border-radius: 9px; background: #0284c7; color: white; font-size: 13px; font-weight: 600; text-decoration: none; text-align: center; }
    .comi-dashboard .db-btn:hover { background: #0369a1; border-color: #38bdf8; }
    .comi-dashboard .db-link { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; color: #7dd3fc; font-size: 12px; font-weight: 600; text-decoration: none; }
    .comi-dashboard .db-link:hover { color: #bae6fd; text-decoration: underline; text-underline-offset: 4px; }
    .comi-dashboard :is(a, [tabindex]):focus-visible { outline: 2px solid #38bdf8; outline-offset: 3px; }
    .comi-dashboard .db-alert { display: flex; align-items: flex-start; gap: 12px; padding: 16px; margin-bottom: 24px; border: 1px solid #9f4050; border-radius: 10px; background: #422333; color: #fecdd3; font-size: 13px; }
    .comi-dashboard .db-alert p { margin: 0; }
    .comi-dashboard .db-alert > i { font-size: 18px; flex-shrink: 0; }
    .comi-dashboard .db-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-bottom: 28px; }
    .comi-dashboard .db-kpi { min-width: 0; padding: 24px; border: 1px solid var(--db-border); border-radius: 13px; background: #1e293b; box-shadow: 0 8px 24px rgb(0 0 0 / 8%); }
    .comi-dashboard .db-kpi-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .comi-dashboard .db-kpi-label { margin: 0; font-size: 13px; font-weight: 500; color: #cbd5e1; }
    .comi-dashboard .db-icon { display: grid; place-items: center; width: 42px; height: 42px; flex-shrink: 0; border: 1px solid #31516a; border-radius: 11px; background: #17364a; color: #7dd3fc; font-size: 20px; }
    .comi-dashboard .db-icon-green { background: #143f39; border-color: #286556; color: #6ee7b7; }
    .comi-dashboard .db-icon-violet { background: #393052; border-color: #56456d; color: #d8b4fe; }
    .comi-dashboard .db-value { margin: 17px 0 8px; color: #f1f5f9; font-size: clamp(30px, 3vw, 40px); line-height: 1.15; font-weight: 700; letter-spacing: -.04em; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .comi-dashboard .db-value-green { color: #6ee7b7; }
    .comi-dashboard .db-value-cyan { color: #7dd3fc; }
    .comi-dashboard .db-kpi-note { margin: 0; color: var(--db-muted); font-size: 12px; }
    .comi-dashboard .db-panel { border: 1px solid var(--db-border); border-radius: 13px; background: #1e293b; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 10%); }
    .comi-dashboard .db-panel-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding: 22px 24px; }
    .comi-dashboard .db-panel-title { margin: 0; color: #f1f5f9; font-size: 16px; font-weight: 600; }
    .comi-dashboard .db-panel-note { margin: 5px 0 0; color: var(--db-muted); font-size: 12px; }
    .comi-dashboard .db-table-scroll { overflow-x: auto; scrollbar-width: thin; scrollbar-color: #52647f #0f172a; }
    .comi-dashboard .db-table { width: 100%; min-width: 500px; text-align: left; border-collapse: collapse; }
    .comi-dashboard .db-table th { padding: 14px 24px; background: #172235; border-top: 1px solid var(--db-border); border-bottom: 1px solid var(--db-border); color: #aebed2; font-size: 10px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; }
    .comi-dashboard .db-table td { padding: 19px 24px; border-bottom: 1px solid #2d3b50; color: #d6e0ed; font-size: 13px; }
    .comi-dashboard .db-table tbody tr:last-child td { border-bottom: 0; }
    .comi-dashboard .db-table tbody tr:hover { background: #243248; }
    .comi-dashboard .db-company { display: flex; align-items: center; gap: 12px; }
    .comi-dashboard .db-avatar { display: grid; place-items: center; width: 36px; height: 36px; flex-shrink: 0; border: 1px solid #285569; border-radius: 10px; background: #153f54; color: #7dd3fc; font-size: 17px; }
    .comi-dashboard .db-company-name { font-weight: 600; color: #f1f5f9; overflow-wrap: anywhere; }
    .comi-dashboard .db-date { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .comi-dashboard .db-date i { margin-right: 7px; color: #94a3b8; }
    .comi-dashboard .db-empty { padding: 40px 20px; text-align: center; }
    .comi-dashboard .db-empty > i { color: #38bdf8; font-size: 32px; }
    .comi-dashboard .db-empty h3 { margin: 12px 0 7px; color: #f1f5f9; font-size: 16px; }
    .comi-dashboard .db-empty p { margin: 0; color: var(--db-muted); font-size: 13px; }
    .comi-dashboard .db-welcome { padding: clamp(24px, 4vw, 44px); border: 1px solid #31516a; border-radius: 13px; background: linear-gradient(120deg, #1e293b, #142b40); }
    .comi-dashboard .db-welcome > .db-icon { margin-bottom: 24px; width: 52px; height: 52px; font-size: 25px; }
    .comi-dashboard .db-welcome-note { display: flex; align-items: flex-start; gap: 12px; padding-top: 24px; margin-top: 28px; border-top: 1px solid #334b62; color: #b6c8df; font-size: 13px; }
    .comi-dashboard .db-welcome-note i { color: #38bdf8; }
    .comi-dashboard .db-welcome-note p { margin: 0; }
    @media (max-width: 1000px) { .comi-dashboard .db-kpis { gap: 12px; } .comi-dashboard .db-kpi { padding: 18px; } }
    @media (max-width: 760px) {
        .comi-dashboard { padding: 18px 12px; border-radius: 10px; }
        .comi-dashboard .db-kpis { grid-template-columns: 1fr; gap: 14px; }
        .comi-dashboard .db-header > .db-btn { width: 100%; }
        .comi-dashboard .db-panel-header { padding: 18px; }
    }
</style>

<div class="comi-dashboard">
    @if (session('error'))
        <div class="db-alert" role="alert">
            <i class="bi bi-shield-exclamation" aria-hidden="true"></i>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if (Auth::check() && (int) Auth::user()->id_rol === 1)
        <header class="db-header">
            <div>
                <p class="db-eyebrow">COMICenter / Plataforma</p>
                <h1 class="db-title">Dashboard global</h1>
                <p class="db-description">Resumen de actividad y estado general del CRM.</p>
            </div>
            @if (Route::has('empresas.create'))
                <a class="db-btn" href="{{ route('empresas.create') }}">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo inquilino
                </a>
            @endif
        </header>

        {{-- Se utilizan los mismos indicadores enviados por tu controlador. --}}
        <section class="db-kpis" aria-label="Indicadores globales">
            <article class="db-kpi" aria-labelledby="kpi-inquilinos">
                <div class="db-kpi-top">
                    <h2 class="db-kpi-label" id="kpi-inquilinos">Total de inquilinos</h2>
                    <span class="db-icon"><i class="bi bi-buildings" aria-hidden="true"></i></span>
                </div>
                <p class="db-value">{{ number_format($totalEmpresas, 0, '.', ',') }}</p>
                <p class="db-kpi-note">Inquilinos registrados en la plataforma</p>
            </article>
            <article class="db-kpi" aria-labelledby="kpi-activas">
                <div class="db-kpi-top">
                    <h2 class="db-kpi-label" id="kpi-activas">Empresas activas</h2>
                    <span class="db-icon db-icon-green"><i class="bi bi-building-check" aria-hidden="true"></i></span>
                </div>
                <p class="db-value db-value-green">{{ number_format($empresasActivas, 0, '.', ',') }}</p>
                <p class="db-kpi-note">Empresas con estado activo</p>
            </article>
            <article class="db-kpi" aria-labelledby="kpi-leads">
                <div class="db-kpi-top">
                    <h2 class="db-kpi-label" id="kpi-leads">Volumen total de leads</h2>
                    <span class="db-icon db-icon-violet"><i class="bi bi-people" aria-hidden="true"></i></span>
                </div>
                <p class="db-value db-value-cyan">{{ number_format($totalLeads, 0, '.', ',') }}</p>
                <p class="db-kpi-note">Leads registrados en el CRM</p>
            </article>
        </section>

        <section class="db-panel" aria-labelledby="ultimas-altas-title">
            <header class="db-panel-header">
                <div>
                    <h2 class="db-panel-title" id="ultimas-altas-title">Últimas altas en el sistema</h2>
                    <p class="db-panel-note">Consulta las empresas registradas recientemente.</p>
                </div>
                @if (Route::has('empresas.index'))
                    <a class="db-link" href="{{ route('empresas.index') }}">Ver empresas <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endif
            </header>
            <div class="db-table-scroll" tabindex="0" role="region" aria-label="Últimas empresas; desplaza horizontalmente para ver todas las columnas">
                <table class="db-table" aria-labelledby="ultimas-altas-title">
                    <thead><tr><th scope="col">Empresa</th><th scope="col">Fecha de registro</th></tr></thead>
                    <tbody>
                        @forelse ($ultimasEmpresas as $empresa)
                            <tr>
                                <td>
                                    <div class="db-company">
                                        <span class="db-avatar"><i class="bi bi-building" aria-hidden="true"></i></span>
                                        <span class="db-company-name">{{ $empresa->nombre }}</span>
                                    </div>
                                </td>
                                <td class="db-date">
                                    @if ($empresa->created_at)
                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                        <time datetime="{{ $empresa->created_at->format('Y-m-d') }}">{{ $empresa->created_at->format('d/m/Y') }}</time>
                                    @else
                                        Sin registro
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2">
                                <div class="db-empty">
                                    <i class="bi bi-buildings" aria-hidden="true"></i>
                                    <h3>No hay altas para mostrar</h3>
                                    <p>Las nuevas empresas aparecerán aquí cuando se registren.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="db-welcome" aria-labelledby="bienvenida-title">
            <span class="db-icon"><i class="bi bi-person-workspace" aria-hidden="true"></i></span>
            <p class="db-eyebrow">COMICenter / Tu espacio de trabajo</p>
            <h1 class="db-title" id="bienvenida-title">Bienvenido, {{ Auth::user()->name ?? 'Usuario' }}</h1>
            <p class="db-description">Consulta y gestiona tu trabajo desde los módulos disponibles para tu cuenta.</p>
            <div class="db-welcome-note">
                <i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>
                <p>Selecciona una sección en el menú principal para comenzar.</p>
            </div>
        </section>
    @endif
</div>
@endsection