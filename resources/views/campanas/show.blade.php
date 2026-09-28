@extends('layouts.app')

@section('title', 'Detalle de campaña - CRM')
@section('header-title', 'Detalle de Campaña')

@section('content')

@php
    $inicio = \Carbon\Carbon::parse(
        $campana->fecha_inicio
    );

    $fin = \Carbon\Carbon::parse(
        $campana->fecha_fin
    );

    $hoy = now()->startOfDay();

    if ($inicio->gt($hoy)) {
        $situacion = 'Próxima';
        $situacionClase = 'upcoming';
    } elseif ($fin->lt($hoy)) {
        $situacion = 'Finalizada';
        $situacionClase = 'finished';
    } else {
        $situacion = 'En curso';
        $situacionClase = 'active';
    }
@endphp


<style>
    .campaign-show-page {
        max-width: 1100px;
        margin: 0 auto;
        color: #e8eef7;
    }

    /* =========================================================
       HEADER
    ========================================================== */

    .show-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 24px;
    }

    .show-eyebrow {
        color: #35c6ff;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .show-title {
        margin: 8px 0 0;
        color: #fff;
        font-size: 30px;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .show-id {
        margin-top: 5px;
        color: #617087;
        font-size: 10px;
    }

    /* =========================================================
       ACCIONES
    ========================================================== */

    .show-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 39px;
        padding: 0 13px;
        border-radius: 9px;
        font-size: 10px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .action-btn svg {
        width: 13px;
        height: 13px;
    }

    .action-btn.back {
        border: 1px solid rgba(148, 163, 184, .14);
        background: rgba(148, 163, 184, .05);
        color: #94a3b8;
    }

    .action-btn.back:hover {
        border-color: rgba(148, 163, 184, .28);
        background: rgba(148, 163, 184, .10);
        color: #d8e0ea;
        transform: translateY(-1px);
    }

    .action-btn.clients {
        border: 1px solid rgba(52, 211, 153, .20);
        background: rgba(52, 211, 153, .07);
        color: #6ee7b7;
    }

    .action-btn.clients:hover {
        border-color: rgba(52, 211, 153, .38);
        background: rgba(52, 211, 153, .13);
        color: #a7f3d0;
        transform: translateY(-1px);
    }

    .action-btn.edit {
        border: 1px solid rgba(53, 198, 255, .20);
        background: rgba(53, 198, 255, .08);
        color: #5ed1ff;
    }

    .action-btn.edit:hover {
        border-color: rgba(53, 198, 255, .38);
        background: rgba(53, 198, 255, .14);
        color: #9ce7ff;
        transform: translateY(-1px);
    }

    .action-btn.delete {
        border: 1px solid rgba(248, 113, 113, .16);
        background: rgba(248, 113, 113, .06);
        color: #f99;
    }

    .action-btn.delete:hover {
        border-color: rgba(248, 113, 113, .32);
        background: rgba(248, 113, 113, .11);
        color: #fca5a5;
        transform: translateY(-1px);
    }

    .delete-form {
        margin: 0;
    }

    /* =========================================================
       ALERTAS
    ========================================================== */

    .show-alert {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 18px;
        padding: 13px 15px;
        border: 1px solid rgba(52, 211, 153, .16);
        border-radius: 10px;
        background: rgba(52, 211, 153, .07);
        color: #6ee7b7;
        font-size: 11px;
    }

    .show-alert svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    /* =========================================================
       TARJETA PRINCIPAL
    ========================================================== */

    .show-card {
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 16px;
        background: #111c30;
        box-shadow: 0 18px 40px rgba(0, 0, 0, .08);
    }

    .show-main {
        padding: 24px;
    }

    .show-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }

    /* =========================================================
       ESTADOS
    ========================================================== */

    .status-group {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 30px;
        font-size: 9px;
        font-weight: 750;
    }

    .status-badge::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
    }

    .status-badge.active {
        background: rgba(52, 211, 153, .07);
        border: 1px solid rgba(52, 211, 153, .16);
        color: #6ee7b7;
    }

    .status-badge.active::before {
        background: #34d399;
        box-shadow: 0 0 7px rgba(52, 211, 153, .55);
    }

    .status-badge.upcoming {
        background: rgba(167, 139, 250, .07);
        border: 1px solid rgba(167, 139, 250, .16);
        color: #c4b5fd;
    }

    .status-badge.upcoming::before {
        background: #a78bfa;
    }

    .status-badge.finished {
        background: rgba(148, 163, 184, .05);
        border: 1px solid rgba(148, 163, 184, .12);
        color: #94a3b8;
    }

    .status-badge.finished::before {
        background: #64748b;
    }

    .status-badge.system {
        background: rgba(53, 198, 255, .06);
        border: 1px solid rgba(53, 198, 255, .13);
        color: #67d5ff;
    }

    .status-badge.system::before {
        background: #35c6ff;
    }

    /* =========================================================
       INFORMACIÓN
    ========================================================== */

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 13px;
        margin-top: 23px;
    }

    .info-card {
        padding: 16px;
        border: 1px solid rgba(255, 255, 255, .045);
        border-radius: 11px;
        background: rgba(7, 17, 31, .25);
    }

    .info-label {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #5e6e84;
        font-size: 9px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .info-label svg {
        width: 11px;
        height: 11px;
    }

    .info-value {
        margin-top: 7px;
        color: #e2e8f0;
        font-size: 14px;
        font-weight: 700;
    }

    .info-value.highlight {
        color: #58d0ff;
        font-size: 21px;
    }

    /* =========================================================
       CONTENIDO
    ========================================================== */

    .content-section {
        margin-top: 22px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, .05);
    }

    .content-title {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #8391a5;
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .content-title svg {
        width: 13px;
        height: 13px;
        color: #35c6ff;
    }

    .content-text {
        margin-top: 9px;
        color: #aab5c4;
        font-size: 12px;
        line-height: 1.75;
        white-space: pre-line;
    }

    /* =========================================================
       CLIENTES
    ========================================================== */

    .clients-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-top: 22px;
        padding: 18px;
        border: 1px dashed rgba(53, 198, 255, .13);
        border-radius: 12px;
        background: rgba(53, 198, 255, .025);
    }

    .clients-preview-content {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .clients-preview-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border: 1px solid rgba(53, 198, 255, .14);
        border-radius: 10px;
        background: rgba(53, 198, 255, .055);
        color: #51cdff;
    }

    .clients-preview-icon svg {
        width: 17px;
        height: 17px;
    }

    .clients-preview-title {
        color: #c9d4e0;
        font-size: 12px;
        font-weight: 750;
    }

    .clients-preview-text {
        margin-top: 5px;
        color: #627188;
        font-size: 10px;
        line-height: 1.6;
    }

    .clients-preview-text strong {
        color: #67d5ff;
    }

    .clients-preview-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 37px;
        padding: 0 13px;
        flex-shrink: 0;
        border: 1px solid rgba(52, 211, 153, .18);
        border-radius: 9px;
        background: rgba(52, 211, 153, .07);
        color: #6ee7b7;
        font-size: 9px;
        font-weight: 750;
        text-decoration: none;
        transition: .2s ease;
    }

    .clients-preview-btn:hover {
        border-color: rgba(52, 211, 153, .35);
        background: rgba(52, 211, 153, .12);
        color: #a7f3d0;
    }

    .clients-preview-btn svg {
        width: 12px;
        height: 12px;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 850px) {
        .show-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .show-actions {
            width: 100%;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .show-title {
            font-size: 26px;
        }

        .show-actions {
            flex-direction: column;
        }

        .show-actions form {
            width: 100%;
        }

        .action-btn {
            width: 100%;
            box-sizing: border-box;
        }

        .clients-preview {
            flex-direction: column;
            align-items: flex-start;
        }

        .clients-preview-btn {
            width: 100%;
            box-sizing: border-box;
        }
    }
</style>


<div class="campaign-show-page">

    {{-- =========================================================
         ALERTA
    ========================================================== --}}

    @if(session('success'))

        <div class="show-alert">

            <i data-lucide="circle-check"></i>

            {{ session('success') }}

        </div>

    @endif


    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="show-header">

        <div>

            <div class="show-eyebrow">
                Detalle de campaña
            </div>

            <h1 class="show-title">
                {{ $campana->nombre }}
            </h1>

            <div class="show-id">
                CAMPAÑA #{{ $campana->id_campana }}
            </div>

        </div>


        <div class="show-actions">

            {{-- VOLVER --}}

            <a
                href="{{ route('campanas.index') }}"
                class="action-btn back"
            >
                <i data-lucide="arrow-left"></i>
                Volver
            </a>


            {{-- ADMINISTRAR CLIENTES --}}

            @if(
                Route::has('campanas.clientes') &&
                auth()->check() &&
                auth()->user()->id_rol == 2
            )

                <a
                    href="{{ route(
                        'campanas.clientes',
                        $campana->id_campana
                    ) }}"
                    class="action-btn clients"
                >
                    <i data-lucide="users-round"></i>
                    Administrar clientes
                </a>

            @endif


            {{-- EDITAR --}}

            @if(
                Route::has('campanas.edit') &&
                auth()->check() &&
                auth()->user()->id_rol == 2
            )

                <a
                    href="{{ route(
                        'campanas.edit',
                        $campana->id_campana
                    ) }}"
                    class="action-btn edit"
                >
                    <i data-lucide="pencil"></i>
                    Editar
                </a>

            @endif


            {{-- ELIMINAR --}}

            @if(
                Route::has('campanas.destroy') &&
                auth()->check() &&
                auth()->user()->id_rol == 2
            )

                <form
                    action="{{ route(
                        'campanas.destroy',
                        $campana->id_campana
                    ) }}"
                    method="POST"
                    class="delete-form"
                    onsubmit="return confirm(
                        '¿Seguro que deseas eliminar esta campaña?'
                    );"
                >

                    @csrf
                    @method('DELETE')


                    <button
                        type="submit"
                        class="action-btn delete"
                    >
                        <i data-lucide="trash-2"></i>
                        Eliminar
                    </button>

                </form>

            @endif

        </div>

    </div>


    {{-- =========================================================
         TARJETA
    ========================================================== --}}

    <div class="show-card">

        <div class="show-main">

            {{-- ESTADO --}}

            <div class="show-top">

                <div class="status-group">

                    <span
                        class="status-badge {{ $situacionClase }}"
                    >
                        {{ $situacion }}
                    </span>


                    <span class="status-badge system">

                        {{ $campana->estadoCampana->nombre
                            ?? 'Sin estado'
                        }}

                    </span>

                </div>

            </div>


            {{-- =================================================
                 DATOS PRINCIPALES
            ================================================== --}}

            <div class="info-grid">

                <div class="info-card">

                    <div class="info-label">

                        <i data-lucide="calendar-days"></i>

                        Fecha de inicio

                    </div>

                    <div class="info-value">
                        {{ $inicio->format('d/m/Y') }}
                    </div>

                </div>


                <div class="info-card">

                    <div class="info-label">

                        <i data-lucide="calendar-check"></i>

                        Fecha final

                    </div>

                    <div class="info-value">
                        {{ $fin->format('d/m/Y') }}
                    </div>

                </div>


                <div class="info-card">

                    <div class="info-label">

                        <i data-lucide="users-round"></i>

                        Clientes asignados

                    </div>

                    <div class="info-value highlight">

                        {{ $campana->clientes_count ?? 0 }}

                    </div>

                </div>

            </div>


            {{-- =================================================
                 DESCRIPCIÓN
            ================================================== --}}

            <div class="content-section">

                <div class="content-title">

                    <i data-lucide="file-text"></i>

                    Descripción

                </div>


                <div class="content-text">

                    {{ $campana->descripcion
                        ?: 'No se registró una descripción para esta campaña.'
                    }}

                </div>

            </div>


            {{-- =================================================
                 OBJETIVO
            ================================================== --}}

            <div class="content-section">

                <div class="content-title">

                    <i data-lucide="target"></i>

                    Objetivo

                </div>


                <div class="content-text">

                    {{ $campana->objetivo
                        ?: 'No se definió un objetivo para esta campaña.'
                    }}

                </div>

            </div>


            {{-- =================================================
                 CLIENTES
            ================================================== --}}

            <div class="clients-preview">

                <div class="clients-preview-content">

                    <div class="clients-preview-icon">

                        <i data-lucide="users-round"></i>

                    </div>


                    <div>

                        <div class="clients-preview-title">
                            Clientes de la campaña
                        </div>

                        <div class="clients-preview-text">

                            Actualmente hay

                            <strong>
                                {{ $campana->clientes_count ?? 0 }}
                            </strong>

                            clientes asignados a esta campaña.

                            @if(
                                auth()->check() &&
                                auth()->user()->id_rol == 2
                            )

                                Puedes administrar este grupo para
                                agregar o retirar clientes.

                            @endif

                        </div>

                    </div>

                </div>


                @if(
                    Route::has('campanas.clientes') &&
                    auth()->check() &&
                    auth()->user()->id_rol == 2
                )

                    <a
                        href="{{ route(
                            'campanas.clientes',
                            $campana->id_campana
                        ) }}"
                        class="clients-preview-btn"
                    >

                        <i data-lucide="users-round"></i>

                        Administrar clientes

                    </a>

                @endif

            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    }
);
</script>

@endsection