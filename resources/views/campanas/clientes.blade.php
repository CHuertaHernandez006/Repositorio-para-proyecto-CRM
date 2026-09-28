@extends('layouts.app')

@section('title', 'Clientes de campaña - CRM')
@section('header-title', 'Clientes de Campaña')

@section('content')

@php
    $clientesDisponibles = $clientesDisponibles ?? collect();
    $clientesAsignados = $clientesAsignados ?? collect();
@endphp

<style>
    .campaign-clients-page {
        max-width: 1400px;
        margin: 0 auto;
        color: #e8eef7;
    }

    /* =========================================================
       HEADER
    ========================================================== */

    .campaign-clients-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 25px;
    }

    .page-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: #35c6ff;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .page-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 10px rgba(53, 198, 255, .6);
    }

    .page-title {
        margin: 0;
        color: #ffffff;
        font-size: 30px;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .page-description {
        max-width: 750px;
        margin: 8px 0 0;
        color: #7e8da3;
        font-size: 12px;
        line-height: 1.6;
    }

    .page-description strong {
        color: #cbd5e1;
    }

    .header-actions {
        display: flex;
        gap: 8px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        height: 40px;
        padding: 0 14px;
        border: 1px solid rgba(148, 163, 184, .15);
        border-radius: 9px;
        background: rgba(148, 163, 184, .05);
        color: #9aa8ba;
        font-size: 10px;
        font-weight: 750;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-back:hover {
        border-color: rgba(148, 163, 184, .30);
        background: rgba(148, 163, 184, .10);
        color: #e2e8f0;
        transform: translateY(-1px);
    }

    .btn-back svg {
        width: 14px;
        height: 14px;
    }

    /* =========================================================
       ALERTAS
    ========================================================== */

    .page-alert {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 18px;
        padding: 13px 15px;
        border-radius: 10px;
        font-size: 11px;
        line-height: 1.6;
    }

    .page-alert svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .page-alert.success {
        border: 1px solid rgba(52, 211, 153, .16);
        background: rgba(52, 211, 153, .07);
        color: #6ee7b7;
    }

    .page-alert.error {
        border: 1px solid rgba(248, 113, 113, .16);
        background: rgba(248, 113, 113, .06);
        color: #fca5a5;
    }

    .error-list {
        margin: 5px 0 0;
        padding-left: 18px;
    }

    /* =========================================================
       RESUMEN
    ========================================================== */

    .campaign-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
        padding: 18px 20px;
        border: 1px solid rgba(53, 198, 255, .10);
        border-radius: 14px;
        background: rgba(53, 198, 255, .035);
    }

    .campaign-summary-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .campaign-summary-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border: 1px solid rgba(53, 198, 255, .17);
        border-radius: 11px;
        background: rgba(53, 198, 255, .07);
        color: #4fcfff;
    }

    .campaign-summary-icon svg {
        width: 18px;
        height: 18px;
    }

    .campaign-summary-name {
        color: #f1f5f9;
        font-size: 13px;
        font-weight: 750;
    }

    .campaign-summary-text {
        margin-top: 4px;
        color: #66768c;
        font-size: 10px;
    }

    .campaign-summary-count {
        text-align: right;
        flex-shrink: 0;
    }

    .campaign-summary-count strong {
        display: block;
        color: #59d1ff;
        font-size: 25px;
        line-height: 1;
    }

    .campaign-summary-count span {
        display: block;
        margin-top: 5px;
        color: #64748b;
        font-size: 8px;
        font-weight: 750;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    /* =========================================================
       LAYOUT
    ========================================================== */

    .clients-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1.1fr)
            minmax(0, .9fr);
        gap: 17px;
        align-items: start;
    }

    .clients-panel {
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 15px;
        background: #111c30;
        box-shadow: 0 15px 35px rgba(0, 0, 0, .07);
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 17px 18px;
        border-bottom: 1px solid rgba(255, 255, 255, .05);
    }

    .panel-heading {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .panel-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border: 1px solid rgba(53, 198, 255, .14);
        border-radius: 9px;
        background: rgba(53, 198, 255, .055);
        color: #55ceff;
    }

    .panel-icon.green {
        border-color: rgba(52, 211, 153, .16);
        background: rgba(52, 211, 153, .055);
        color: #6ee7b7;
    }

    .panel-icon svg {
        width: 15px;
        height: 15px;
    }

    .panel-title {
        color: #f1f5f9;
        font-size: 13px;
        font-weight: 750;
    }

    .panel-description {
        margin-top: 3px;
        color: #607087;
        font-size: 9px;
    }

    .panel-count {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 25px;
        padding: 0 8px;
        border: 1px solid rgba(53, 198, 255, .13);
        border-radius: 7px;
        background: rgba(53, 198, 255, .05);
        color: #65d4ff;
        font-size: 10px;
        font-weight: 750;
    }

    .panel-count.green {
        border-color: rgba(52, 211, 153, .15);
        background: rgba(52, 211, 153, .06);
        color: #6ee7b7;
    }

    .panel-body {
        padding: 17px;
    }

    /* =========================================================
       BUSCADOR
    ========================================================== */

    .search-wrapper {
        position: relative;
        margin-bottom: 13px;
    }

    .search-wrapper svg {
        position: absolute;
        top: 50%;
        left: 12px;
        width: 14px;
        height: 14px;
        color: #53637a;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .search-control {
        width: 100%;
        height: 40px;
        padding: 0 12px 0 36px;
        border: 1px solid rgba(255, 255, 255, .075);
        border-radius: 9px;
        outline: none;
        background: #091426;
        color: #e5edf7;
        font-size: 11px;
        box-sizing: border-box;
        transition: .2s ease;
    }

    .search-control::placeholder {
        color: #53637a;
    }

    .search-control:focus {
        border-color: rgba(53, 198, 255, .35);
        box-shadow: 0 0 0 3px rgba(53, 198, 255, .04);
    }

    /* =========================================================
       SELECCIÓN
    ========================================================== */

    .selection-tools {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 11px;
    }

    .btn-select-all {
        padding: 0;
        border: 0;
        background: transparent;
        color: #60cffa;
        font-size: 9px;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-select-all:hover {
        color: #9ce5ff;
    }

    .selection-info {
        color: #64748b;
        font-size: 9px;
    }

    .selection-info strong {
        color: #5fd2ff;
    }

    /* =========================================================
       CLIENTES
    ========================================================== */

    .available-list,
    .assigned-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 520px;
        overflow-y: auto;
        padding-right: 3px;
    }

    .available-list::-webkit-scrollbar,
    .assigned-list::-webkit-scrollbar {
        width: 5px;
    }

    .available-list::-webkit-scrollbar-track,
    .assigned-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .available-list::-webkit-scrollbar-thumb,
    .assigned-list::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .15);
        border-radius: 10px;
    }

    .client-option,
    .assigned-client {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 12px;
        border: 1px solid rgba(255, 255, 255, .045);
        border-radius: 10px;
        background: rgba(7, 17, 31, .22);
        transition: .15s ease;
    }

    .client-option {
        cursor: pointer;
    }

    .client-option:hover {
        border-color: rgba(53, 198, 255, .15);
        background: rgba(53, 198, 255, .035);
    }

    .client-option.selected {
        border-color: rgba(53, 198, 255, .28);
        background: rgba(53, 198, 255, .075);
    }

    .client-checkbox {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        accent-color: #35c6ff;
        cursor: pointer;
    }

    .client-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        border: 1px solid rgba(53, 198, 255, .13);
        border-radius: 9px;
        background: rgba(53, 198, 255, .06);
        color: #51cdff;
        font-size: 10px;
        font-weight: 800;
    }

    .assigned-client .client-avatar {
        border-color: rgba(52, 211, 153, .13);
        background: rgba(52, 211, 153, .055);
        color: #6ee7b7;
    }

    .client-data {
        flex: 1;
        min-width: 0;
    }

    .client-name {
        color: #dfe7f1;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .client-meta {
        margin-top: 3px;
        color: #64748b;
        font-size: 9px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .client-phone {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
        color: #53637a;
        font-size: 8px;
    }

    .client-phone svg {
        width: 9px;
        height: 9px;
    }

    /* =========================================================
       BOTÓN AGREGAR
    ========================================================== */

    .assign-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, .05);
    }

    .assign-help {
        color: #53637a;
        font-size: 9px;
    }

    .btn-assign {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 39px;
        padding: 0 15px;
        border: 1px solid rgba(53, 198, 255, .25);
        border-radius: 9px;
        background: #35c6ff;
        color: #07111f;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn-assign:hover:not(:disabled) {
        background: #66d5ff;
        transform: translateY(-1px);
        box-shadow: 0 0 16px rgba(53, 198, 255, .13);
    }

    .btn-assign:disabled {
        opacity: .35;
        cursor: not-allowed;
        transform: none;
    }

    .btn-assign svg {
        width: 13px;
        height: 13px;
    }

    /* =========================================================
       QUITAR CLIENTE
    ========================================================== */

    .remove-form {
        margin: 0;
    }

    .btn-remove {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        padding: 0;
        border: 1px solid rgba(248, 113, 113, .13);
        border-radius: 8px;
        background: rgba(248, 113, 113, .045);
        color: #d98282;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn-remove:hover {
        border-color: rgba(248, 113, 113, .30);
        background: rgba(248, 113, 113, .09);
        color: #fca5a5;
    }

    .btn-remove svg {
        width: 13px;
        height: 13px;
    }

    /* =========================================================
       VACÍO
    ========================================================== */

    .empty-list {
        padding: 38px 20px;
        border: 1px dashed rgba(148, 163, 184, .12);
        border-radius: 10px;
        color: #627188;
        font-size: 10px;
        line-height: 1.6;
        text-align: center;
    }

    .empty-list-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin: 0 auto 10px;
        border: 1px solid rgba(148, 163, 184, .11);
        border-radius: 10px;
        background: rgba(148, 163, 184, .04);
        color: #68788e;
    }

    .empty-list-icon svg {
        width: 18px;
        height: 18px;
    }

    .hidden-client {
        display: none !important;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 950px) {
        .clients-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .campaign-clients-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .header-actions,
        .btn-back {
            width: 100%;
            box-sizing: border-box;
        }

        .campaign-summary {
            align-items: flex-start;
            flex-direction: column;
        }

        .campaign-summary-count {
            text-align: left;
        }

        .selection-tools,
        .assign-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        .btn-assign {
            width: 100%;
        }
    }
