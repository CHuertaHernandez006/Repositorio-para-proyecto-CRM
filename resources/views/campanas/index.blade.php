@extends('layouts.app')

@section('title', 'Campañas - CRM')
@section('header-title', 'Gestión de Campañas')

@section('content')

@php
    $listaCampanas = $campanas ?? collect();

    $totalCampanasVista =
        $totalCampanas ?? 0;

    $campanasEnCursoVista =
        $campanasEnCurso ?? 0;

    $campanasProximasVista =
        $campanasProximas ?? 0;

    $campanasFinalizadasVista =
        $campanasFinalizadas ?? 0;

    $hoy = now()->startOfDay();
@endphp


<style>
    .campanas-page {
        max-width: 1400px;
        margin: 0 auto;
        color: #e8eef7;
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .campanas-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 28px;
    }

    .campanas-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 9px;

        color: #35c6ff;

        font-size: 11px;
        font-weight: 750;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .campanas-eyebrow-dot {
        width: 6px;
        height: 6px;

        border-radius: 50%;

        background: #35c6ff;

        box-shadow:
            0 0 10px
            rgba(53, 198, 255, .6);
    }

    .campanas-title {
        margin: 0;

        color: #ffffff;

        font-size: 32px;
        line-height: 1.15;
        font-weight: 750;

        letter-spacing: -.03em;
    }

    .campanas-description {
        max-width: 720px;

        margin: 9px 0 0;

        color: #8190a7;

        font-size: 14px;
        line-height: 1.6;
    }

    .campanas-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-new-campaign {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        min-height: 42px;

        padding: 0 17px;

        border:
            1px solid
            rgba(53, 198, 255, .35);

        border-radius: 10px;

        background: #1196ce;

        color: #ffffff;

        font-size: 12px;
        font-weight: 800;

        text-decoration: none;

        box-shadow:
            0 10px 25px
            rgba(17, 150, 206, .12);

        transition: .2s ease;
    }

    .btn-new-campaign:hover {
        background: #20a9e5;

        transform:
            translateY(-1px);

        box-shadow:
            0 14px 30px
            rgba(17, 150, 206, .18);
    }

    .btn-new-campaign svg {
        width: 16px;
        height: 16px;
    }


    /* =========================================================
       ALERTAS
    ========================================================== */

    .campaign-alert {
        display: flex;
        align-items: flex-start;

        gap: 10px;

        margin-bottom: 20px;

        padding: 13px 15px;

        border-radius: 11px;

        font-size: 12px;
        line-height: 1.6;
    }

    .campaign-alert svg {
        width: 17px;
        height: 17px;

        flex-shrink: 0;

        margin-top: 1px;
    }

    .campaign-alert.success {
        border:
            1px solid
            rgba(52, 211, 153, .16);

        background:
            rgba(52, 211, 153, .07);

        color: #6ee7b7;
    }

    .campaign-alert.error {
        border:
            1px solid
            rgba(248, 113, 113, .16);

        background:
            rgba(248, 113, 113, .06);

        color: #fca5a5;
    }

    .campaign-error-list {
        margin: 5px 0 0;

        padding-left: 18px;
    }


    /* =========================================================
       ESTADÍSTICAS
    ========================================================== */

    .campaign-stats {
        display: grid;

        grid-template-columns:
            repeat(4, 1fr);

        gap: 15px;

        margin-bottom: 24px;
    }

    .campaign-stat-card {
        position: relative;

        overflow: hidden;

        min-height: 116px;

        padding: 19px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 15px;

        background: #111c30;

        box-shadow:
            0 12px 30px
            rgba(0, 0, 0, .08);

        transition: .2s ease;
    }

    .campaign-stat-card:hover {
        border-color:
            rgba(53, 198, 255, .14);

        transform:
            translateY(-1px);
    }

    .campaign-stat-card::after {
        content: "";

        position: absolute;

        width: 95px;
        height: 95px;

        right: -42px;
        bottom: -47px;

        border-radius: 50%;

        background:
            rgba(53, 198, 255, .035);

        pointer-events: none;
    }

    .campaign-stat-top {
        display: flex;

        align-items: flex-start;

        justify-content:
            space-between;

        gap: 15px;
    }

    .campaign-stat-label {
        color: #718098;

        font-size: 10px;
        font-weight: 750;

        letter-spacing: .1em;

        text-transform: uppercase;
    }

    .campaign-stat-value {
        margin-top: 8px;

        color: #ffffff;

        font-size: 29px;
        line-height: 1;

        font-weight: 750;
    }

    .campaign-stat-hint {
        margin-top: 9px;

        color: #56657b;

        font-size: 9px;
    }

    .campaign-stat-icon {
        display: flex;

        align-items: center;

        justify-content: center;

        width: 39px;
        height: 39px;

        border:
            1px solid
            rgba(53, 198, 255, .28);

        border-radius: 10px;

        background:
            rgba(53, 198, 255, .13);

        color: #35c6ff;
    }

    .campaign-stat-icon svg {
        width: 19px;
        height: 19px;
    }

    .campaign-stat-icon.green {
        border-color:
            rgba(52, 211, 153, .25);

        background:
            rgba(52, 211, 153, .10);

        color: #34d399;
    }

    .campaign-stat-icon.purple {
        border-color:
            rgba(167, 139, 250, .25);

        background:
            rgba(167, 139, 250, .10);

        color: #a78bfa;
    }

    .campaign-stat-icon.gray {
        border-color:
            rgba(148, 163, 184, .20);

        background:
            rgba(148, 163, 184, .08);

        color: #94a3b8;
    }


    /* =========================================================
       FILTROS
    ========================================================== */

    .campaign-filters {
        margin-bottom: 26px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 16px;

        background: #111c30;

        overflow: hidden;
    }

    .campaign-filters-header {
        display: flex;

        align-items: center;

        justify-content:
            space-between;

        gap: 18px;

        padding: 17px 19px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .05);
    }

    .campaign-filters-heading {
        display: flex;

        align-items: center;

        gap: 10px;
    }

    .campaign-filters-icon {
        display: flex;

        align-items: center;

        justify-content: center;

        width: 33px;
        height: 33px;

        border:
            1px solid
            rgba(53, 198, 255, .15);

        border-radius: 9px;

        background:
            rgba(53, 198, 255, .07);

        color: #35c6ff;
    }

    .campaign-filters-icon svg {
        width: 15px;
        height: 15px;
    }

    .campaign-filters-title {
        color: #f1f5f9;

        font-size: 13px;

        font-weight: 750;
    }

    .campaign-filters-subtitle {
        margin-top: 2px;

        color: #607087;

        font-size: 10px;
    }

    .campaign-results {
        color: #718198;

        font-size: 10px;
    }

    .campaign-results strong {
        color: #c1ccda;
    }

    .campaign-filters-form {
        display: grid;

        grid-template-columns:
            minmax(260px, 1.7fr)
            minmax(170px, .8fr)
            minmax(180px, .9fr)
            auto;

        gap: 11px;

        align-items: end;

        padding: 17px 19px;
    }

    .filter-group {
        min-width: 0;
    }

    .filter-label {
        display: block;

        margin-bottom: 6px;

        color: #63738a;

        font-size: 9px;
        font-weight: 750;

        letter-spacing: .08em;

        text-transform: uppercase;
    }

    .filter-control {
        width: 100%;
        height: 40px;

        padding: 0 11px;

        border:
            1px solid
            rgba(255, 255, 255, .075);

        border-radius: 9px;

        outline: none;

        background: #0b1628;

        color: #dce7f5;

        font-size: 11px;

        box-sizing: border-box;

        color-scheme: dark;

        transition: .2s ease;
    }

    .filter-control:focus {
        border-color:
            rgba(53, 198, 255, .38);

        box-shadow:
            0 0 0 3px
            rgba(53, 198, 255, .045);
    }

    .filter-control option {
        background: #0e192b;

        color: #dce7f5;
    }

    .campaign-search {
        position: relative;
    }

    .campaign-search svg {
        position: absolute;

        top: 50%;
        left: 12px;

        width: 14px;
        height: 14px;

        color: #576780;

        transform:
            translateY(-50%);

        pointer-events: none;
    }

    .campaign-search input {
        padding-left: 36px;
    }

    .filter-actions {
        display: flex;

        align-items: center;

        gap: 8px;
    }

    .btn-filter,
    .btn-clear-filter {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        height: 40px;

        padding: 0 14px;

        border-radius: 9px;

        font-size: 10px;
        font-weight: 750;

        text-decoration: none;

        cursor: pointer;

        transition: .2s ease;

        white-space: nowrap;
    }

    .btn-filter {
        border:
            1px solid
            rgba(53, 198, 255, .25);

        background:
            rgba(53, 198, 255, .10);

        color: #61d2ff;
    }

    .btn-filter:hover {
        background:
            rgba(53, 198, 255, .17);
    }

    .btn-clear-filter {
        border:
            1px solid
            rgba(148, 163, 184, .13);

        background:
            rgba(148, 163, 184, .05);

        color: #8d9bad;
    }

    .btn-clear-filter:hover {
        background:
            rgba(148, 163, 184, .10);

        color: #cbd5e1;
    }

    .btn-filter svg,
    .btn-clear-filter svg {
        width: 13px;
        height: 13px;
    }


    /* =========================================================
       LISTADO
    ========================================================== */

    .campaign-section-header {
        display: flex;

        align-items: flex-end;

        justify-content:
            space-between;

        gap: 20px;

        margin:
            31px 0 16px;
    }

    .campaign-section-title {
        margin: 0;

        color: #ffffff;

        font-size: 17px;

        font-weight: 750;
    }

    .campaign-section-description {
        margin: 5px 0 0;

        color: #687890;

        font-size: 12px;
    }

    .campaign-section-counter {
        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 7px 10px;

        border:
            1px solid
            rgba(53, 198, 255, .12);

        border-radius: 8px;

        background:
            rgba(53, 198, 255, .05);

        color: #67d5ff;

        font-size: 10px;

        font-weight: 700;
    }

    .campaign-section-counter svg {
        width: 13px;
        height: 13px;
    }


    /* =========================================================
       TARJETAS
    ========================================================== */

    .campaign-grid {
        display: grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap: 16px;
    }

    .campaign-card {
        position: relative;

        overflow: hidden;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 16px;

        background: #111c30;

        box-shadow:
            0 18px 40px
            rgba(0, 0, 0, .08);

        transition: .2s ease;
    }

    .campaign-card:hover {
        border-color:
            rgba(53, 198, 255, .13);

        transform:
            translateY(-2px);
    }

    .campaign-card::before {
        content: "";

        position: absolute;

        top: 0;
        left: 0;

        width: 3px;
        height: 100%;

        opacity: .65;
    }

    .campaign-card.active::before {
        background: #34d399;
    }

    .campaign-card.upcoming::before {
        background: #a78bfa;
    }

    .campaign-card.finished::before {
        background: #64748b;
    }

    .campaign-card-main {
        padding: 20px 21px;
    }

    .campaign-card-header {
        display: flex;

        align-items: flex-start;

        justify-content:
            space-between;

        gap: 16px;
    }

    .campaign-card-title-wrap {
        display: flex;

        gap: 12px;

        min-width: 0;
    }

    .campaign-icon {
        display: flex;

        align-items: center;

        justify-content: center;

        width: 43px;
        height: 43px;

        flex: 0 0 43px;

        border:
            1px solid
            rgba(53, 198, 255, .17);

        border-radius: 11px;

        background:
            rgba(53, 198, 255, .07);

        color: #4ccaff;
    }

    .campaign-icon svg {
        width: 19px;
        height: 19px;
    }

    .campaign-name {
        margin: 0;

        color: #f4f7fb;

        font-size: 15px;

        font-weight: 750;

        line-height: 1.35;
    }

    .campaign-id {
        margin-top: 4px;

        color: #5d6d84;

        font-size: 9px;

        font-weight: 650;
    }


    /* =========================================================
       BADGES
    ========================================================== */

    .campaign-badges {
        display: flex;

        justify-content:
            flex-end;

        flex-wrap: wrap;

        gap: 6px;
    }

    .campaign-badge {
        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 6px 9px;

        border-radius: 30px;

        font-size: 9px;

        font-weight: 750;

        white-space: nowrap;
    }

    .campaign-badge .dot {
        width: 5px;
        height: 5px;

        border-radius: 50%;
    }

    .campaign-badge.active {
        border:
            1px solid
            rgba(52, 211, 153, .17);

        background:
            rgba(52, 211, 153, .07);

        color: #6ee7b7;
    }

    .campaign-badge.active .dot {
        background: #34d399;
    }

    .campaign-badge.upcoming {
        border:
            1px solid
            rgba(167, 139, 250, .17);

        background:
            rgba(167, 139, 250, .07);

        color: #c4b5fd;
    }

    .campaign-badge.upcoming .dot {
        background: #a78bfa;
    }

    .campaign-badge.finished {
        border:
            1px solid
            rgba(148, 163, 184, .13);

        background:
            rgba(148, 163, 184, .05);

        color: #8997aa;
    }

    .campaign-badge.finished .dot {
        background: #64748b;
    }

    .campaign-state-badge {
        display: inline-flex;

        align-items: center;

        padding: 5px 8px;

        border:
            1px solid
            rgba(53, 198, 255, .11);

        border-radius: 7px;

        background:
            rgba(53, 198, 255, .045);

        color: #7fdcff;

        font-size: 9px;

        font-weight: 700;

        white-space: nowrap;
    }


    /* =========================================================
       CONTENIDO
    ========================================================== */

    .campaign-description {
        min-height: 44px;

        margin: 17px 0 0;

        color: #7c8ba0;

        font-size: 11px;

        line-height: 1.65;
    }

    .campaign-objective-box {
        margin-top: 15px;

        padding: 13px 14px;

        border:
            1px solid
            rgba(255, 255, 255, .045);

        border-radius: 10px;

        background:
            rgba(7, 17, 31, .27);
    }

    .campaign-objective-label {
        display: flex;

        align-items: center;

        gap: 6px;

        color: #66758b;

        font-size: 9px;

        font-weight: 750;

        letter-spacing: .08em;

        text-transform: uppercase;
    }

    .campaign-objective-label svg {
        width: 12px;
        height: 12px;

        color: #4b5c73;
    }

    .campaign-objective-text {
        margin-top: 6px;

        color: #a8b4c4;

        font-size: 10px;

        line-height: 1.6;
    }

    .campaign-meta {
        display: grid;

        grid-template-columns:
            repeat(3, 1fr);

        gap: 10px;

        margin-top: 15px;
    }

    .campaign-meta-item {
        min-width: 0;

        padding: 11px 12px;

        border:
            1px solid
            rgba(255, 255, 255, .04);

        border-radius: 9px;

        background:
            rgba(255, 255, 255, .018);
    }

    .campaign-meta-label {
        display: flex;

        align-items: center;

        gap: 5px;

        color: #58687e;

        font-size: 8px;

        font-weight: 750;

        letter-spacing: .07em;

        text-transform: uppercase;
    }

    .campaign-meta-label svg {
        width: 11px;
        height: 11px;
    }

    .campaign-meta-value {
        margin-top: 5px;

        color: #c5cfdb;

        font-size: 10px;

        font-weight: 650;
    }

    .campaign-clients-value {
        color: #5dd2ff;

        font-size: 15px;

        font-weight: 800;
    }


    /* =========================================================
       LISTA DE CLIENTES POR CAMPAÑA
    ========================================================== */

    .campaign-client-list-box {
        margin-top: 15px;

        border:
            1px solid
            rgba(255, 255, 255, .045);

        border-radius: 11px;

        background:
            rgba(7, 17, 31, .24);

        overflow: hidden;
    }

    .campaign-client-list-header {
        display: flex;

        align-items: center;

        justify-content:
            space-between;

        gap: 12px;

        padding: 11px 13px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .045);
    }

    .campaign-client-list-title {
        display: flex;

        align-items: center;

        gap: 7px;

        color: #8a99ad;

        font-size: 9px;

        font-weight: 750;

        letter-spacing: .07em;

        text-transform: uppercase;
    }

    .campaign-client-list-title svg {
        width: 12px;
        height: 12px;

        color: #35c6ff;
    }

    .campaign-client-list-count {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 23px;

        height: 21px;

        padding: 0 7px;

        border:
            1px solid
            rgba(53, 198, 255, .12);

        border-radius: 6px;

        background:
            rgba(53, 198, 255, .05);

        color: #62d2ff;

        font-size: 9px;

        font-weight: 750;
    }

    .campaign-client-list {
        display: flex;

        flex-direction: column;

        max-height: 180px;

        overflow-y: auto;
    }

    .campaign-client-list::-webkit-scrollbar {
        width: 5px;
    }

    .campaign-client-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .campaign-client-list::-webkit-scrollbar-thumb {
        background:
            rgba(148, 163, 184, .15);

        border-radius: 10px;
    }

    .campaign-client-row {
        display: flex;

        align-items: center;

        gap: 10px;

        padding: 10px 13px;

        border-bottom:
            1px solid
            rgba(255, 255, 255, .035);

        transition: .15s ease;
    }

    .campaign-client-row:hover {
        background:
            rgba(53, 198, 255, .025);
    }

    .campaign-client-row:last-child {
        border-bottom: 0;
    }

    .campaign-client-avatar {
        display: flex;

        align-items: center;

        justify-content: center;

        width: 30px;
        height: 30px;

        flex: 0 0 30px;

        border:
            1px solid
            rgba(53, 198, 255, .13);

        border-radius: 8px;

        background:
            rgba(53, 198, 255, .06);

        color: #61d2ff;

        font-size: 9px;

        font-weight: 800;
    }

    .campaign-client-info {
        flex: 1;

        min-width: 0;
    }

    .campaign-client-name {
        color: #cbd5e1;

        font-size: 10px;

        font-weight: 700;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }

    .campaign-client-contact {
        margin-top: 3px;

        color: #5f6f85;

        font-size: 8px;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }

    .campaign-client-phone {
        display: flex;

        align-items: center;

        gap: 4px;

        margin-top: 3px;

        color: #53637a;

        font-size: 8px;
    }

    .campaign-client-phone svg {
        width: 9px;
        height: 9px;
    }

    .campaign-client-empty {
        display: flex;

        align-items: center;

        gap: 8px;

        padding: 14px 13px;

        color: #63738a;

        font-size: 9px;
    }

    .campaign-client-empty svg {
        width: 13px;
        height: 13px;

        flex-shrink: 0;
    }


    /* =========================================================
       ACCIONES
    ========================================================== */

    .campaign-actions {
        display: flex;

        align-items: center;

        gap: 8px;

        margin-top: 17px;

        padding-top: 15px;

        border-top:
            1px solid
            rgba(255, 255, 255, .045);
    }

    .campaign-btn-primary,
    .campaign-btn-clients,
    .campaign-btn-secondary,
    .campaign-btn-danger {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        height: 36px;

        padding: 0 12px;

        border-radius: 9px;

        font-size: 10px;

        font-weight: 750;

        text-decoration: none;

        cursor: pointer;

        transition: .2s ease;

        white-space: nowrap;

        box-sizing: border-box;
    }


    /* VER */

    .campaign-btn-primary {
        flex: 1;

        border:
            1px solid
            rgba(53, 198, 255, .20);

        background:
            rgba(53, 198, 255, .08);

        color: #56ceff;
    }

    .campaign-btn-primary:hover {
        border-color:
            rgba(53, 198, 255, .42);

        background:
            rgba(53, 198, 255, .14);

        color: #8dddff;
    }


    /* CLIENTES */

    .campaign-btn-clients {
        border:
            1px solid
            rgba(52, 211, 153, .30);

        background:
            rgba(52, 211, 153, .10);

        color: #6ee7b7;
    }

    .campaign-btn-clients:hover {
        border-color:
            rgba(52, 211, 153, .50);

        background:
            rgba(52, 211, 153, .18);

        color: #a7f3d0;

        transform:
            translateY(-1px);
    }


    /* EDITAR */

    .campaign-btn-secondary {
        border:
            1px solid
            rgba(148, 163, 184, .13);

        background:
            rgba(148, 163, 184, .05);

        color: #94a3b8;
    }

    .campaign-btn-secondary:hover {
        border-color:
            rgba(148, 163, 184, .28);

        background:
            rgba(148, 163, 184, .10);

        color: #d1d9e5;
    }


    /* ELIMINAR */

    .campaign-btn-danger {
        width: 36px;

        padding: 0;

        border:
            1px solid
            rgba(248, 113, 113, .13);

        background:
            rgba(248, 113, 113, .045);

        color: #cf7c7c;
    }

    .campaign-btn-danger:hover {
        border-color:
            rgba(248, 113, 113, .32);

        background:
            rgba(248, 113, 113, .10);

        color: #fca5a5;
    }

    .campaign-btn-primary svg,
    .campaign-btn-clients svg,
    .campaign-btn-secondary svg,
    .campaign-btn-danger svg {
        width: 13px;
        height: 13px;
    }

    .delete-form {
        margin: 0;
    }


    /* =========================================================
       VACÍO
    ========================================================== */

    .campaign-empty {
        padding: 70px 25px;

        border:
            1px solid
            rgba(255, 255, 255, .055);

        border-radius: 16px;

        background: #111c30;

        text-align: center;
    }

    .campaign-empty-icon {
        display: flex;

        align-items: center;

        justify-content: center;

        width: 62px;
        height: 62px;

        margin: 0 auto;

        border:
            1px solid
            rgba(53, 198, 255, .14);

        border-radius: 16px;

        background:
            rgba(53, 198, 255, .055);

        color: #35c6ff;
    }

    .campaign-empty-icon svg {
        width: 27px;
        height: 27px;
    }

    .campaign-empty h3 {
        margin: 17px 0 0;

        color: #ffffff;

        font-size: 16px;
    }

    .campaign-empty p {
        max-width: 460px;

        margin: 7px auto 0;

        color: #65748b;

        font-size: 12px;

        line-height: 1.7;
    }

    .campaign-empty-action {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        min-height: 38px;

        margin-top: 17px;

        padding: 0 14px;

        border:
            1px solid
            rgba(53, 198, 255, .23);

        border-radius: 9px;

        background:
            rgba(53, 198, 255, .08);

        color: #5ed0ff;

        font-size: 10px;

        font-weight: 750;

        text-decoration: none;
    }

    .campaign-pagination {
        margin-top: 23px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 1100px) {

        .campaign-stats {
            grid-template-columns:
                repeat(2, 1fr);
        }

        .campaign-filters-form {
            grid-template-columns:
                1fr 1fr;
        }

        .filter-actions {
            grid-column:
                1 / -1;
        }

        .campaign-actions {
            flex-wrap: wrap;
        }

        .campaign-btn-primary {
            flex-basis: 100%;
        }
    }


    @media (max-width: 850px) {

        .campanas-header,
        .campaign-section-header {
            align-items:
                flex-start;

            flex-direction:
                column;
        }

        .campanas-header-actions,
        .btn-new-campaign {
            width: 100%;
        }

        .campaign-grid {
            grid-template-columns:
                1fr;
        }
    }


    @media (max-width: 620px) {

        .campanas-title {
            font-size: 27px;
        }

        .campaign-stats {
            grid-template-columns:
                1fr;
        }

        .campaign-filters-header {
            align-items:
                flex-start;

            flex-direction:
                column;
        }

        .campaign-filters-form {
            grid-template-columns:
                1fr;
        }

        .filter-actions {
            grid-column: 1;
        }

        .btn-filter,
        .btn-clear-filter {
            flex: 1;
        }

        .campaign-card-header {
            flex-direction:
                column;
        }

        .campaign-badges {
            justify-content:
                flex-start;
        }

        .campaign-meta {
            grid-template-columns:
                1fr;
        }

        .campaign-actions {
            display: grid;

            grid-template-columns:
                1fr 1fr;
        }

        .campaign-btn-primary {
            grid-column:
                1 / -1;

            width: 100%;
        }

        .campaign-btn-clients,
        .campaign-btn-secondary {
            width: 100%;
        }

        .delete-form {
            grid-column:
                1 / -1;
        }

        .campaign-btn-danger {
            width: 100%;
        }
    }
