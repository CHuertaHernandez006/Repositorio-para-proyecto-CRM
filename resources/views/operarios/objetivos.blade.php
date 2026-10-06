@extends('layouts.app')

@section('header-title', 'Objetivos de operarios')

@section('content')

@php
    $listaOperarios = $operarios ?? collect();

    $totalOperariosVista =
        $totalOperarios ?? $listaOperarios->count();

    $operariosConObjetivoVista =
        $operariosConObjetivo
        ?? $listaOperarios->filter(function ($operario) {
            return !is_null($operario->objetivo_actual ?? null);
        })->count();

    $operariosSinObjetivoVista =
        $operariosSinObjetivo
        ?? max(
            $totalOperariosVista - $operariosConObjetivoVista,
            0
        );

    $proximosVencerVista =
        $objetivosProximosVencer
        ?? $listaOperarios->filter(function ($operario) {
            return ($operario->situacion_objetivo ?? null)
                === 'proximo_vencer';
        })->count();

    $fechaInicioDefault = now()->toDateString();

    $fechaFinDefault = now()
        ->copy()
        ->addDays(7)
        ->toDateString();


    $objetivosCumplidosVista =
        $objetivosCumplidos ?? 0;

    $llamadasObjetivoGlobalVista =
        $llamadasObjetivoGlobal ?? 0;

    $llamadasRealizadasGlobalVista =
        $llamadasRealizadasGlobal ?? 0;

    $porcentajeGlobalVista =
        $porcentajeGlobal ?? 0;

    $porcentajeGlobalBarra =
        min(
            max(
                $porcentajeGlobalVista,
                0
            ),
            100
        );
@endphp