</style>


<div class="campaign-clients-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="campaign-clients-header">

        <div>

            <div class="page-eyebrow">
                <span class="page-eyebrow-dot"></span>
                Agrupación de clientes
            </div>

            <h1 class="page-title">
                Clientes de campaña
            </h1>

            <p class="page-description">
                Agrega o retira clientes de
                <strong>{{ $campana->nombre }}</strong>.
                Los clientes seleccionados quedarán relacionados
                directamente con esta campaña.
            </p>

        </div>


        <div class="header-actions">

            <a
                href="{{ url(
                    '/campanas/' . $campana->id_campana
                ) }}"
                class="btn-back"
            >
                <i data-lucide="arrow-left"></i>
                Volver a campaña
            </a>

        </div>

    </div>


    {{-- =========================================================
         MENSAJE DE ÉXITO
    ========================================================== --}}

    @if(session('success'))

        <div class="page-alert success">

            <i data-lucide="circle-check"></i>

            <div>
                {{ session('success') }}
            </div>

        </div>

    @endif


    {{-- =========================================================
         ERRORES
    ========================================================== --}}

    @if($errors->any())

        <div class="page-alert error">

            <i data-lucide="triangle-alert"></i>

            <div>

                <strong>
                    No se pudo completar la operación.
                </strong>

                <ul class="error-list">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif


    {{-- =========================================================
         RESUMEN DE LA CAMPAÑA
    ========================================================== --}}

    <div class="campaign-summary">

        <div class="campaign-summary-left">

            <div class="campaign-summary-icon">
                <i data-lucide="megaphone"></i>
            </div>


            <div>

                <div class="campaign-summary-name">
                    {{ $campana->nombre }}
                </div>

                <div class="campaign-summary-text">

                    {{ $campana->estadoCampana->nombre ?? 'Sin estado' }}

                    ·

                    {{ \Carbon\Carbon::parse(
                        $campana->fecha_inicio
                    )->format('d/m/Y') }}

                    →

                    {{ \Carbon\Carbon::parse(
                        $campana->fecha_fin
                    )->format('d/m/Y') }}

                </div>

            </div>

        </div>


        <div class="campaign-summary-count">

            <strong>
                {{ $clientesAsignados->count() }}
            </strong>

            <span>
                Clientes asignados
            </span>

        </div>

    </div>


    {{-- =========================================================
         PANELES
    ========================================================== --}}

    <div class="clients-layout">

        {{-- =====================================================
             CLIENTES DISPONIBLES
        ====================================================== --}}

        <section class="clients-panel">

            <div class="panel-header">

                <div class="panel-heading">

                    <div class="panel-icon">
                        <i data-lucide="user-plus"></i>
                    </div>

                    <div>

                        <div class="panel-title">
                            Clientes disponibles
                        </div>

                        <div class="panel-description">
                            Selecciona uno o varios clientes para agregarlos.
                        </div>

                    </div>

                </div>


                <div class="panel-count">
                    {{ $clientesDisponibles->count() }}
                </div>

            </div>


            <div class="panel-body">

                <div class="search-wrapper">

                    <i data-lucide="search"></i>

                    <input
                        type="text"
                        id="buscarDisponible"
                        class="search-control"
                        placeholder="Buscar por nombre, empresa, correo o teléfono..."
                        autocomplete="off"
                    >

                </div>


                @if($clientesDisponibles->count() > 0)

                    <form
                        action="{{ url(
                            '/campanas/' .
                            $campana->id_campana .
                            '/clientes'
                        ) }}"
                        method="POST"
                    >

                        @csrf


                        <div class="selection-tools">

                            <button
                                type="button"
                                class="btn-select-all"
                                id="seleccionarVisibles"
                            >
                                Seleccionar visibles
                            </button>


                            <div class="selection-info">

                                <strong id="cantidadSeleccionados">
                                    0
                                </strong>

                                seleccionados

                            </div>

                        </div>


                        <div
                            class="available-list"
                            id="listaDisponibles"
                        >

                            @foreach($clientesDisponibles as $cliente)

                                @php
                                    $nombreCompleto =
                                        trim(
                                            $cliente->nombre_completo
                                            ?: (
                                                ($cliente->nombre ?? '') . ' ' .
                                                ($cliente->apellido_paterno ?? '') . ' ' .
                                                ($cliente->apellido_materno ?? '')
                                            )
                                        );

                                    $inicial = mb_strtoupper(
                                        mb_substr(
                                            $cliente->nombre ?? 'C',
                                            0,
                                            1
                                        )
                                    );

                                    $busqueda = mb_strtolower(
                                        $nombreCompleto . ' ' .
                                        ($cliente->empresa ?? '') . ' ' .
                                        ($cliente->correo ?? '') . ' ' .
                                        ($cliente->telefono_principal ?? '')
                                    );
                                @endphp


                                <label
                                    class="client-option"
                                    data-client-available
                                    data-search="{{ $busqueda }}"
                                >

                                    <input
                                        type="checkbox"
                                        name="clientes[]"
                                        value="{{ $cliente->id_cliente }}"
                                        class="client-checkbox"
                                    >


                                    <div class="client-avatar">
                                        {{ $inicial }}
                                    </div>


                                    <div class="client-data">

                                        <div class="client-name">
                                            {{ $nombreCompleto }}
                                        </div>


                                        <div class="client-meta">

                                            {{ $cliente->empresa
                                                ?: 'Sin empresa registrada'
                                            }}

                                            @if($cliente->correo)
                                                · {{ $cliente->correo }}
                                            @endif

                                        </div>


                                        @if($cliente->telefono_principal)

                                            <div class="client-phone">

                                                <i data-lucide="phone"></i>

                                                {{ $cliente->telefono_principal }}

                                            </div>

                                        @endif

                                    </div>

                                </label>

                            @endforeach

                        </div>


                        <div class="assign-actions">

                            <div class="assign-help">
                                Selecciona al menos un cliente para continuar.
                            </div>


                            <button
                                type="submit"
                                class="btn-assign"
                                id="btnAsignar"
                                disabled
                            >
                                <i data-lucide="user-plus"></i>
                                Agregar seleccionados
                            </button>

                        </div>

                    </form>

                @else

                    <div class="empty-list">

                        <div class="empty-list-icon">
                            <i data-lucide="circle-check"></i>
                        </div>

                        No hay clientes disponibles para agregar.
                        Todos los clientes disponibles ya forman parte
                        de esta campaña.

                    </div>

                @endif

            </div>

        </section>


        {{-- =====================================================
             CLIENTES ASIGNADOS
        ====================================================== --}}

        <section class="clients-panel">

            <div class="panel-header">

                <div class="panel-heading">

                    <div class="panel-icon green">
                        <i data-lucide="users-round"></i>
                    </div>

                    <div>

                        <div class="panel-title">
                            En esta campaña
                        </div>

                        <div class="panel-description">
                            Clientes actualmente asignados.
                        </div>

                    </div>

                </div>


                <div class="panel-count green">
                    {{ $clientesAsignados->count() }}
                </div>

            </div>


            <div class="panel-body">

                <div class="search-wrapper">

                    <i data-lucide="search"></i>

                    <input
                        type="text"
                        id="buscarAsignado"
                        class="search-control"
                        placeholder="Buscar cliente asignado..."
                        autocomplete="off"
                    >

                </div>


                @if($clientesAsignados->count() > 0)

                    <div
                        class="assigned-list"
                        id="listaAsignados"
                    >

                        @foreach($clientesAsignados as $cliente)

                            @php
                                $nombreCompleto =
                                    trim(
                                        $cliente->nombre_completo
                                        ?: (
                                            ($cliente->nombre ?? '') . ' ' .
                                            ($cliente->apellido_paterno ?? '') . ' ' .
                                            ($cliente->apellido_materno ?? '')
                                        )
                                    );

                                $inicial = mb_strtoupper(
                                    mb_substr(
                                        $cliente->nombre ?? 'C',
                                        0,
                                        1
                                    )
                                );

                                $busqueda = mb_strtolower(
                                    $nombreCompleto . ' ' .
                                    ($cliente->empresa ?? '') . ' ' .
                                    ($cliente->correo ?? '') . ' ' .
                                    ($cliente->telefono_principal ?? '')
                                );
                            @endphp


                            <div
                                class="assigned-client"
                                data-client-assigned
                                data-search="{{ $busqueda }}"
                            >

                                <div class="client-avatar">
                                    {{ $inicial }}
                                </div>


                                <div class="client-data">

                                    <div class="client-name">
                                        {{ $nombreCompleto }}
                                    </div>


                                    <div class="client-meta">

                                        {{ $cliente->empresa
                                            ?: 'Sin empresa registrada'
                                        }}

                                        @if($cliente->correo)
                                            · {{ $cliente->correo }}
                                        @endif

                                    </div>


                                    @if($cliente->telefono_principal)

                                        <div class="client-phone">

                                            <i data-lucide="phone"></i>

                                            {{ $cliente->telefono_principal }}

                                        </div>

                                    @endif

                                </div>


                                <form
                                    action="{{ url(
                                        '/campanas/' .
                                        $campana->id_campana .
                                        '/clientes/' .
                                        $cliente->id_cliente
                                    ) }}"
                                    method="POST"
                                    class="remove-form"
                                    onsubmit="return confirm(
                                        '¿Retirar a este cliente de la campaña?'
                                    );"
                                >

                                    @csrf
                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="btn-remove"
                                        title="Retirar cliente de la campaña"
                                    >
                                        <i data-lucide="x"></i>
                                    </button>

                                </form>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="empty-list">

                        <div class="empty-list-icon">
                            <i data-lucide="users"></i>
                        </div>

                        Esta campaña todavía no tiene clientes asignados.
                        Selecciona clientes del panel izquierdo para comenzar.

                    </div>

                @endif

            </div>

        </section>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ICONOS
    |--------------------------------------------------------------------------
    */

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }


    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS
    |--------------------------------------------------------------------------
    */

    const checkboxes =
        document.querySelectorAll('.client-checkbox');

    const contador =
        document.getElementById('cantidadSeleccionados');

    const botonAsignar =
        document.getElementById('btnAsignar');

    const botonSeleccionar =
        document.getElementById('seleccionarVisibles');


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR SELECCIÓN
    |--------------------------------------------------------------------------
    */

    function actualizarSeleccion() {

        const seleccionados =
            document.querySelectorAll(
                '.client-checkbox:checked'
            ).length;


        if (contador) {
            contador.textContent = seleccionados;
        }


        if (botonAsignar) {
            botonAsignar.disabled = seleccionados === 0;
        }


        document
            .querySelectorAll('[data-client-available]')
            .forEach(function (elemento) {

                const checkbox =
                    elemento.querySelector(
                        '.client-checkbox'
                    );

                elemento.classList.toggle(
                    'selected',
                    checkbox && checkbox.checked
                );

            });
    }


    checkboxes.forEach(function (checkbox) {

        checkbox.addEventListener(
            'change',
            actualizarSeleccion
        );

    });


    /*
    |--------------------------------------------------------------------------
    | SELECCIONAR CLIENTES VISIBLES
    |--------------------------------------------------------------------------
    */

    if (botonSeleccionar) {

        botonSeleccionar.addEventListener(
            'click',
            function () {

                const visibles =
                    Array.from(
                        document.querySelectorAll(
                            '[data-client-available]'
                        )
                    ).filter(function (cliente) {

                        return !cliente.classList.contains(
                            'hidden-client'
                        );

                    });


                if (visibles.length === 0) {
                    return;
                }


                const todosSeleccionados =
                    visibles.every(function (cliente) {

                        const checkbox =
                            cliente.querySelector(
                                '.client-checkbox'
                            );

                        return checkbox && checkbox.checked;

                    });


                visibles.forEach(function (cliente) {

                    const checkbox =
                        cliente.querySelector(
                            '.client-checkbox'
                        );

                    if (checkbox) {
                        checkbox.checked =
                            !todosSeleccionados;
                    }

                });


                botonSeleccionar.textContent =
                    todosSeleccionados
                        ? 'Seleccionar visibles'
                        : 'Quitar selección visible';


                actualizarSeleccion();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR TEXTO
    |--------------------------------------------------------------------------
    */

    function normalizar(texto) {

        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .trim();

    }


    /*
    |--------------------------------------------------------------------------
    | BUSCADOR
    |--------------------------------------------------------------------------
    */

    function configurarBusqueda(
        inputId,
        selector
    ) {

        const input =
            document.getElementById(inputId);


        if (!input) {
            return;
        }


        const elementos =
            document.querySelectorAll(selector);


        input.addEventListener(
            'input',
            function () {

                const busqueda =
                    normalizar(input.value);


                elementos.forEach(function (elemento) {

                    const contenido =
                        normalizar(
                            elemento.dataset.search
                        );


                    elemento.classList.toggle(
                        'hidden-client',
                        !contenido.includes(busqueda)
                    );

                });


                if (
                    inputId === 'buscarDisponible' &&
                    botonSeleccionar
                ) {
                    botonSeleccionar.textContent =
                        'Seleccionar visibles';
                }

            }
        );

    }


    configurarBusqueda(
        'buscarDisponible',
        '[data-client-available]'
    );


    configurarBusqueda(
        'buscarAsignado',
        '[data-client-assigned]'
    );


    actualizarSeleccion();

});
</script>

@endsection