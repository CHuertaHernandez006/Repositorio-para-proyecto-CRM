@extends('layouts.app')

@section('header-title', 'Operarios')

@section('content')

<style>
    .operarios-page {
        color: #e8eef7;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* HEADER */

    .operarios-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 30px;
    }

    .operarios-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #35c6ff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
        margin-bottom: 9px;
    }

    .operarios-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 10px rgba(53,198,255,.6);
    }

    .operarios-title {
        margin: 0;
        color: #ffffff;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .operarios-description {
        margin: 9px 0 0;
        color: #8190a7;
        font-size: 14px;
        line-height: 1.6;
    }

    .btn-nuevo-operario {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 11px 17px;
        border: 1px solid rgba(53,198,255,.22);
        border-radius: 11px;
        background: rgba(53,198,255,.09);
        color: #55ceff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
        white-space: nowrap;
    }

    .btn-nuevo-operario:hover {
        background: rgba(53,198,255,.15);
        border-color: rgba(53,198,255,.4);
        color: #8dddff;
        transform: translateY(-1px);
    }


    /* STATS */

    .operarios-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 22px;
    }

    .operario-stat {
        position: relative;
        overflow: hidden;
        min-height: 115px;
        padding: 20px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 15px;
        background: #111c30;
        box-shadow: 0 12px 30px rgba(0,0,0,.08);
    }

    .operario-stat::after {
        content: "";
        position: absolute;
        width: 100px;
        height: 100px;
        right: -45px;
        bottom: -55px;
        border-radius: 50%;
        background: rgba(53,198,255,.04);
    }

    .stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
    }

    .stat-label {
        color: #718098;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .stat-number {
        margin-top: 8px;
        color: #ffffff;
        font-size: 29px;
        line-height: 1;
        font-weight: 750;
    }

    .stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 39px;
        height: 39px;
        border-radius: 10px;
        background: rgba(53,198,255,.08);
        color: #35c6ff;
    }

    .stat-icon.green {
        background: rgba(52,211,153,.08);
        color: #34d399;
    }

    .stat-icon.gray {
        background: rgba(148,163,184,.07);
        color: #94a3b8;
    }


    /* MAIN CARD */

    .operarios-card {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 16px;
        background: #111c30;
        box-shadow: 0 20px 50px rgba(0,0,0,.10);
    }

    .operarios-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 19px 21px;
        border-bottom: 1px solid rgba(255,255,255,.055);
    }

    .card-title {
        margin: 0;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
    }

    .card-subtitle {
        margin: 4px 0 0;
        color: #66758d;
        font-size: 12px;
    }


    /* SEARCH */

    .operario-search {
        position: relative;
        width: 260px;
    }

    .operario-search svg {
        position: absolute;
        left: 12px;
        top: 50%;
        width: 15px;
        height: 15px;
        transform: translateY(-50%);
        color: #5c6b81;
        pointer-events: none;
    }

    .operario-search input {
        width: 100%;
        height: 38px;
        padding: 0 12px 0 36px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 9px;
        outline: none;
        background: #0c1628;
        color: #ffffff;
        font-size: 12px;
        box-sizing: border-box;
        transition: .2s ease;
    }

    .operario-search input::placeholder {
        color: #536177;
    }

    .operario-search input:focus {
        border-color: rgba(53,198,255,.35);
        box-shadow: 0 0 0 3px rgba(53,198,255,.05);
    }


    /* TABLE */

    .operarios-table-wrapper {
        overflow-x: auto;
    }

    .operarios-table {
        width: 100%;
        border-collapse: collapse;
    }

    .operarios-table th {
        padding: 13px 21px;
        border-bottom: 1px solid rgba(255,255,255,.045);
        color: #59687f;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .11em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .operarios-table td {
        padding: 15px 21px;
        border-bottom: 1px solid rgba(255,255,255,.035);
        vertical-align: middle;
    }

    .operarios-table tbody tr {
        transition: background .15s ease;
    }

    .operarios-table tbody tr:hover {
        background: rgba(255,255,255,.018);
    }

    .operarios-table tbody tr:last-child td {
        border-bottom: none;
    }


    /* USER */

    .operario-user {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 210px;
    }

    .operario-avatar {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border: 1px solid rgba(53,198,255,.12);
        border-radius: 11px;
        background: rgba(53,198,255,.07);
        color: #52cfff;
        font-size: 13px;
        font-weight: 800;
    }

    .operario-online {
        position: absolute;
        right: -2px;
        bottom: -2px;
        width: 9px;
        height: 9px;
        border: 2px solid #111c30;
        border-radius: 50%;
        background: #34d399;
    }

    .operario-name {
        margin: 0;
        color: #edf3fb;
        font-size: 13px;
        font-weight: 650;
    }

    .operario-id {
        margin-top: 3px;
        color: #59687f;
        font-size: 10px;
    }


    /* EMAIL */

    .operario-email {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #8996aa;
        font-size: 12px;
        white-space: nowrap;
    }

    .operario-email svg {
        width: 14px;
        height: 14px;
        color: #58677d;
    }


    /* EMPRESA */

    .empresa-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 9px;
        border: 1px solid rgba(255,255,255,.05);
        border-radius: 7px;
        background: rgba(255,255,255,.025);
        color: #a1adbd;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .empresa-badge svg {
        width: 13px;
        height: 13px;
        color: #66758b;
    }


    /* STATUS */

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 9px;
        border-radius: 30px;
        font-size: 10px;
        font-weight: 700;
    }

    .status-badge.active {
        border: 1px solid rgba(52,211,153,.14);
        background: rgba(52,211,153,.07);
        color: #6ee7b7;
    }

    .status-badge.inactive {
        border: 1px solid rgba(148,163,184,.1);
        background: rgba(148,163,184,.05);
        color: #728097;
    }

    .status-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
    }

    .active .status-dot {
        background: #34d399;
        box-shadow: 0 0 7px rgba(52,211,153,.6);
    }

    .inactive .status-dot {
        background: #64748b;
    }


    /* ACTIONS */

    .operario-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 3px;
    }

    .action-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 31px;
        height: 31px;
        border: 1px solid transparent;
        border-radius: 8px;
        background: transparent;
        color: #64738a;
        cursor: pointer;
        transition: .15s ease;
        text-decoration: none;
    }

    .action-button svg {
        width: 14px;
        height: 14px;
    }

    .action-button:hover {
        border-color: rgba(255,255,255,.05);
        background: rgba(255,255,255,.04);
        color: #ffffff;
    }

    .action-button.view:hover {
        color: #35c6ff;
        background: rgba(53,198,255,.07);
    }

    .action-button.delete:hover {
        color: #f87171;
        background: rgba(248,113,113,.07);
    }


    /* EMPTY */

    .operarios-empty {
        padding: 65px 25px;
        text-align: center;
    }

    .empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin: 0 auto;
        border: 1px solid rgba(53,198,255,.1);
        border-radius: 15px;
        background: rgba(53,198,255,.045);
        color: #35c6ff;
    }

    .empty-icon svg {
        width: 25px;
        height: 25px;
    }

    .empty-title {
        margin: 17px 0 0;
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
    }

    .empty-description {
        max-width: 420px;
        margin: 7px auto 0;
        color: #65748b;
        font-size: 12px;
        line-height: 1.7;
    }

    .empty-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 20px;
        padding: 10px 16px;
        border-radius: 9px;
        background: #35c6ff;
        color: #07111f;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition: .2s ease;
    }

    .empty-button:hover {
        background: #67d5ff;
        transform: translateY(-1px);
    }


    /* NO RESULTS */

    .operarios-no-results {
        display: none;
        padding: 45px 20px;
        text-align: center;
        color: #66758c;
        font-size: 12px;
    }


    /* RESPONSIVE */

    @media (max-width: 900px) {

        .operarios-stats {
            grid-template-columns: 1fr;
        }

        .operarios-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .btn-nuevo-operario {
            width: 100%;
            justify-content: center;
        }

        .operarios-card-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .operario-search {
            width: 100%;
        }
    }

    @media (max-width: 650px) {

        .operarios-page {
            padding: 0 2px;
        }

        .operarios-title {
            font-size: 27px;
        }

        .operarios-table th,
        .operarios-table td {
            padding-left: 14px;
            padding-right: 14px;
        }
    }