</style>


<div class="campanas-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="campanas-header">

        <div>

            <div class="campanas-eyebrow">

                <span class="campanas-eyebrow-dot"></span>

                Gestión comercial

            </div>


            <h1 class="campanas-title">
                Campañas
            </h1>


            <p class="campanas-description">

                Organiza las campañas comerciales,
                consulta sus periodos de operación y
                administra los clientes relacionados
                con cada estrategia.

            </p>

        </div>


        <div class="campanas-header-actions">

            @if(auth()->user()->id_rol != 3 && Route::has('campanas.create'))

                <a
                    href="{{ route('campanas.create') }}"
                    class="btn-new-campaign"
                >

                    <i data-lucide="plus"></i>

                    Nueva campaña

                </a>

            @endif

        </div>

    </div>


    {{-- =========================================================
         ALERTAS
    ========================================================== --}}

    @if(session('success'))

        <div class="campaign-alert success">

            <i data-lucide="circle-check"></i>

            <div>
                {{ session('success') }}
            </div>

        </div>

    @endif


    @if($errors->any())

        <div class="campaign-alert error">

            <i data-lucide="triangle-alert"></i>

            <div>

                <strong>
                    Ocurrió un problema.
                </strong>

                <ul class="campaign-error-list">

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
         ESTADÍSTICAS
    ========================================================== --}}

    <div class="campaign-stats">

        {{-- TOTAL --}}

        <div class="campaign-stat-card">

            <div class="campaign-stat-top">

                <div>

                    <div class="campaign-stat-label">
                        Total de campañas
                    </div>

                    <div class="campaign-stat-value">
                        {{ $totalCampanasVista }}
                    </div>

                    <div class="campaign-stat-hint">
                        Campañas registradas
                    </div>

                </div>


                <div class="campaign-stat-icon">

                    <i data-lucide="megaphone"></i>

                </div>

            </div>

        </div>


        {{-- EN CURSO --}}

        <div class="campaign-stat-card">

            <div class="campaign-stat-top">

                <div>

                    <div class="campaign-stat-label">
                        En curso
                    </div>

                    <div class="campaign-stat-value">
                        {{ $campanasEnCursoVista }}
                    </div>

                    <div class="campaign-stat-hint">
                        Dentro de su periodo
                    </div>

                </div>


                <div class="campaign-stat-icon green">

                    <i data-lucide="radio-tower"></i>

                </div>

            </div>

        </div>


        {{-- PRÓXIMAS --}}

        <div class="campaign-stat-card">

            <div class="campaign-stat-top">

                <div>

                    <div class="campaign-stat-label">
                        Próximas
                    </div>

                    <div class="campaign-stat-value">
                        {{ $campanasProximasVista }}
                    </div>

                    <div class="campaign-stat-hint">
                        Aún no comienzan
                    </div>

                </div>


                <div class="campaign-stat-icon purple">

                    <i data-lucide="calendar-clock"></i>

                </div>

            </div>

        </div>


        {{-- FINALIZADAS --}}

        <div class="campaign-stat-card">

            <div class="campaign-stat-top">

                <div>

                    <div class="campaign-stat-label">
                        Finalizadas
                    </div>

                    <div class="campaign-stat-value">
                        {{ $campanasFinalizadasVista }}
                    </div>

                    <div class="campaign-stat-hint">
                        Periodo concluido
                    </div>

                </div>


                <div class="campaign-stat-icon gray">

                    <i data-lucide="archive"></i>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         FILTROS
    ========================================================== --}}

    <div class="campaign-filters">

        <div class="campaign-filters-header">

            <div class="campaign-filters-heading">

                <div class="campaign-filters-icon">

                    <i data-lucide="list-filter"></i>

                </div>


                <div>

                    <div class="campaign-filters-title">
                        Buscar y organizar campañas
                    </div>

                    <div class="campaign-filters-subtitle">
                        Filtra por estado o cambia el orden del listado.
                    </div>

                </div>

            </div>


            <div class="campaign-results">

                @if(method_exists($campanas, 'total'))

                    Mostrando

                    <strong>
                        {{ $campanas->count() }}
                    </strong>

                    de

                    <strong>
                        {{ $campanas->total() }}
                    </strong>

                @endif

            </div>

        </div>


        <form
            action="{{ route('campanas.index') }}"
            method="GET"
            class="campaign-filters-form"
        >

            {{-- BUSCAR --}}

            <div class="filter-group">

                <label class="filter-label">
                    Buscar campaña
                </label>

                <div class="campaign-search">

                    <i data-lucide="search"></i>

                    <input
                        type="text"
                        name="buscar"
                        class="filter-control"
                        value="{{ request('buscar') }}"
                        placeholder="Nombre, descripción u objetivo..."
                    >

                </div>

            </div>


            {{-- ESTADO --}}

            <div class="filter-group">

                <label class="filter-label">
                    Estado
                </label>

                <select
                    name="estado"
                    class="filter-control"
                >

                    <option value="">
                        Todos los estados
                    </option>


                    @foreach($estados ?? [] as $estado)

                        <option
                            value="{{ $estado->id_estado_campana }}"
                            {{
                                (string) request('estado') ===
                                (string) $estado->id_estado_campana
                                    ? 'selected'
                                    : ''
                            }}
                        >

                            {{ $estado->nombre }}

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- ORDEN --}}

            <div class="filter-group">

                <label class="filter-label">
                    Ordenar por
                </label>

                <select
                    name="orden"
                    class="filter-control"
                >

                    <option
                        value="nombre_asc"
                        {{
                            request(
                                'orden',
                                'nombre_asc'
                            ) === 'nombre_asc'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Nombre A - Z
                    </option>


                    <option
                        value="nombre_desc"
                        {{
                            request('orden') ===
                            'nombre_desc'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Nombre Z - A
                    </option>


                    <option
                        value="inicio_asc"
                        {{
                            request('orden') ===
                            'inicio_asc'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Inicio más próximo
                    </option>


                    <option
                        value="inicio_desc"
                        {{
                            request('orden') ===
                            'inicio_desc'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Inicio más lejano
                    </option>


                    <option
                        value="fin_asc"
                        {{
                            request('orden') ===
                            'fin_asc'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Finaliza primero
                    </option>


                    <option
                        value="fin_desc"
                        {{
                            request('orden') ===
                            'fin_desc'
                                ? 'selected'
                                : ''
                        }}
                    >
                        Finaliza después
                    </option>

                </select>

            </div>


            {{-- BOTONES FILTRO --}}

            <div class="filter-actions">

                <button
                    type="submit"
                    class="btn-filter"
                >

                    <i data-lucide="search"></i>

                    Aplicar

                </button>


                @if(
                    request()->filled('buscar') ||
                    request()->filled('estado') ||
                    request()->filled('orden')
                )

                    <a
                        href="{{ route('campanas.index') }}"
                        class="btn-clear-filter"
                    >

                        <i data-lucide="rotate-ccw"></i>

                        Limpiar

                    </a>

                @endif

            </div>

        </form>

    </div>


    {{-- =========================================================
         CABECERA LISTADO
    ========================================================== --}}

    <div class="campaign-section-header">

        <div>

            <h2 class="campaign-section-title">
                Campañas registradas
            </h2>

            <p class="campaign-section-description">
                Consulta la información general,
                los clientes relacionados y administra cada campaña.
            </p>

        </div>


        <div class="campaign-section-counter">

            <i data-lucide="megaphone"></i>


            @if(method_exists($campanas, 'total'))

                {{ $campanas->total() }}

            @else

                {{ $listaCampanas->count() }}

            @endif


            campañas

        </div>

    </div>


    {{-- =========================================================
         CAMPAÑAS
    ========================================================== --}}

    @if($listaCampanas->count() > 0)

        <div class="campaign-grid">

            @foreach($listaCampanas as $campana)

                @php
                    $fechaInicio =
                        \Carbon\Carbon::parse(
                            $campana->fecha_inicio
                        )->startOfDay();


                    $fechaFin =
                        \Carbon\Carbon::parse(
                            $campana->fecha_fin
                        )->startOfDay();


                    $tipoTemporal = 'active';

                    $textoTemporal = 'En curso';


                    if ($fechaInicio->gt($hoy)) {

                        $tipoTemporal =
                            'upcoming';

                        $textoTemporal =
                            'Próxima';

                    } elseif ($fechaFin->lt($hoy)) {

                        $tipoTemporal =
                            'finished';

                        $textoTemporal =
                            'Finalizada';
                    }


                    $estadoNombre =
                        $campana
                            ->estadoCampana
                            ->nombre
                        ?? 'Sin estado';


                    $clientesCount =
                        $campana
                            ->clientes_count
                        ?? 0;
                @endphp


                <article
                    class="campaign-card {{ $tipoTemporal }}"
                >

                    <div class="campaign-card-main">


                        {{-- =================================================
                             HEADER
                        ================================================== --}}

                        <div class="campaign-card-header">

                            <div class="campaign-card-title-wrap">

                                <div class="campaign-icon">

                                    <i data-lucide="megaphone"></i>

                                </div>


                                <div style="min-width: 0;">

                                    <h3 class="campaign-name">
                                        {{ $campana->nombre }}
                                    </h3>

                                    <div class="campaign-id">

                                        CAMPAÑA
                                        #{{ $campana->id_campana }}

                                    </div>

                                </div>

                            </div>


                            <div class="campaign-badges">

                                <span
                                    class="campaign-badge {{ $tipoTemporal }}"
                                >

                                    <span class="dot"></span>

                                    {{ $textoTemporal }}

                                </span>


                                <span class="campaign-state-badge">

                                    {{ $estadoNombre }}

                                </span>

                            </div>

                        </div>


                        {{-- =================================================
                             DESCRIPCIÓN
                        ================================================== --}}

                        <div class="campaign-description">

                            @if($campana->descripcion)

                                {{
                                    \Illuminate\Support\Str::limit(
                                        $campana->descripcion,
                                        155
                                    )
                                }}

                            @else

                                Esta campaña no tiene
                                una descripción registrada.

                            @endif

                        </div>


                        {{-- =================================================
                             OBJETIVO
                        ================================================== --}}

                        <div class="campaign-objective-box">

                            <div class="campaign-objective-label">

                                <i data-lucide="target"></i>

                                Objetivo

                            </div>


                            <div class="campaign-objective-text">

                                @if($campana->objetivo)

                                    {{
                                        \Illuminate\Support\Str::limit(
                                            $campana->objetivo,
                                            170
                                        )
                                    }}

                                @else

                                    No se ha definido un objetivo
                                    para esta campaña.

                                @endif

                            </div>

                        </div>


                        {{-- =================================================
                             DATOS
                        ================================================== --}}

                        <div class="campaign-meta">

                            {{-- INICIO --}}

                            <div class="campaign-meta-item">

                                <div class="campaign-meta-label">

                                    <i data-lucide="calendar-days"></i>

                                    Inicio

                                </div>


                                <div class="campaign-meta-value">

                                    {{
                                        $fechaInicio
                                            ->format('d/m/Y')
                                    }}

                                </div>

                            </div>


                            {{-- FINAL --}}

                            <div class="campaign-meta-item">

                                <div class="campaign-meta-label">

                                    <i data-lucide="calendar-check"></i>

                                    Finaliza

                                </div>


                                <div class="campaign-meta-value">

                                    {{
                                        $fechaFin
                                            ->format('d/m/Y')
                                    }}

                                </div>

                            </div>


                            {{-- CLIENTES --}}

                            <div class="campaign-meta-item">

                                <div class="campaign-meta-label">

                                    <i data-lucide="users-round"></i>

                                    Clientes

                                </div>


                                <div
                                    class="
                                        campaign-meta-value
                                        campaign-clients-value
                                    "
                                >

                                    {{ $clientesCount }}

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                             LISTA DE CLIENTES DE LA CAMPAÑA
                        ================================================== --}}

                        <div class="campaign-client-list-box">


                            <div class="campaign-client-list-header">

                                <div class="campaign-client-list-title">

                                    <i data-lucide="users-round"></i>

                                    Clientes de esta campaña

                                </div>


                                <div class="campaign-client-list-count">

                                    {{
                                        $campana
                                            ->clientes
                                            ->count()
                                    }}

                                </div>

                            </div>


                            @if(
                                $campana
                                    ->clientes
                                    ->count() > 0
                            )

                                <div class="campaign-client-list">

                                    @foreach(
                                        $campana->clientes
                                        as $cliente
                                    )

                                        @php
                                            $nombreCompleto =
                                                trim(
                                                    $cliente
                                                        ->nombre_completo
                                                    ?: (
                                                        ($cliente->nombre ?? '')
                                                        . ' ' .
                                                        ($cliente->apellido_paterno ?? '')
                                                        . ' ' .
                                                        ($cliente->apellido_materno ?? '')
                                                    )
                                                );


                                            $inicial =
                                                mb_strtoupper(
                                                    mb_substr(
                                                        $cliente->nombre
                                                            ?? 'C',
                                                        0,
                                                        1
                                                    )
                                                );
                                        @endphp


                                        <div class="campaign-client-row">

                                            {{-- AVATAR --}}

                                            <div class="campaign-client-avatar">

                                                {{ $inicial }}

                                            </div>


                                            {{-- INFORMACIÓN --}}

                                            <div class="campaign-client-info">

                                                <div class="campaign-client-name">

                                                    {{ $nombreCompleto }}

                                                </div>


                                                <div class="campaign-client-contact">

                                                    @if($cliente->empresa)

                                                        {{ $cliente->empresa }}

                                                    @elseif($cliente->correo)

                                                        {{ $cliente->correo }}

                                                    @elseif(
                                                        $cliente
                                                            ->telefono_principal
                                                    )

                                                        {{
                                                            $cliente
                                                                ->telefono_principal
                                                        }}

                                                    @else

                                                        Sin información adicional

                                                    @endif

                                                </div>


                                                @if(
                                                    $cliente->empresa &&
                                                    $cliente->telefono_principal
                                                )

                                                    <div class="campaign-client-phone">

                                                        <i data-lucide="phone"></i>

                                                        {{
                                                            $cliente
                                                                ->telefono_principal
                                                        }}

                                                    </div>

                                                @endif

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @else

                                <div class="campaign-client-empty">

                                    <i data-lucide="user-round-x"></i>

                                    Esta campaña todavía
                                    no tiene clientes asignados.

                                </div>

                            @endif

                        </div>


                        {{-- =================================================
                             ACCIONES
                        ================================================== --}}

                        <div class="campaign-actions">


                            {{-- VER CAMPAÑA --}}

                            @if(Route::has('campanas.show'))

                                <a
                                    href="{{
                                        route(
                                            'campanas.show',
                                            $campana->id_campana
                                        )
                                    }}"
                                    class="campaign-btn-primary"
                                >

                                    <i data-lucide="eye"></i>

                                    Ver campaña

                                </a>

                            @endif


                            @if(auth()->user()->id_rol != 3)

                                {{-- ADMINISTRAR CLIENTES --}}

                                <a
                                    href="{{
                                        route(
                                            'campanas.clientes',
                                            $campana->id_campana
                                        )
                                    }}"
                                    class="campaign-btn-clients"
                                    title="
                                        Agregar o retirar
                                        clientes de esta campaña
                                    "
                                >

                                    <i data-lucide="user-plus"></i>

                                    Administrar clientes

                                </a>


                                {{-- EDITAR --}}

                                @if(Route::has('campanas.edit'))

                                    <a
                                        href="{{
                                            route(
                                                'campanas.edit',
                                                $campana->id_campana
                                            )
                                        }}"
                                        class="campaign-btn-secondary"
                                        title="Editar campaña"
                                    >

                                        <i data-lucide="pencil"></i>

                                        Editar

                                    </a>

                                @endif


                                {{-- ELIMINAR --}}

                                @if(Route::has('campanas.destroy'))

                                    <form
                                        action="{{
                                            route(
                                                'campanas.destroy',
                                                $campana->id_campana
                                            )
                                        }}"
                                        method="POST"
                                        class="delete-form"
                                        onsubmit="
                                            return confirm(
                                                '¿Seguro que deseas eliminar esta campaña?'
                                            );
                                        "
                                    >

                                        @csrf

                                        @method('DELETE')


                                        <button
                                            type="submit"
                                            class="campaign-btn-danger"
                                            title="Eliminar campaña"
                                        >

                                            <i data-lucide="trash-2"></i>

                                        </button>

                                    </form>

                                @endif

                            @endif

                        </div>

                    </div>

                </article>

            @endforeach

        </div>


        {{-- =====================================================
             PAGINACIÓN
        ====================================================== --}}

        @if(method_exists($campanas, 'links'))

            <div class="campaign-pagination">

                {{ $campanas->links() }}

            </div>

        @endif


    @else


        {{-- =====================================================
             SIN RESULTADOS
        ====================================================== --}}

        <div class="campaign-empty">

            <div class="campaign-empty-icon">

                <i data-lucide="megaphone"></i>

            </div>


            @if(
                request()->filled('buscar') ||
                request()->filled('estado')
            )

                <h3>
                    No encontramos campañas
                </h3>

                <p>

                    No hay campañas que coincidan con
                    los filtros seleccionados.
                    Prueba con otros criterios de búsqueda.

                </p>


                <a
                    href="{{ route('campanas.index') }}"
                    class="campaign-empty-action"
                >

                    <i data-lucide="rotate-ccw"></i>

                    Limpiar filtros

                </a>


            @else


                <h3>
                    Todavía no hay campañas
                </h3>

                <p>

                    Cuando registres una campaña aparecerá
                    aquí junto con sus fechas, estado,
                    objetivo y clientes relacionados.

                </p>


                @if(auth()->user()->id_rol != 3 && Route::has('campanas.create'))

                    <a
                        href="{{ route('campanas.create') }}"
                        class="campaign-empty-action"
                    >

                        <i data-lucide="plus"></i>

                        Crear primera campaña

                    </a>

                @endif

            @endif

        </div>

    @endif

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