<style>
    .objetivos-page {
        color: #e8eef7;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* =========================================================
       HEADER
    ========================================================== */

    .objetivos-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 28px;
    }

    .objetivos-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 9px;
        color: #35c6ff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .objetivos-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 10px rgba(53, 198, 255, .6);
    }

    .objetivos-title {
        margin: 0;
        color: #fff;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .objetivos-description {
        max-width: 720px;
        margin: 9px 0 0;
        color: #8190a7;
        font-size: 14px;
        line-height: 1.6;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 15px;
        border: 1px solid rgba(148, 163, 184, .16);
        border-radius: 10px;
        background: rgba(148, 163, 184, .06);
        color: #a6b2c3;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-back:hover {
        color: #e2e8f0;
        background: rgba(148, 163, 184, .12);
        border-color: rgba(148, 163, 184, .30);
        transform: translateY(-1px);
    }

    .btn-back svg {
        width: 15px;
        height: 15px;
    }

    /* =========================================================
       ALERTAS
    ========================================================== */

    .page-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 20px;
        padding: 13px 15px;
        border-radius: 11px;
        font-size: 12px;
        line-height: 1.6;
    }

    .page-alert svg {
        width: 17px;
        height: 17px;
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
       ESTADÍSTICAS
    ========================================================== */

    .objetivos-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 24px;
    }

    .stat-card {
        position: relative;
        overflow: hidden;
        min-height: 112px;
        padding: 19px;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 15px;
        background: #111c30;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
        cursor: pointer;
        transition: .2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(53, 198, 255, .18);
        box-shadow: 0 17px 36px rgba(0, 0, 0, .13);
    }

    .stat-card.selected {
        border-color: rgba(53, 198, 255, .38);
        background: #132039;
        box-shadow:
            0 0 0 1px rgba(53, 198, 255, .05),
            0 15px 35px rgba(0, 0, 0, .12);
    }

    .stat-card::after {
        content: "";
        position: absolute;
        width: 90px;
        height: 90px;
        right: -40px;
        bottom: -45px;
        border-radius: 50%;
        background: rgba(53, 198, 255, .035);
        pointer-events: none;
    }

    .stat-card-top {
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

    .stat-value {
        margin-top: 8px;
        color: #fff;
        font-size: 29px;
        line-height: 1;
        font-weight: 750;
    }

    .stat-hint {
        margin-top: 9px;
        color: #56657b;
        font-size: 9px;
    }

    .stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 39px;
        height: 39px;
        border: 1px solid rgba(53, 198, 255, .28);
        border-radius: 10px;
        background: rgba(53, 198, 255, .13);
        color: #35c6ff;
    }

    .stat-icon svg {
        width: 19px;
        height: 19px;
    }

    .stat-icon.green {
        border-color: rgba(52, 211, 153, .25);
        background: rgba(52, 211, 153, .11);
        color: #34d399;
    }

    .stat-icon.yellow {
        border-color: rgba(251, 191, 36, .25);
        background: rgba(251, 191, 36, .10);
        color: #fbbf24;
    }

    .stat-icon.gray {
        border-color: rgba(148, 163, 184, .20);
        background: rgba(148, 163, 184, .08);
        color: #94a3b8;
    }

    /* =========================================================
       BARRA DE CONTROL
    ========================================================== */

    .filters-panel {
        margin-bottom: 26px;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 16px;
        background: #111c30;
        overflow: hidden;
    }

    .filters-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 17px 19px;
        border-bottom: 1px solid rgba(255, 255, 255, .05);
    }

    .filters-heading {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filters-heading-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: 1px solid rgba(53, 198, 255, .15);
        border-radius: 8px;
        background: rgba(53, 198, 255, .07);
        color: #35c6ff;
    }

    .filters-heading-icon svg {
        width: 15px;
        height: 15px;
    }

    .filters-title {
        color: #f1f5f9;
        font-size: 13px;
        font-weight: 700;
    }

    .filters-description {
        margin-top: 2px;
        color: #607087;
        font-size: 10px;
    }

    .results-count {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #718198;
        font-size: 10px;
    }

    .results-count strong {
        color: #b9c5d5;
        font-weight: 700;
    }

    .filters-body {
        display: grid;
        grid-template-columns:
            minmax(220px, 1.6fr)
            repeat(4, minmax(150px, 1fr))
            auto;
        gap: 11px;
        padding: 17px 19px;
        align-items: end;
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
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .filter-control {
        width: 100%;
        height: 39px;
        padding: 0 11px;
        border: 1px solid rgba(255, 255, 255, .075);
        border-radius: 9px;
        outline: none;
        background: #0b1628;
        color: #dce7f5;
        font-size: 11px;
        box-sizing: border-box;
        transition: .2s ease;
        color-scheme: dark;
    }

    .filter-control:focus {
        border-color: rgba(53, 198, 255, .35);
        box-shadow: 0 0 0 3px rgba(53, 198, 255, .045);
    }

    .filter-control option {
        background: #0e192b;
        color: #dce7f5;
    }

    .search-control {
        position: relative;
    }

    .search-control svg {
        position: absolute;
        top: 50%;
        left: 12px;
        width: 14px;
        height: 14px;
        color: #576780;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .search-control input {
        padding-left: 36px;
    }

    .btn-clear-filters {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        height: 39px;
        padding: 0 13px;
        border: 1px solid rgba(148, 163, 184, .13);
        border-radius: 9px;
        background: rgba(148, 163, 184, .055);
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s ease;
        white-space: nowrap;
    }

    .btn-clear-filters:hover {
        border-color: rgba(148, 163, 184, .28);
        background: rgba(148, 163, 184, .11);
        color: #d1d9e5;
    }

    .btn-clear-filters svg {
        width: 13px;
        height: 13px;
    }

    /* =========================================================
       CABECERA DE SECCIÓN
    ========================================================== */

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin: 31px 0 16px;
    }

    .section-title {
        margin: 0;
        color: #fff;
        font-size: 17px;
        font-weight: 750;
    }

    .section-description {
        margin: 5px 0 0;
        color: #687890;
        font-size: 12px;
        line-height: 1.6;
    }

    .section-counter {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid rgba(53, 198, 255, .12);
        border-radius: 8px;
        background: rgba(53, 198, 255, .05);
        color: #67d5ff;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* =========================================================
       GRID
    ========================================================== */

    .objetivos-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .operario-objective-card {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 15px;
        background: #111c30;
        box-shadow: 0 18px 40px rgba(0, 0, 0, .08);
        transition:
            transform .2s ease,
            border-color .2s ease,
            box-shadow .2s ease;
    }

    .operario-objective-card:hover {
        border-color: rgba(53, 198, 255, .12);
        transform: translateY(-1px);
        box-shadow: 0 20px 45px rgba(0, 0, 0, .12);
    }

    .operario-objective-card.hidden-card {
        display: none;
    }

    .objective-card-main {
        padding: 19px 20px;
    }

    .objective-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .operario-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .operario-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border: 1px solid rgba(53, 198, 255, .15);
        border-radius: 11px;
        background: rgba(53, 198, 255, .07);
        color: #52cfff;
        font-size: 14px;
        font-weight: 800;
    }

    .operario-name {
        margin: 0;
        color: #f1f5f9;
        font-size: 14px;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .operario-email {
        margin-top: 4px;
        color: #64748b;
        font-size: 11px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .operario-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 9px;
    }

    .mini-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 7px;
        border: 1px solid rgba(148, 163, 184, .10);
        border-radius: 6px;
        background: rgba(148, 163, 184, .04);
        color: #77869b;
        font-size: 9px;
        font-weight: 650;
    }

    .mini-badge svg {
        width: 10px;
        height: 10px;
    }

    .mini-badge.active-user {
        color: #6ee7b7;
        border-color: rgba(52, 211, 153, .13);
        background: rgba(52, 211, 153, .05);
    }

    .mini-badge.inactive-user {
        color: #8491a3;
    }

    /* =========================================================
       ESTADOS
    ========================================================== */

    .objective-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 30px;
        font-size: 9px;
        font-weight: 750;
        white-space: nowrap;
    }

    .objective-status .dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
    }

    .objective-status.progress {
        border: 1px solid rgba(53, 198, 255, .16);
        background: rgba(53, 198, 255, .07);
        color: #6bd8ff;
    }

    .objective-status.progress .dot {
        background: #35c6ff;
        box-shadow: 0 0 7px rgba(53, 198, 255, .6);
    }

    .objective-status.warning {
        border: 1px solid rgba(251, 191, 36, .17);
        background: rgba(251, 191, 36, .07);
        color: #fcd34d;
    }

    .objective-status.warning .dot {
        background: #fbbf24;
    }

    .objective-status.pending {
        border: 1px solid rgba(167, 139, 250, .17);
        background: rgba(167, 139, 250, .07);
        color: #c4b5fd;
    }

    .objective-status.pending .dot {
        background: #a78bfa;
    }

    .objective-status.success {
        border: 1px solid rgba(52, 211, 153, .18);
        background: rgba(52, 211, 153, .07);
        color: #6ee7b7;
    }

    .objective-status.success .dot {
        background: #34d399;
        box-shadow: 0 0 7px rgba(52, 211, 153, .45);
    }


    .objective-status.none {
        border: 1px solid rgba(148, 163, 184, .12);
        background: rgba(148, 163, 184, .05);
        color: #7e8ca1;
    }

    .objective-status.none .dot {
        background: #64748b;
    }

    /* =========================================================
       OBJETIVO ACTUAL
    ========================================================== */

    .current-objective {
        margin-top: 17px;
        padding: 15px;
        border: 1px solid rgba(255, 255, 255, .045);
        border-radius: 11px;
        background: rgba(7, 17, 31, .27);
    }

    .objective-number-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 15px;
    }

    .objective-small-label {
        color: #627189;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .objective-number {
        margin-top: 5px;
        color: #fff;
        font-size: 25px;
        font-weight: 750;
        line-height: 1;
    }

    .objective-number span {
        color: #718096;
        font-size: 11px;
        font-weight: 600;
    }

    .objective-period {
        padding: 5px 8px;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 6px;
        background: rgba(255, 255, 255, .025);
        color: #91a0b5;
        font-size: 10px;
        font-weight: 650;
        text-transform: capitalize;
    }

    .objective-dates {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 13px;
        padding-top: 12px;
        border-top: 1px solid rgba(255, 255, 255, .045);
        color: #6f7f95;
        font-size: 10px;
    }

    .objective-dates svg {
        width: 13px;
        height: 13px;
        color: #506078;
    }

    .objective-progress {
        margin-top: 15px;
        padding-top: 14px;
        border-top: 1px solid rgba(255, 255, 255, .045);
    }

    .objective-progress-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 9px;
    }

    .objective-progress-title {
        color: #8c9bb0;
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .objective-progress-percent {
        color: #eaf4ff;
        font-size: 11px;
        font-weight: 800;
    }

    .objective-progress-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(148, 163, 184, .10);
    }

    .objective-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(
            90deg,
            #0ea5e9,
            #35c6ff
        );
        transition: width .35s ease;
    }

    .objective-progress-fill.complete {
        background: linear-gradient(
            90deg,
            #059669,
            #34d399
        );
    }

    .objective-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-top: 11px;
    }

    .objective-metric {
        min-width: 0;
        padding: 9px 10px;
        border: 1px solid rgba(148, 163, 184, .07);
        border-radius: 8px;
        background: rgba(8, 17, 30, .28);
    }

    .objective-metric-value {
        color: #edf6ff;
        font-size: 14px;
        font-weight: 800;
    }

    .objective-metric-label {
        margin-top: 3px;
        color: #627189;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .objective-progress-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 10px;
        color: #5f6f85;
        font-size: 9px;
    }

    .team-progress-panel {
        margin-bottom: 24px;
        padding: 17px 19px;
        border: 1px solid rgba(53, 198, 255, .10);
        border-radius: 14px;
        background:
            linear-gradient(
                120deg,
                rgba(53, 198, 255, .045),
                rgba(17, 28, 48, .96)
            );
    }

    .team-progress-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .team-progress-title {
        color: #f1f5f9;
        font-size: 12px;
        font-weight: 750;
    }

    .team-progress-sub {
        margin-top: 4px;
        color: #63738a;
        font-size: 10px;
    }

    .team-progress-number {
        color: #67d5ff;
        font-size: 18px;
        font-weight: 800;
        white-space: nowrap;
    }

    .team-progress-track {
        height: 7px;
        margin-top: 13px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(148, 163, 184, .10);
    }

    .team-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(
            90deg,
            #0ea5e9,
            #35c6ff
        );
    }


    .objective-empty {
        margin-top: 17px;
        padding: 16px;
        border: 1px dashed rgba(148, 163, 184, .13);
        border-radius: 11px;
        background: rgba(148, 163, 184, .025);
    }

    .objective-empty-title {
        color: #a8b3c3;
        font-size: 12px;
        font-weight: 700;
    }

    .objective-empty-text {
        margin-top: 4px;
        color: #5f6e83;
        font-size: 10px;
        line-height: 1.6;
    }

    /* =========================================================
       ACCIONES
    ========================================================== */

    .objective-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
    }

    .objective-details {
        flex: 1;
    }

    .objective-details summary {
        list-style: none;
    }

    .objective-details summary::-webkit-details-marker {
        display: none;
    }

    .btn-objective {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 36px;
        padding: 0 13px;
        border: 1px solid rgba(53, 198, 255, .20);
        border-radius: 9px;
        background: rgba(53, 198, 255, .08);
        color: #56ceff;
        font-size: 11px;
        font-weight: 750;
        cursor: pointer;
        text-decoration: none;
        transition: .2s ease;
        box-sizing: border-box;
    }

    .btn-objective:hover {
        border-color: rgba(53, 198, 255, .45);
        background: rgba(53, 198, 255, .15);
        color: #8dddff;
    }

    .btn-objective svg {
        width: 14px;
        height: 14px;
    }

    .btn-objective.full {
        width: 100%;
    }

    .btn-view {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        border: 1px solid rgba(148, 163, 184, .14);
        border-radius: 9px;
        background: rgba(148, 163, 184, .055);
        color: #94a3b8;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-view:hover {
        color: #e2e8f0;
        border-color: rgba(148, 163, 184, .30);
        background: rgba(148, 163, 184, .11);
    }

    .btn-view svg {
        width: 14px;
        height: 14px;
    }

    /* =========================================================
       FORMULARIO
    ========================================================== */

    .objective-form-wrapper {
        margin-top: 13px;
        padding: 17px;
        border-top: 1px solid rgba(255, 255, 255, .05);
        background: #0d1728;
    }

    .form-heading {
        margin-bottom: 15px;
    }

    .form-title {
        color: #fff;
        font-size: 12px;
        font-weight: 750;
    }

    .form-description {
        margin-top: 4px;
        color: #5f6e84;
        font-size: 10px;
        line-height: 1.5;
    }

    .objective-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 13px;
    }

    .form-group {
        min-width: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        color: #8090a7;
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .form-control {
        width: 100%;
        height: 39px;
        padding: 0 11px;
        border: 1px solid rgba(255, 255, 255, .075);
        border-radius: 9px;
        outline: none;
        background: #091426;
        color: #edf4fc;
        font-size: 11px;
        box-sizing: border-box;
        transition: .2s ease;
        color-scheme: dark;
    }

    .form-control:focus {
        border-color: rgba(53, 198, 255, .35);
        box-shadow: 0 0 0 3px rgba(53, 198, 255, .045);
    }

    .form-control option {
        background: #101a2c;
        color: #e2e8f0;
    }

    .form-hint {
        margin-top: 5px;
        color: #4f5e73;
        font-size: 9px;
        line-height: 1.45;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        grid-column: 1 / -1;
        padding-top: 4px;
    }

    .btn-save-objective {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        padding: 0 15px;
        border: 1px solid rgba(53, 198, 255, .25);
        border-radius: 9px;
        background: #35c6ff;
        color: #07111f;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn-save-objective:hover {
        background: #66d5ff;
        box-shadow: 0 0 16px rgba(53, 198, 255, .14);
        transform: translateY(-1px);
    }

    .btn-save-objective svg {
        width: 14px;
        height: 14px;
    }

    /* =========================================================
       SIN RESULTADOS
    ========================================================== */

    .no-filter-results {
        display: none;
        padding: 65px 25px;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 15px;
        background: #111c30;
        text-align: center;
    }

    .no-filter-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        margin: 0 auto;
        border: 1px solid rgba(148, 163, 184, .12);
        border-radius: 14px;
        background: rgba(148, 163, 184, .05);
        color: #7f8ca0;
    }

    .no-filter-icon svg {
        width: 23px;
        height: 23px;
    }

    .no-filter-results h3 {
        margin: 15px 0 0;
        color: #e5edf7;
        font-size: 14px;
    }

    .no-filter-results p {
        margin: 6px 0 0;
        color: #617087;
        font-size: 11px;
    }

    /* =========================================================
       EMPTY
    ========================================================== */

    .empty-page {
        padding: 65px 25px;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 15px;
        background: #111c30;
        text-align: center;
    }

    .empty-page-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin: 0 auto;
        border: 1px solid rgba(53, 198, 255, .12);
        border-radius: 15px;
        background: rgba(53, 198, 255, .05);
        color: #35c6ff;
    }

    .empty-page-icon svg {
        width: 25px;
        height: 25px;
    }

    .empty-page h3 {
        margin: 16px 0 0;
        color: #fff;
        font-size: 16px;
    }

    .empty-page p {
        max-width: 420px;
        margin: 7px auto 0;
        color: #65748b;
        font-size: 12px;
        line-height: 1.7;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 1200px) {
        .filters-body {
            grid-template-columns: repeat(3, 1fr);
        }

        .search-filter {
            grid-column: span 2;
        }
    }

    @media (max-width: 1050px) {
        .objetivos-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 850px) {
        .objetivos-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .header-actions,
        .btn-back {
            width: 100%;
        }

        .objetivos-grid {
            grid-template-columns: 1fr;
        }

        .filters-body {
            grid-template-columns: 1fr 1fr;
        }

        .search-filter {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 600px) {
        .objetivos-title {
            font-size: 27px;
        }

        .objetivos-stats {
            grid-template-columns: 1fr;
        }

        .filters-header,
        .section-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .filters-body {
            grid-template-columns: 1fr;
        }

        .search-filter {
            grid-column: 1;
        }

        .btn-clear-filters {
            width: 100%;
        }

        .objective-card-header {
            flex-direction: column;
        }

        .objective-form {
            grid-template-columns: 1fr;
        }

        .objective-metrics {
            grid-template-columns: 1fr;
        }


        .form-actions {
            grid-column: 1;
        }
    }
</style>


<div class="objetivos-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="objetivos-header">

        <div>
            <div class="objetivos-eyebrow">
                <span class="objetivos-eyebrow-dot"></span>
                Gestión de metas
            </div>

            <h1 class="objetivos-title">
                Objetivos de operarios
            </h1>

            <p class="objetivos-description">
                Administra las metas de llamadas del equipo, identifica
                rápidamente quién necesita un objetivo y consulta las fechas
                establecidas para cada operario.
            </p>
        </div>

        <div class="header-actions">
            <a
                href="{{ route('operarios.index') }}"
                class="btn-back"
            >
                <i data-lucide="arrow-left"></i>
                Volver a operarios
            </a>
        </div>

    </div>


    {{-- =========================================================
         MENSAJES
    ========================================================== --}}

    @if(session('success'))
        <div class="page-alert success">
            <i data-lucide="circle-check"></i>

            <div>
                {{ session('success') }}
            </div>
        </div>
    @endif


    @if($errors->any())
        <div class="page-alert error">
            <i data-lucide="triangle-alert"></i>

            <div>
                <strong>
                    No se pudo guardar el objetivo.
                </strong>

                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif


    {{-- =========================================================
         ESTADÍSTICAS / FILTROS RÁPIDOS
    ========================================================== --}}

    <div class="objetivos-stats">

        <div
            class="stat-card selected"
            data-quick-filter="todos"
            title="Mostrar todos"
        >
            <div class="stat-card-top">
                <div>
                    <div class="stat-label">
                        Total de operarios
                    </div>

                    <div class="stat-value">
                        {{ $totalOperariosVista }}
                    </div>

                    <div class="stat-hint">
                        Ver todo el equipo
                    </div>
                </div>

                <div class="stat-icon">
                    <i data-lucide="users-round"></i>
                </div>
            </div>
        </div>


        <div
            class="stat-card"
            data-quick-filter="con_objetivo"
            title="Mostrar operarios con objetivo"
        >
            <div class="stat-card-top">
                <div>
                    <div class="stat-label">
                        Con objetivo
                    </div>

                    <div class="stat-value">
                        {{ $operariosConObjetivoVista }}
                    </div>

                    <div class="stat-hint">
                        Metas actualmente asignadas
                    </div>
                </div>

                <div class="stat-icon green">
                    <i data-lucide="target"></i>
                </div>
            </div>
        </div>


        <div
            class="stat-card"
            data-quick-filter="sin_objetivo"
            title="Mostrar operarios sin objetivo"
        >
            <div class="stat-card-top">
                <div>
                    <div class="stat-label">
                        Sin objetivo
                    </div>

                    <div class="stat-value">
                        {{ $operariosSinObjetivoVista }}
                    </div>

                    <div class="stat-hint">
                        Requieren una meta
                    </div>
                </div>

                <div class="stat-icon gray">
                    <i data-lucide="circle-dashed"></i>
                </div>
            </div>
        </div>


        <div
            class="stat-card"
            data-quick-filter="proximo_vencer"
            title="Mostrar objetivos próximos a vencer"
        >
            <div class="stat-card-top">
                <div>
                    <div class="stat-label">
                        Próximos a vencer
                    </div>

                    <div class="stat-value">
                        {{ $proximosVencerVista }}
                    </div>

                    <div class="stat-hint">
                        Dos días o menos
                    </div>
                </div>

                <div class="stat-icon yellow">
                    <i data-lucide="clock-alert"></i>
                </div>
            </div>
        </div>

    </div>

    @if($llamadasObjetivoGlobalVista > 0)
        <div class="team-progress-panel">
            <div class="team-progress-top">

                <div>
                    <div class="team-progress-title">
                        Avance automático del equipo
                    </div>

                    <div class="team-progress-sub">
                        Solo cuentan llamadas salientes con cierre registrado dentro del periodo de cada objetivo.
                    </div>
                </div>

                <div class="team-progress-number">
                    {{ number_format($llamadasRealizadasGlobalVista) }}
                    /
                    {{ number_format($llamadasObjetivoGlobalVista) }}
                    ·
                    {{ $porcentajeGlobalVista }}%
                </div>

            </div>

            <div class="team-progress-track">
                <div
                    class="team-progress-fill"
                    style="width: {{ $porcentajeGlobalBarra }}%;"
                ></div>
            </div>
        </div>
    @endif


    {{-- =========================================================
         FILTROS
    ========================================================== --}}

    @if($listaOperarios->count() > 0)

        <div class="filters-panel">

            <div class="filters-header">

                <div class="filters-heading">

                    <div class="filters-heading-icon">
                        <i data-lucide="list-filter"></i>
                    </div>

                    <div>
                        <div class="filters-title">
                            Buscar y organizar operarios
                        </div>

                        <div class="filters-description">
                            Combina los filtros para localizar rápidamente
                            los objetivos que necesitas administrar.
                        </div>
                    </div>

                </div>

                <div class="results-count">
                    Mostrando
                    <strong id="resultadosVisibles">
                        {{ $totalOperariosVista }}
                    </strong>
                    de
                    <strong>
                        {{ $totalOperariosVista }}
                    </strong>
                </div>

            </div>


            <div class="filters-body">

                {{-- BUSCADOR --}}

                <div class="filter-group search-filter">

                    <label class="filter-label">
                        Buscar operario
                    </label>

                    <div class="search-control">

                        <i data-lucide="search"></i>

                        <input
                            type="text"
                            id="filtroBusqueda"
                            class="filter-control"
                            placeholder="Nombre o correo..."
                            autocomplete="off"
                        >

                    </div>

                </div>


                {{-- OBJETIVO --}}

                <div class="filter-group">

                    <label class="filter-label">
                        Objetivo
                    </label>

                    <select
                        id="filtroObjetivo"
                        class="filter-control"
                    >
                        <option value="todos">
                            Todos
                        </option>

                        <option value="con_objetivo">
                            Con objetivo
                        </option>

                        <option value="sin_objetivo">
                            Sin objetivo
                        </option>
                    </select>

                </div>


                {{-- ESTADO OBJETIVO --}}

                <div class="filter-group">

                    <label class="filter-label">
                        Estado de meta
                    </label>

                    <select
                        id="filtroSituacion"
                        class="filter-control"
                    >
                        <option value="todos">
                            Todos
                        </option>

                        <option value="en_progreso">
                            En progreso
                        </option>

                        <option value="pendiente">
                            Pendientes
                        </option>

                        <option value="proximo_vencer">
                            Próximos a vencer
                        </option>

                        <option value="cumplido">
                            Cumplidos
                        </option>

                        <option value="sin_objetivo">
                            Sin objetivo
                        </option>
                    </select>

                </div>


                {{-- ESTADO OPERARIO --}}

                <div class="filter-group">

                    <label class="filter-label">
                        Operario
                    </label>

                    <select
                        id="filtroEstadoOperario"
                        class="filter-control"
                    >
                        <option value="todos">
                            Activos e inactivos
                        </option>

                        <option value="activo">
                            Activos
                        </option>

                        <option value="inactivo">
                            Inactivos
                        </option>
                    </select>

                </div>


                {{-- ORDEN --}}

                <div class="filter-group">

                    <label class="filter-label">
                        Ordenar por
                    </label>

                    <select
                        id="ordenOperarios"
                        class="filter-control"
                    >
                        <option value="nombre_asc">
                            Nombre A - Z
                        </option>

                        <option value="nombre_desc">
                            Nombre Z - A
                        </option>

                        <option value="objetivo_desc">
                            Mayor objetivo
                        </option>

                        <option value="objetivo_asc">
                            Menor objetivo
                        </option>

                        <option value="progreso_desc">
                            Mayor progreso
                        </option>

                        <option value="progreso_asc">
                            Menor progreso
                        </option>

                        <option value="vence_primero">
                            Vence primero
                        </option>

                        <option value="sin_objetivo_primero">
                            Sin objetivo primero
                        </option>
                    </select>

                </div>


                {{-- LIMPIAR --}}

                <button
                    type="button"
                    id="limpiarFiltros"
                    class="btn-clear-filters"
                >
                    <i data-lucide="rotate-ccw"></i>
                    Limpiar
                </button>

            </div>

        </div>

    @endif


    {{-- =========================================================
         LISTADO
    ========================================================== --}}

    <div class="section-header">

        <div>
            <h2 class="section-title">
                Asignación de objetivos
            </h2>

            <p class="section-description">
                Consulta la meta actual de cada operario o asigna una nueva
                directamente desde su tarjeta.
            </p>
        </div>

        <div class="section-counter">
            <i
                data-lucide="users"
                style="width:13px;height:13px;"
            ></i>

            <span id="contadorSeccion">
                {{ $totalOperariosVista }}
            </span>

            <span>
                operarios
            </span>
        </div>

    </div>


    @if($listaOperarios->count() > 0)

        <div
            class="objetivos-grid"
            id="objetivosGrid"
        >

            @foreach($listaOperarios as $operario)

                @php
                    $objetivoActual =
                        $operario->objetivo_actual ?? null;

                    $situacion =
                        $operario->situacion_objetivo
                        ?? ($objetivoActual
                            ? 'en_progreso'
                            : 'sin_objetivo');

                    $activo =
                        (bool) ($operario->activo ?? true);

                    $inicial =
                        strtoupper(
                            substr($operario->name, 0, 1)
                        );

                    $formularioConError =
                        old('operario_form')
                        == $operario->id;

                    $estadoClase = 'none';
                    $estadoTexto = 'Sin objetivo';

                    if ($objetivoActual) {
                        if (
                            $objetivoActual->estado
                            === 'pendiente'
                        ) {
                            $estadoClase = 'pending';
                            $estadoTexto = 'Pendiente';
                        } elseif (
                            $situacion
                            === 'cumplido'
                        ) {
                            $estadoClase = 'success';
                            $estadoTexto =
                                'Cumplido';
                        } elseif (
                            $situacion
                            === 'proximo_vencer'
                        ) {
                            $estadoClase = 'warning';
                            $estadoTexto =
                                'Próximo a vencer';
                        } else {
                            $estadoClase = 'progress';
                            $estadoTexto =
                                'En progreso';
                        }
                    }

                    $cantidadObjetivo =
                        $objetivoActual
                            ? (int) $objetivoActual->objetivo_llamadas
                            : 0;

                    $periodoObjetivo =
                        $objetivoActual
                            ? $objetivoActual->periodo
                            : 'ninguno';

                    $fechaFinOrden =
                        $objetivoActual
                            ? \Carbon\Carbon::parse(
                                $objetivoActual->fecha_fin
                            )->timestamp
                            : 9999999999;


                    $llamadasRealizadas =
                        (int) (
                            $operario->llamadas_realizadas
                            ?? 0
                        );

                    $llamadasPendientes =
                        (int) (
                            $operario->llamadas_pendientes
                            ?? 0
                        );

                    $llamadasIntentadas =
                        (int) (
                            $operario->llamadas_intentadas
                            ?? 0
                        );

                    $llamadasClasificadas =
                        (int) (
                            $operario->llamadas_clasificadas
                            ?? 0
                        );

                    $porcentajeObjetivo =
                        (int) (
                            $operario->porcentaje_objetivo
                            ?? 0
                        );

                    $porcentajeBarra =
                        (int) (
                            $operario->porcentaje_barra
                            ?? 0
                        );

                    $diasRestantes =
                        $operario->dias_restantes_objetivo
                        ?? null;
                @endphp


                <article
                    class="operario-objective-card"
                    data-operario-card

                    data-name="{{ strtolower($operario->name) }}"

                    data-email="{{ strtolower($operario->email) }}"

                    data-search="{{ strtolower(
                        $operario->name . ' ' .
                        $operario->email
                    ) }}"

                    data-objective="{{ $objetivoActual ? 'con_objetivo' : 'sin_objetivo' }}"

                    data-situation="{{ $situacion }}"

                    data-user-status="{{ $activo ? 'activo' : 'inactivo' }}"

                    data-period="{{ $periodoObjetivo }}"

                    data-goal="{{ $cantidadObjetivo }}"

                    data-end-date="{{ $fechaFinOrden }}"

                    data-progress="{{ $porcentajeObjetivo }}"
                >

                    <div class="objective-card-main">

                        {{-- OPERARIO --}}

                        <div class="objective-card-header">

                            <div class="operario-info">

                                <div class="operario-avatar">
                                    {{ $inicial }}
                                </div>


                                <div style="min-width:0;">

                                    <h3 class="operario-name">
                                        {{ $operario->name }}
                                    </h3>

                                    <div class="operario-email">
                                        {{ $operario->email }}
                                    </div>


                                    <div class="operario-meta">

                                        @if($activo)

                                            <span class="mini-badge active-user">
                                                <i data-lucide="circle-check"></i>
                                                Activo
                                            </span>

                                        @else

                                            <span class="mini-badge inactive-user">
                                                <i data-lucide="circle-minus"></i>
                                                Inactivo
                                            </span>

                                        @endif


                                        <span class="mini-badge">
                                            <i data-lucide="hash"></i>
                                            {{ $operario->id }}
                                        </span>

                                    </div>

                                </div>

                            </div>


                            <span class="objective-status {{ $estadoClase }}">

                                <span class="dot"></span>

                                {{ $estadoTexto }}

                            </span>

                        </div>


                        {{-- OBJETIVO ACTUAL --}}

                        @if($objetivoActual)

                            <div class="current-objective">

                                <div class="objective-number-row">

                                    <div>

                                        <div class="objective-small-label">
                                            Objetivo actual
                                        </div>

                                        <div class="objective-number">

                                            {{ number_format(
                                                $objetivoActual->objetivo_llamadas
                                            ) }}

                                            <span>
                                                llamadas
                                            </span>

                                        </div>

                                    </div>


                                    <div class="objective-period">

                                        {{ ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $objetivoActual->periodo
                                            )
                                        ) }}

                                    </div>

                                </div>


                                <div class="objective-dates">

                                    <i data-lucide="calendar-range"></i>

                                    <span>
                                        {{ \Carbon\Carbon::parse(
                                            $objetivoActual->fecha_inicio
                                        )->format('d/m/Y') }}
                                    </span>

                                    <i
                                        data-lucide="arrow-right"
                                        style="width:11px;height:11px;"
                                    ></i>

                                    <span>
                                        {{ \Carbon\Carbon::parse(
                                            $objetivoActual->fecha_fin
                                        )->format('d/m/Y') }}
                                    </span>

                                </div>


                                <div class="objective-progress">

                                    <div class="objective-progress-head">

                                        <div class="objective-progress-title">
                                            Progreso automático
                                        </div>

                                        <div class="objective-progress-percent">
                                            {{ $porcentajeObjetivo }}%
                                        </div>

                                    </div>


                                    <div class="objective-progress-track">

                                        <div
                                            class="objective-progress-fill {{ $situacion === 'cumplido' ? 'complete' : '' }}"
                                            style="width: {{ $porcentajeBarra }}%;"
                                        ></div>

                                    </div>


                                    <div class="objective-metrics">

                                        <div class="objective-metric">

                                            <div class="objective-metric-value">
                                                {{ number_format($llamadasRealizadas) }}
                                            </div>

                                            <div class="objective-metric-label">
                                                Realizadas
                                            </div>

                                        </div>


                                        <div class="objective-metric">

                                            <div class="objective-metric-value">
                                                {{ number_format($llamadasPendientes) }}
                                            </div>

                                            <div class="objective-metric-label">
                                                Restantes
                                            </div>

                                        </div>


                                        <div class="objective-metric">

                                            <div class="objective-metric-value">
                                                {{ number_format($llamadasClasificadas) }}
                                            </div>

                                            <div class="objective-metric-label">
                                                Con resultado
                                            </div>

                                        </div>

                                    </div>


                                    <div class="objective-progress-foot">

                                        <span>
                                            {{ number_format($llamadasIntentadas) }}
                                            intentos salientes registrados
                                        </span>

                                        @if($diasRestantes !== null)

                                            <span>
                                                @if($situacion === 'cumplido')
                                                    Meta alcanzada
                                                @elseif($diasRestantes < 0)
                                                    Periodo vencido
                                                @elseif($diasRestantes === 0)
                                                    Vence hoy
                                                @elseif($diasRestantes === 1)
                                                    1 día restante
                                                @else
                                                    {{ $diasRestantes }} días restantes
                                                @endif
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </div>

                        @else

                            <div class="objective-empty">

                                <div class="objective-empty-title">
                                    Sin objetivo asignado
                                </div>

                                <div class="objective-empty-text">
                                    Este operario todavía no tiene una meta
                                    de llamadas activa.
                                </div>

                            </div>

                        @endif


                        {{-- ACCIONES --}}

                        <div class="objective-actions">

                            <details
                                class="objective-details"
                                {{ $formularioConError ? 'open' : '' }}
                            >

                                <summary class="btn-objective full">

                                    <i
                                        data-lucide="{{ $objetivoActual ? 'refresh-cw' : 'plus' }}"
                                    ></i>

                                    {{ $objetivoActual
                                        ? 'Cambiar objetivo'
                                        : 'Asignar objetivo'
                                    }}

                                </summary>


                                <div class="objective-form-wrapper">

                                    <div class="form-heading">

                                        <div class="form-title">
                                            {{ $objetivoActual
                                                ? 'Asignar nuevo objetivo'
                                                : 'Asignar objetivo'
                                            }}
                                        </div>

                                        <div class="form-description">

                                            @if($objetivoActual)

                                                El objetivo anterior
                                                permanecerá registrado
                                                en el historial.

                                            @else

                                                Define la meta de llamadas
                                                de este operario.

                                            @endif

                                        </div>

                                    </div>


                                    <form
                                        action="{{ route(
                                            'operarios.asignarObjetivo',
                                            $operario
                                        ) }}"
                                        method="POST"
                                        class="objective-form"
                                        data-objective-form
                                    >

                                        @csrf

                                        <input
                                            type="hidden"
                                            name="operario_form"
                                            value="{{ $operario->id }}"
                                        >


                                        {{-- CANTIDAD --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Llamadas objetivo
                                            </label>

                                            <input
                                                type="number"
                                                name="objetivo_llamadas"
                                                class="form-control"
                                                min="1"
                                                max="100000"
                                                placeholder="Ej. 100"
                                                value="{{ $formularioConError
                                                    ? old('objetivo_llamadas')
                                                    : ''
                                                }}"
                                                required
                                            >

                                            <div class="form-hint">
                                                Cantidad de llamadas
                                                esperadas.
                                            </div>

                                        </div>


                                        {{-- PERIODO --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Periodo
                                            </label>

                                            <select
                                                name="periodo"
                                                class="form-control periodo-select"
                                                required
                                            >

                                                <option
                                                    value="semanal"
                                                    {{ $formularioConError &&
                                                        old('periodo') === 'semanal'
                                                        ? 'selected'
                                                        : ''
                                                    }}
                                                >
                                                    Semanal
                                                </option>

                                                <option
                                                    value="mensual"
                                                    {{ $formularioConError &&
                                                        old('periodo') === 'mensual'
                                                        ? 'selected'
                                                        : ''
                                                    }}
                                                >
                                                    Mensual
                                                </option>

                                                <option
                                                    value="personalizado"
                                                    {{ $formularioConError &&
                                                        old('periodo') === 'personalizado'
                                                        ? 'selected'
                                                        : ''
                                                    }}
                                                >
                                                    Personalizado
                                                </option>

                                            </select>

                                        </div>


                                        {{-- FECHA INICIO --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Fecha de inicio
                                            </label>

                                            <input
                                                type="date"
                                                name="fecha_inicio"
                                                class="form-control fecha-inicio"
                                                value="{{ $formularioConError
                                                    ? old(
                                                        'fecha_inicio',
                                                        $fechaInicioDefault
                                                    )
                                                    : $fechaInicioDefault
                                                }}"
                                                required
                                            >

                                        </div>


                                        {{-- FECHA FIN --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Fecha límite
                                            </label>

                                            <input
                                                type="date"
                                                name="fecha_fin"
                                                class="form-control fecha-fin"
                                                value="{{ $formularioConError
                                                    ? old(
                                                        'fecha_fin',
                                                        $fechaFinDefault
                                                    )
                                                    : $fechaFinDefault
                                                }}"
                                                required
                                            >

                                        </div>


                                        <div class="form-actions">

                                            <button
                                                type="submit"
                                                class="btn-save-objective"
                                            >

                                                <i data-lucide="target"></i>

                                                {{ $objetivoActual
                                                    ? 'Guardar nuevo objetivo'
                                                    : 'Asignar objetivo'
                                                }}

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </details>


                            @if(Route::has('operarios.show'))

                                <a
                                    href="{{ route(
                                        'operarios.show',
                                        $operario
                                    ) }}"
                                    class="btn-view"
                                    title="Ver operario"
                                >
                                    <i data-lucide="eye"></i>
                                </a>

                            @endif

                        </div>

                    </div>

                </article>

            @endforeach

        </div>


        {{-- SIN RESULTADOS --}}

        <div
            id="sinResultadosFiltros"
            class="no-filter-results"
        >

            <div class="no-filter-icon">
                <i data-lucide="search-x"></i>
            </div>

            <h3>
                No hay coincidencias
            </h3>

            <p>
                Cambia o limpia los filtros para volver
                a mostrar operarios.
            </p>

        </div>

    @else

        <div class="empty-page">

            <div class="empty-page-icon">
                <i data-lucide="users-round"></i>
            </div>

            <h3>
                No hay operarios disponibles
            </h3>

            <p>
                Primero registra operarios en el sistema
                para poder asignarles objetivos de llamadas.
            </p>

        </div>

    @endif

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       LUCIDE
    ========================================================== */

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }


    /* =========================================================
       ELEMENTOS DE FILTRADO
    ========================================================== */

    const grid =
        document.getElementById('objetivosGrid');

    const tarjetas =
        Array.from(
            document.querySelectorAll(
                '[data-operario-card]'
            )
        );

    const filtroBusqueda =
        document.getElementById('filtroBusqueda');

    const filtroObjetivo =
        document.getElementById('filtroObjetivo');

    const filtroSituacion =
        document.getElementById('filtroSituacion');

    const filtroEstadoOperario =
        document.getElementById('filtroEstadoOperario');

    const ordenOperarios =
        document.getElementById('ordenOperarios');

    const limpiarFiltros =
        document.getElementById('limpiarFiltros');

    const resultadosVisibles =
        document.getElementById('resultadosVisibles');

    const contadorSeccion =
        document.getElementById('contadorSeccion');

    const sinResultados =
        document.getElementById(
            'sinResultadosFiltros'
        );

    const filtrosRapidos =
        document.querySelectorAll(
            '[data-quick-filter]'
        );


    /* =========================================================
       NORMALIZAR TEXTO
    ========================================================== */

    function normalizarTexto(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }


    /* =========================================================
       FILTRAR
    ========================================================== */

    function aplicarFiltros() {

        if (!grid) {
            return;
        }

        const busqueda =
            normalizarTexto(
                filtroBusqueda
                    ? filtroBusqueda.value
                    : ''
            );

        const objetivo =
            filtroObjetivo
                ? filtroObjetivo.value
                : 'todos';

        const situacion =
            filtroSituacion
                ? filtroSituacion.value
                : 'todos';

        const estadoOperario =
            filtroEstadoOperario
                ? filtroEstadoOperario.value
                : 'todos';


        let visibles = 0;


        tarjetas.forEach(function (tarjeta) {

            const texto =
                normalizarTexto(
                    tarjeta.dataset.search
                );

            const objetivoTarjeta =
                tarjeta.dataset.objective;

            const situacionTarjeta =
                tarjeta.dataset.situation;

            const estadoTarjeta =
                tarjeta.dataset.userStatus;


            const coincideBusqueda =
                !busqueda ||
                texto.includes(busqueda);


            const coincideObjetivo =
                objetivo === 'todos' ||
                objetivoTarjeta === objetivo;


            const coincideSituacion =
                situacion === 'todos' ||
                situacionTarjeta === situacion;


            const coincideEstado =
                estadoOperario === 'todos' ||
                estadoTarjeta === estadoOperario;


            const visible =
                coincideBusqueda &&
                coincideObjetivo &&
                coincideSituacion &&
                coincideEstado;


            tarjeta.classList.toggle(
                'hidden-card',
                !visible
            );


            if (visible) {
                visibles++;
            }

        });


        if (resultadosVisibles) {
            resultadosVisibles.textContent =
                visibles;
        }

        if (contadorSeccion) {
            contadorSeccion.textContent =
                visibles;
        }

        if (sinResultados) {
            sinResultados.style.display =
                visibles === 0
                    ? 'block'
                    : 'none';
        }


        ordenarTarjetas();
    }


    /* =========================================================
       ORDENAR
    ========================================================== */

    function ordenarTarjetas() {

        if (!grid || !ordenOperarios) {
            return;
        }

        const tipo =
            ordenOperarios.value;


        const ordenadas =
            [...tarjetas].sort(
                function (a, b) {

                    const nombreA =
                        normalizarTexto(
                            a.dataset.name
                        );

                    const nombreB =
                        normalizarTexto(
                            b.dataset.name
                        );

                    const objetivoA =
                        Number(
                            a.dataset.goal || 0
                        );

                    const objetivoB =
                        Number(
                            b.dataset.goal || 0
                        );

                    const fechaA =
                        Number(
                            a.dataset.endDate ||
                            9999999999
                        );

                    const fechaB =
                        Number(
                            b.dataset.endDate ||
                            9999999999
                        );

                    const progresoA =
                        Number(
                            a.dataset.progress || 0
                        );

                    const progresoB =
                        Number(
                            b.dataset.progress || 0
                        );

                    const tieneObjetivoA =
                        a.dataset.objective ===
                        'con_objetivo';

                    const tieneObjetivoB =
                        b.dataset.objective ===
                        'con_objetivo';


                    switch (tipo) {

                        case 'nombre_desc':

                            return nombreB.localeCompare(
                                nombreA,
                                'es'
                            );


                        case 'objetivo_desc':

                            return (
                                objetivoB -
                                objetivoA
                            );


                        case 'objetivo_asc':

                            /*
                             * Los que no tienen objetivo
                             * van después.
                             */
                            if (!tieneObjetivoA &&
                                tieneObjetivoB) {
                                return 1;
                            }

                            if (tieneObjetivoA &&
                                !tieneObjetivoB) {
                                return -1;
                            }

                            return (
                                objetivoA -
                                objetivoB
                            );


                        case 'progreso_desc':

                            return progresoB - progresoA;


                        case 'progreso_asc':

                            return progresoA - progresoB;


                        case 'vence_primero':

                            return fechaA - fechaB;


                        case 'sin_objetivo_primero':

                            if (
                                tieneObjetivoA ===
                                tieneObjetivoB
                            ) {
                                return nombreA.localeCompare(
                                    nombreB,
                                    'es'
                                );
                            }

                            return tieneObjetivoA
                                ? 1
                                : -1;


                        case 'nombre_asc':
                        default:

                            return nombreA.localeCompare(
                                nombreB,
                                'es'
                            );
                    }

                }
            );


        ordenadas.forEach(function (tarjeta) {
            grid.appendChild(tarjeta);
        });
    }


    /* =========================================================
       FILTROS RÁPIDOS
    ========================================================== */

    filtrosRapidos.forEach(
        function (tarjetaFiltro) {

            tarjetaFiltro.addEventListener(
                'click',
                function () {

                    const filtro =
                        this.dataset.quickFilter;


                    filtrosRapidos.forEach(
                        function (elemento) {
                            elemento.classList.remove(
                                'selected'
                            );
                        }
                    );


                    this.classList.add(
                        'selected'
                    );


                    if (!filtroObjetivo ||
                        !filtroSituacion) {
                        return;
                    }


                    filtroObjetivo.value =
                        'todos';

                    filtroSituacion.value =
                        'todos';


                    if (
                        filtro ===
                        'con_objetivo'
                    ) {
                        filtroObjetivo.value =
                            'con_objetivo';
                    }


                    if (
                        filtro ===
                        'sin_objetivo'
                    ) {
                        filtroObjetivo.value =
                            'sin_objetivo';
                    }


                    if (
                        filtro ===
                        'proximo_vencer'
                    ) {
                        filtroSituacion.value =
                            'proximo_vencer';
                    }


                    aplicarFiltros();

                }
            );

        }
    );


    /* =========================================================
       CAMBIOS EN FILTROS
    ========================================================== */

    [
        filtroObjetivo,
        filtroSituacion,
        filtroEstadoOperario,
        ordenOperarios
    ].forEach(function (control) {

        if (!control) {
            return;
        }

        control.addEventListener(
            'change',
            function () {

                filtrosRapidos.forEach(
                    function (elemento) {
                        elemento.classList.remove(
                            'selected'
                        );
                    }
                );

                aplicarFiltros();

            }
        );

    });


    if (filtroBusqueda) {

        filtroBusqueda.addEventListener(
            'input',
            aplicarFiltros
        );

    }


    /* =========================================================
       LIMPIAR FILTROS
    ========================================================== */

    if (limpiarFiltros) {

        limpiarFiltros.addEventListener(
            'click',
            function () {

                if (filtroBusqueda) {
                    filtroBusqueda.value = '';
                }

                if (filtroObjetivo) {
                    filtroObjetivo.value =
                        'todos';
                }

                if (filtroSituacion) {
                    filtroSituacion.value =
                        'todos';
                }

                if (filtroEstadoOperario) {
                    filtroEstadoOperario.value =
                        'todos';
                }

                if (ordenOperarios) {
                    ordenOperarios.value =
                        'nombre_asc';
                }


                filtrosRapidos.forEach(
                    function (elemento) {
                        elemento.classList.remove(
                            'selected'
                        );
                    }
                );


                const tarjetaTodos =
                    document.querySelector(
                        '[data-quick-filter="todos"]'
                    );

                if (tarjetaTodos) {
                    tarjetaTodos.classList.add(
                        'selected'
                    );
                }


                aplicarFiltros();

            }
        );

    }


    /* =========================================================
       FORMULARIOS DE OBJETIVOS
    ========================================================== */

    const formularios =
        document.querySelectorAll(
            '[data-objective-form]'
        );


    formularios.forEach(function (formulario) {

        const periodo =
            formulario.querySelector(
                '.periodo-select'
            );

        const fechaInicio =
            formulario.querySelector(
                '.fecha-inicio'
            );

        const fechaFin =
            formulario.querySelector(
                '.fecha-fin'
            );


        if (
            !periodo ||
            !fechaInicio ||
            !fechaFin
        ) {
            return;
        }


        function formatearFecha(fecha) {

            const year =
                fecha.getFullYear();

            const month =
                String(
                    fecha.getMonth() + 1
                ).padStart(
                    2,
                    '0'
                );

            const day =
                String(
                    fecha.getDate()
                ).padStart(
                    2,
                    '0'
                );


            return `${year}-${month}-${day}`;
        }


        function actualizarFechaFin() {

            if (!fechaInicio.value) {
                return;
            }


            if (
                periodo.value ===
                'personalizado'
            ) {
                fechaFin.readOnly = false;
                return;
            }


            const partes =
                fechaInicio.value.split('-');


            const fecha =
                new Date(
                    Number(partes[0]),
                    Number(partes[1]) - 1,
                    Number(partes[2])
                );


            if (
                periodo.value ===
                'semanal'
            ) {

                fecha.setDate(
                    fecha.getDate() + 7
                );

            }


            if (
                periodo.value ===
                'mensual'
            ) {

                fecha.setMonth(
                    fecha.getMonth() + 1
                );

            }


            fechaFin.value =
                formatearFecha(fecha);

            fechaFin.readOnly = true;
        }


        periodo.addEventListener(
            'change',
            actualizarFechaFin
        );

        fechaInicio.addEventListener(
            'change',
            actualizarFechaFin
        );


        actualizarFechaFin();

    });


    /*
    |--------------------------------------------------------------------------
    | INICIAR ORDEN
    |--------------------------------------------------------------------------
    */

    aplicarFiltros();

});
</script>

@endsection