</style>


<div class="operarios-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="operarios-header">

        <div>

            <div class="operarios-eyebrow">
                <span class="operarios-eyebrow-dot"></span>
                Gestión del equipo
            </div>

            <h1 class="operarios-title">
                Operarios
            </h1>

            <p class="operarios-description">
                Administra las cuentas de los operarios y consulta la información
                de tu equipo.
            </p>

        </div>


        <a
            href="{{ route('operarios.create') }}"
            class="btn-nuevo-operario"
        >
            <i data-lucide="user-plus"></i>
            Nuevo operario
        </a>

    </div>


    {{-- =========================================================
         MENSAJE
    ========================================================== --}}

    @if(session('success'))

        <div style="
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom:20px;
            padding:12px 15px;
            border:1px solid rgba(52,211,153,.15);
            border-radius:10px;
            background:rgba(52,211,153,.07);
            color:#6ee7b7;
            font-size:12px;
            font-weight:600;
        ">

            <i data-lucide="circle-check"></i>

            {{ session('success') }}

        </div>

    @endif


    {{-- =========================================================
         STATS
    ========================================================== --}}

    <div class="operarios-stats">

        <div class="operario-stat">

            <div class="stat-top">

                <div>
                    <div class="stat-label">
                        Total de operarios
                    </div>

                    <div class="stat-number">
                        {{ $operarios->count() }}
                    </div>
                </div>

                <div class="stat-icon">
                    <i data-lucide="users-round"></i>
                </div>

            </div>

        </div>


        <div class="operario-stat">

            <div class="stat-top">

                <div>
                    <div class="stat-label">
                        Operarios activos
                    </div>

                    <div class="stat-number">
                        {{ $operarios->where('estado', true)->count() }}
                    </div>
                </div>

                <div class="stat-icon green">
                    <i data-lucide="user-round-check"></i>
                </div>

            </div>

        </div>


        <div class="operario-stat">

            <div class="stat-top">

                <div>
                    <div class="stat-label">
                        Operarios inactivos
                    </div>

                    <div class="stat-number">
                        {{ $operarios->where('estado', false)->count() }}
                    </div>
                </div>

                <div class="stat-icon gray">
                    <i data-lucide="user-round-x"></i>
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         LISTADO
    ========================================================== --}}

    <div class="operarios-card">

        <div class="operarios-card-header">

            <div>

                <h2 class="card-title">
                    Equipo de trabajo
                </h2>

                <p class="card-subtitle">
                    Operarios registrados en tu espacio de gestión.
                </p>

            </div>


            <div class="operario-search">

                <i data-lucide="search"></i>

                <input
                    type="text"
                    id="buscarOperario"
                    placeholder="Buscar operario..."
                    autocomplete="off"
                >

            </div>

        </div>


        @if($operarios->count() > 0)

            <div class="operarios-table-wrapper">

                <table class="operarios-table">

                    <thead>

                        <tr>

                            <th>
                                Operario
                            </th>

                            <th>
                                Correo
                            </th>

                            <th>
                                Empresa
                            </th>

                            <th>
                                Estado
                            </th>

                            <th style="text-align:right;">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody id="tablaOperarios">

                        @foreach($operarios as $operario)

                            @php
                                $activo = $operario->estado ?? true;
                                $inicial = strtoupper(substr($operario->name, 0, 1));
                            @endphp

                            <tr
                                class="operario-row"
                                data-search="{{ strtolower($operario->name . ' ' . $operario->email . ' ' . ($operario->empresa->nombre ?? '')) }}"
                            >

                                {{-- OPERARIO --}}

                                <td>

                                    <div class="operario-user">

                                        <div class="operario-avatar">

                                            {{ $inicial }}

                                            @if($activo)
                                                <span class="operario-online"></span>
                                            @endif

                                        </div>


                                        <div>

                                            <p class="operario-name">
                                                {{ $operario->name }}
                                            </p>

                                            <p class="operario-id">
                                                ID #{{ $operario->id }}
                                            </p>

                                        </div>

                                    </div>

                                </td>


                                {{-- CORREO --}}

                                <td>

                                    <div class="operario-email">

                                        <i data-lucide="mail"></i>

                                        {{ $operario->email }}

                                    </div>

                                </td>


                                {{-- EMPRESA --}}

                                <td>

                                    @if($operario->empresa)

                                        <span class="empresa-badge">

                                            <i data-lucide="building-2"></i>

                                            {{ $operario->empresa->nombre }}

                                        </span>

                                    @else

                                        <span style="color:#59687f;font-size:11px;">
                                            Sin empresa
                                        </span>

                                    @endif

                                </td>


                                {{-- ESTADO --}}

                                <td>

                                    @if($activo)

                                        <span class="status-badge active">

                                            <span class="status-dot"></span>

                                            Activo

                                        </span>

                                    @else

                                        <span class="status-badge inactive">

                                            <span class="status-dot"></span>

                                            Inactivo

                                        </span>

                                    @endif

                                </td>


                                {{-- ACCIONES --}}

                                <td>

                                    <div class="operario-actions">

                                        @if(Route::has('operarios.show'))

                                            <a
                                                href="{{ route('operarios.show', $operario) }}"
                                                class="action-button view"
                                                title="Ver operario"
                                            >
                                                <i data-lucide="eye"></i>
                                            </a>

                                        @endif


                                        @if(Route::has('operarios.edit'))

                                            <a
                                                href="{{ route('operarios.edit', $operario) }}"
                                                class="action-button"
                                                title="Editar operario"
                                            >
                                                <i data-lucide="pencil"></i>
                                            </a>

                                        @endif


                                        @if(Route::has('operarios.toggleEstado'))

                                            <form
                                                action="{{ route('operarios.toggleEstado', $operario) }}"
                                                method="POST"
                                                style="margin:0;"
                                            >

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="action-button"
                                                    title="{{ $activo ? 'Desactivar' : 'Activar' }}"
                                                >

                                                    <i
                                                        data-lucide="{{ $activo ? 'user-round-x' : 'user-round-check' }}"
                                                    ></i>

                                                </button>

                                            </form>

                                        @endif


                                        @if(Route::has('operarios.destroy'))

                                            <form
                                                action="{{ route('operarios.destroy', $operario) }}"
                                                method="POST"
                                                style="margin:0;"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar este operario?');"
                                            >

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="action-button delete"
                                                    title="Eliminar"
                                                >
                                                    <i data-lucide="trash-2"></i>
                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div
                id="sinResultados"
                class="operarios-no-results"
            >
                No encontramos operarios que coincidan con tu búsqueda.
            </div>

        @else

            {{-- =================================================
                 EMPTY STATE
            ================================================== --}}

            <div class="operarios-empty">

                <div class="empty-icon">

                    <i data-lucide="users-round"></i>

                </div>

                <h3 class="empty-title">
                    Aún no hay operarios
                </h3>

                <p class="empty-description">
                    Crea el primer operario para comenzar a administrar
                    tu equipo dentro del CRM.
                </p>


                <a
                    href="{{ route('operarios.create') }}"
                    class="empty-button"
                >

                    <i data-lucide="user-plus"></i>

                    Crear primer operario

                </a>

            </div>

        @endif

    </div>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const buscador = document.getElementById('buscarOperario');
    const filas = document.querySelectorAll('.operario-row');
    const sinResultados = document.getElementById('sinResultados');

    if (!buscador) {
        return;
    }

    buscador.addEventListener('input', function () {

        const texto = this.value
            .toLowerCase()
            .trim();

        let encontrados = 0;

        filas.forEach(function (fila) {

            const contenido = fila.dataset.search || '';

            if (contenido.includes(texto)) {

                fila.style.display = '';
                encontrados++;

            } else {

                fila.style.display = 'none';

            }

        });

        if (sinResultados) {

            if (encontrados === 0 && texto !== '') {
                sinResultados.style.display = 'block';
            } else {
                sinResultados.style.display = 'none';
            }

        }

    });

});

</script>

@endsection
