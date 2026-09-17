{{-- resources/views/clientes/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Clientes - CRM')

@section('content')
@php
    $buscar = $buscar ?? '';
    $clientes->appends(['buscar' => $buscar]);
    $estadosLead = [
        1 => ['texto' => 'Nuevo', 'clase' => 'nuevo'],
        2 => ['texto' => 'Contactado', 'clase' => 'contactado'],
        3 => ['texto' => 'Interesado', 'clase' => 'interesado'],
        4 => ['texto' => 'Cliente', 'clase' => 'cliente'],
    ];
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Estilos limitados a esta vista para evitar conflictos con layouts.app. */
    .comi-clientes {
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
    .comi-clientes *, .comi-clientes *::before, .comi-clientes *::after { box-sizing: border-box; }
    .comi-clientes a { text-decoration: none; }
    .comi-clientes button, .comi-clientes input { font: inherit; }
    .comi-clientes .cc-eyebrow { margin: 0 0 10px; color: var(--cc-accent); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-clientes .cc-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 26px; flex-wrap: wrap; }
    .comi-clientes .cc-title { color: var(--cc-text); margin: 0; font-size: clamp(26px, 3vw, 34px); font-weight: 700; letter-spacing: -.035em; line-height: 1.2; }
    .comi-clientes .cc-subtitle { margin: 10px 0 0; color: var(--cc-muted); font-size: 14px; }
    .comi-clientes .cc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background-color .15s, border-color .15s; }
    .comi-clientes .cc-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-clientes .cc-btn-primary:hover { background: #0369a1; border-color: #38bdf8; }
    .comi-clientes .cc-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; }
    .comi-clientes .cc-btn-secondary:hover { background: #26364d; border-color: #38bdf8; }
    .comi-clientes .cc-card { min-width: 0; background: var(--cc-panel); border: 1px solid var(--cc-border); border-radius: 13px; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 12%); }
    .comi-clientes .cc-card-heading { display: flex; align-items: center; gap: 12px; padding: 22px 24px 0; }
    .comi-clientes .cc-heading-icon { width: 40px; height: 40px; display: grid; place-items: center; border: 1px solid #31516a; border-radius: 10px; background: #17364a; color: var(--cc-accent); font-size: 19px; flex-shrink: 0; }
    .comi-clientes .cc-card-title { color: var(--cc-text); font-size: 16px; font-weight: 600; margin: 0; }
    .comi-clientes .cc-card-note { color: var(--cc-muted); font-size: 12px; margin: 3px 0 0; }
    .comi-clientes .cc-toolbar { padding: 22px 24px; }
    .comi-clientes .cc-search-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin: 0; }
    .comi-clientes .cc-search { position: relative; flex: 1 1 260px; max-width: 470px; }
    .comi-clientes .cc-search > i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--cc-muted); pointer-events: none; }
    .comi-clientes .cc-input { width: 100%; min-height: 44px; padding: 10px 14px 10px 42px; border: 1px solid #475569; border-radius: 9px; background: var(--cc-bg); color: var(--cc-text); font-size: 13px; }
    .comi-clientes .cc-input::placeholder { color: #94a3b8; opacity: 1; }
    .comi-clientes :is(a, button, input, [tabindex]):focus-visible { outline: 2px solid var(--cc-accent); outline-offset: 3px; }
    .comi-clientes .cc-clear { color: #cbd5e1; font-size: 13px; padding: 10px; text-decoration: underline; text-underline-offset: 4px; }
    .comi-clientes .cc-filter-note { margin: 13px 0 0; color: var(--cc-muted); font-size: 13px; overflow-wrap: anywhere; }
    .comi-clientes .cc-filter-note strong { color: var(--cc-accent); }
    .comi-clientes .cc-alert { padding: 12px 16px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; margin: 0 0 18px; font-size: 13px; }
    .comi-clientes .cc-error { margin: 10px 0 0; color: #fda4af; font-size: 13px; }
    .comi-clientes .cc-table-scroll { overflow-x: auto; scrollbar-width: thin; scrollbar-color: #52647f #0f172a; }
    .comi-clientes .cc-table { width: 100%; min-width: 1320px; border-collapse: collapse; text-align: left; }
    .comi-clientes .cc-table th { background: #172235; padding: 14px 18px; border-top: 1px solid var(--cc-border); border-bottom: 1px solid var(--cc-border); color: #aebed2; font-size: 10px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; white-space: nowrap; }
    .comi-clientes .cc-table td { padding: 19px 18px; border-bottom: 1px solid #2d3b50; color: #d6e0ed; font-size: 13px; vertical-align: middle; }
    .comi-clientes .cc-table tbody tr:hover { background: #243248; }
    .comi-clientes .cc-table tbody tr:last-child td { border-bottom: 0; }
    .comi-clientes .cc-id { color: #91a4bd; font-size: 12px; font-variant-numeric: tabular-nums; }
    .comi-clientes .cc-person { display: flex; gap: 11px; align-items: center; min-width: 185px; }
    .comi-clientes .cc-avatar { width: 36px; height: 36px; flex-shrink: 0; display: grid; place-items: center; border-radius: 10px; background: #153f54; border: 1px solid #285569; color: #7dd3fc; font-size: 12px; font-weight: 700; }
    .comi-clientes .cc-name { color: #f1f5f9; font-weight: 600; overflow-wrap: anywhere; }
    .comi-clientes .cc-wrap { max-width: 240px; overflow-wrap: anywhere; }
    .comi-clientes .cc-nowrap { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .comi-clientes .cc-badge { display: inline-flex; align-items: center; gap: 7px; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; white-space: nowrap; background: #334155; color: #e2e8f0; }
    .comi-clientes .cc-badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
    .comi-clientes .cc-badge-nuevo { background: #123e59; color: #7dd3fc; }
    .comi-clientes .cc-badge-contactado { background: #493c23; color: #fcd34d; }
    .comi-clientes .cc-badge-interesado { background: #393052; color: #d8b4fe; }
    .comi-clientes .cc-badge-cliente { background: #143f39; color: #6ee7b7; }
    .comi-clientes .cc-actions { display: flex; align-items: center; justify-content: center; gap: 7px; }
    .comi-clientes .cc-actions form { margin: 0; }
    .comi-clientes .cc-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #475569; border-radius: 8px; background: #172235; color: #cbd5e1; cursor: pointer; }
    .comi-clientes .cc-edit:hover { background: #153f54; color: #7dd3fc; border-color: #38bdf8; }
    .comi-clientes .cc-delete:hover { background: #482635; color: #fda4af; border-color: #fb7185; }
    .comi-clientes .cc-actions-title { text-align: center; }
    .comi-clientes .cc-footer { border-top: 1px solid var(--cc-border); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
    .comi-clientes .cc-footer p { margin: 0; color: var(--cc-muted); font-size: 12px; }
    .comi-clientes .cc-pagination { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .comi-clientes .cc-pagination .cc-btn { min-height: 38px; padding: 7px 12px; font-size: 12px; }
    .comi-clientes .cc-disabled { opacity: .45; cursor: default; }
    .comi-clientes .cc-page-current { padding: 7px 10px; color: #7dd3fc; font-size: 12px; }
    .comi-clientes .cc-empty { padding: 55px 24px; text-align: center; border-top: 1px solid var(--cc-border); }
    .comi-clientes .cc-empty > i { font-size: 32px; color: #38bdf8; }
    .comi-clientes .cc-empty h3 { margin: 14px 0 7px; color: var(--cc-text); font-size: 18px; }
    .comi-clientes .cc-empty p { margin: 0 0 20px; color: var(--cc-muted); font-size: 13px; overflow-wrap: anywhere; }
    @media (max-width: 640px) {
        .comi-clientes { padding: 18px 12px; border-radius: 10px; }
        .comi-clientes .cc-header { align-items: stretch; gap: 18px; }
        .comi-clientes .cc-header > .cc-btn { width: 100%; }
        .comi-clientes .cc-toolbar, .comi-clientes .cc-footer { padding: 16px; }
        .comi-clientes .cc-card-heading { padding: 18px 16px 0; }
        .comi-clientes .cc-search { flex-basis: 100%; max-width: none; }
        .comi-clientes .cc-footer { align-items: flex-start; flex-direction: column; }
    }
</style>

<section class="comi-clientes" aria-labelledby="clientes-title">
    <header class="cc-header">
        <div>
            <p class="cc-eyebrow">COMICenter / Gestión comercial</p>
            <h1 class="cc-title" id="clientes-title">Clientes</h1>
            <p class="cc-subtitle">Administra tus contactos y da seguimiento a cada oportunidad.</p>
        </div>
        <a href="{{ route('clientes.create') }}" class="cc-btn cc-btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo cliente
        </a>
    </header>

    @if (session('success'))
        <div role="status" class="cc-alert" style="background: #143f39; border-color: #286556; color: #a7f3d0;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="cc-alert">{{ session('error') }}</div>
    @endif

    <div class="cc-card">
        <div class="cc-card-heading">
            <span class="cc-heading-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <div>
                <h2 class="cc-card-title">Directorio de clientes</h2>
                <p class="cc-card-note">Información de contacto y estado de tus leads.</p>
            </div>
        </div>
        <div class="cc-toolbar">
            <form action="{{ route('clientes.index') }}" method="GET" class="cc-search-form" role="search">
                <div class="cc-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="buscar" class="cc-input" placeholder="Nombre, empresa, teléfono..."
                        aria-label="Buscar clientes" value="{{ $buscar }}" maxlength="100"
                        @error('buscar') aria-invalid="true" aria-describedby="cc-search-error" @enderror>
                </div>
                <button type="submit" class="cc-btn cc-btn-secondary">Buscar</button>
                @if ($buscar !== '')
                    <a href="{{ route('clientes.index') }}" class="cc-clear">Limpiar búsqueda</a>
                @endif
            </form>
            @error('buscar')
                <p class="cc-error" id="cc-search-error" role="alert">{{ $message }}</p>
            @enderror
            @if ($buscar !== '')
                <p class="cc-filter-note">Resultados para <strong>“{{ $buscar }}”</strong></p>
            @endif
        </div>

        @if ($clientes->count() > 0)
            <div class="cc-table-scroll" tabindex="0" role="region" aria-label="Tabla de clientes; desplaza horizontalmente para ver todas las columnas">
                <table class="cc-table" aria-label="Directorio de clientes">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Empresa</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Correo</th>
                            <th scope="col">País</th>
                            <th scope="col">Fuente</th>
                            <th scope="col">Estado lead</th>
                            <th scope="col">Fecha de registro</th>
                            <th scope="col">Última actualización</th>
                            <th scope="col" class="cc-actions-title">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clientes as $cliente)
                            @php
                                $estado = $estadosLead[$cliente->id_estado_lead] ?? ['texto' => 'Desconocido', 'clase' => 'desconocido'];
                                $nombreCompleto = trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellido_paterno ?? ''));
                                $iniciales = mb_strtoupper(mb_substr(trim($cliente->nombre ?? ''), 0, 1) . mb_substr(trim($cliente->apellido_paterno ?? ''), 0, 1));
                            @endphp
                            <tr>
                                <td><span class="cc-id">#{{ $cliente->id_cliente }}</span></td>
                                <td>
                                    <div class="cc-person">
                                        <span class="cc-avatar" aria-hidden="true">{{ $iniciales ?: '?' }}</span>
                                        <span class="cc-name">{{ $nombreCompleto ?: 'Sin nombre' }}</span>
                                    </div>
                                </td>
                                <td class="cc-wrap">{{ $cliente->empresa ?: 'N/A' }}</td>
                                <td class="cc-nowrap">{{ $cliente->telefono_principal ?: 'N/A' }}</td>
                                <td class="cc-wrap">{{ $cliente->correo ?: 'N/A' }}</td>
                                <td>{{ $cliente->pais ?: 'N/A' }}</td>
                                <td>{{ $cliente->fuente ?: 'N/A' }}</td>
                                <td><span class="cc-badge cc-badge-{{ $estado['clase'] }}">{{ $estado['texto'] }}</span></td>
                                {{-- Conserva los casts de fecha de tu modelo Cliente. --}}
                                <td class="cc-nowrap">{{ $cliente->fecha_registro ? $cliente->fecha_registro->format('d/m/Y') : 'Sin registro' }}</td>
                                <td class="cc-nowrap">{{ $cliente->fecha_actualizacion ? $cliente->fecha_actualizacion->format('d/m/Y') : 'Sin registro' }}</td>
                                <td>
                                    <div class="cc-actions">
                                        <a href="{{ route('clientes.edit', $cliente->id_cliente) }}" class="cc-icon-btn cc-edit"
                                            title="Editar cliente" aria-label="Editar cliente {{ $cliente->id_cliente }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                        <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}" method="POST"
                                            onsubmit="return confirm('¿Estás seguro de eliminar este cliente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="cc-icon-btn cc-delete"
                                                title="Eliminar cliente" aria-label="Eliminar cliente {{ $cliente->id_cliente }}">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
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
            <div class="cc-empty">
                <i class="bi bi-{{ $buscar !== '' ? 'search' : 'people' }}" aria-hidden="true"></i>
                <h3>{{ $buscar !== '' ? 'No encontramos coincidencias' : 'Aún no hay clientes en esta página' }}</h3>
                <p>{{ $buscar !== '' ? 'No se encontraron clientes para “' . $buscar . '”. Prueba con otro nombre, empresa o teléfono.' : 'Agrega un cliente para comenzar a gestionar tus contactos.' }}</p>
                @if ($buscar !== '')
                    <a href="{{ route('clientes.index') }}" class="cc-btn cc-btn-secondary">Ver clientes</a>
                @else
                    <a href="{{ route('clientes.create') }}" class="cc-btn cc-btn-primary">Nuevo cliente</a>
                @endif
            </div>
        @endif

        <footer class="cc-footer">
            <p>{{ $clientes->count() }} {{ $clientes->count() === 1 ? 'cliente en esta página' : 'clientes en esta página' }} · Desliza la tabla para ver todos los datos.</p>
            @if ($clientes->hasPages())
                <nav class="cc-pagination" aria-label="Paginación de clientes">
                    @if ($clientes->onFirstPage())
                        <span class="cc-btn cc-btn-secondary cc-disabled" aria-disabled="true">Anterior</span>
                    @else
                        <a class="cc-btn cc-btn-secondary" href="{{ $clientes->previousPageUrl() }}" rel="prev">Anterior</a>
                    @endif
                    <span class="cc-page-current" aria-current="page">Página {{ $clientes->currentPage() }}</span>
                    @if ($clientes->hasMorePages())
                        <a class="cc-btn cc-btn-secondary" href="{{ $clientes->nextPageUrl() }}" rel="next">Siguiente</a>
                    @else
                        <span class="cc-btn cc-btn-secondary cc-disabled" aria-disabled="true">Siguiente</span>
                    @endif
                </nav>
            @endif
        </footer>
    </div>
</section>
@endsection
