@extends('layouts.app')

@section('content')

<style>
    .operario-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 32px 26px 50px;
        color: #f8fafc;
    }

    /* =========================================================
       HEADER
    ========================================================= */

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 28px;
    }

    .page-header-left {
        min-width: 0;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #64748b;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .breadcrumb i {
        width: 14px;
        height: 14px;
    }

    .breadcrumb span:last-child {
        color: #94a3b8;
    }

    .page-kicker {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #35c6ff;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.4px;
        margin-bottom: 7px;
    }

    .page-kicker-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 12px rgba(53, 198, 255, .65);
    }

    .page-title {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -0.7px;
        color: #f8fafc;
    }

    .page-subtitle {
        margin: 7px 0 0;
        color: #71829a;
        font-size: 14px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 15px;
        border-radius: 10px;
        border: 1px solid transparent;
        text-decoration: none;
        font-size: 13px;
        font-weight: 750;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn i {
        width: 16px;
        height: 16px;
    }

    .btn-secondary {
        color: #cbd5e1;
        background: #111c30;
        border-color: #1e2c43;
    }

    .btn-secondary:hover {
        color: #f8fafc;
        border-color: #33445e;
        background: #152238;
        transform: translateY(-1px);
    }

    .btn-primary {
        color: #07111f;
        background: #35c6ff;
        border-color: #35c6ff;
        box-shadow: 0 8px 24px rgba(53, 198, 255, .12);
    }

    .btn-primary:hover {
        background: #61d3ff;
        border-color: #61d3ff;
        transform: translateY(-1px);
        box-shadow: 0 10px 28px rgba(53, 198, 255, .18);
    }

    .btn-success {
        color: #071812;
        background: #34d399;
        border-color: #34d399;
    }

    .btn-success:hover {
        background: #5ee6b1;
        border-color: #5ee6b1;
        transform: translateY(-1px);
    }

    /* =========================================================
       ALERTAS
    ========================================================= */

    .alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 15px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 13px;
        border: 1px solid;
    }

    .alert i {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
    }

    .alert-success {
        color: #a7f3d0;
        background: rgba(16, 185, 129, .08);
        border-color: rgba(52, 211, 153, .20);
    }

    .alert-danger {
        color: #fecaca;
        background: rgba(248, 113, 113, .08);
        border-color: rgba(248, 113, 113, .20);
    }

    .errors-list {
        margin: 0;
        padding-left: 20px;
    }

    /* =========================================================
       CARDS
    ========================================================= */

    .content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(300px, .75fr);
        gap: 18px;
        margin-bottom: 18px;
    }

    .card {
        background: #0d182a;
        border: 1px solid #1b2a40;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 14px 35px rgba(0, 0, 0, .10);
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 19px 20px;
        border-bottom: 1px solid #18263a;
    }

    .card-title-wrap {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .card-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid rgba(53, 198, 255, .25);
        background: rgba(53, 198, 255, .07);
        color: #35c6ff;
    }

    .card-icon i {
        width: 18px;
        height: 18px;
    }

    .card-title {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: #f1f5f9;
    }

    .card-body {
        padding: 20px;
    }

    /* =========================================================
       INFO OPERARIO
    ========================================================= */

    .profile-layout {
        display: flex;
        align-items: flex-start;
        gap: 18px;
    }

    .avatar {
        width: 68px;
        height: 68px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #35c6ff;
        background: rgba(53, 198, 255, .08);
        border: 1px solid rgba(53, 198, 255, .25);
        box-shadow: inset 0 0 25px rgba(53, 198, 255, .025);
    }

    .avatar i {
        width: 30px;
        height: 30px;
    }

    .profile-name {
        margin: 2px 0 5px;
        font-size: 21px;
        font-weight: 800;
        color: #f8fafc;
    }

    .profile-role {
        color: #35c6ff;
        font-size: 12px;
        font-weight: 700;
    }

    .info-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
        margin-top: 22px;
    }

    .info-item {
        padding: 13px;
        border-radius: 11px;
        background: #101d31;
        border: 1px solid #1a2a40;
    }

    .info-label {
        color: #52627a;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .8px;
        margin-bottom: 6px;
    }

    .info-value {
        color: #dbe5f0;
        font-size: 13px;
        font-weight: 650;
        word-break: break-word;
    }

    /* =========================================================
       ESTADO
    ========================================================= */

    .status-card {
        height: 100%;
    }

    .status-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 220px;
        text-align: center;
    }

    .status-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }

    .status-icon i {
        width: 28px;
        height: 28px;
    }

    .status-icon.active {
        color: #34d399;
        background: rgba(52, 211, 153, .08);
        border: 1px solid rgba(52, 211, 153, .22);
    }

    .status-icon.inactive {
        color: #94a3b8;
        background: rgba(148, 163, 184, .08);
        border: 1px solid rgba(148, 163, 184, .20);
    }

    .status-title {
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .status-title.active {
        color: #6ee7b7;
    }

    .status-title.inactive {
        color: #cbd5e1;
    }

    .status-description {
        color: #64748b;
        font-size: 12px;
        margin-bottom: 18px;
    }

    /* =========================================================
       OBJETIVO
    ========================================================= */

    .objective-card {
        margin-bottom: 18px;
    }

    .objective-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        width: 100%;
    }

    .objective-header-left {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .objective-header-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        color: #fbbf24;
        background: rgba(251, 191, 36, .07);
        border: 1px solid rgba(251, 191, 36, .22);
    }

    .objective-header-icon i {
        width: 19px;
        height: 19px;
    }

    .objective-title {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: #f1f5f9;
    }

    .objective-subtitle {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 11px;
    }

    /* =========================================================
       OBJETIVO VACÍO
    ========================================================= */

    .objective-empty {
        padding: 20px;
        border-radius: 12px;
        border: 1px dashed #263750;
        background: rgba(16, 29, 49, .55);
    }

    .objective-empty-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .empty-icon {
        width: 46px;
        height: 46px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #64748b;
        background: rgba(100, 116, 139, .07);
        border: 1px solid rgba(100, 116, 139, .17);
    }

    .empty-icon i {
        width: 21px;
        height: 21px;
    }

    .empty-title {
        color: #cbd5e1;
        font-size: 14px;
        font-weight: 750;
        margin-bottom: 4px;
    }

    .empty-description {
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }

    .objective-empty-action {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    /* =========================================================
       ALERTA OBJETIVO VENCIDO
    ========================================================= */

    .expired-objective {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
        padding: 14px 15px;
        border-radius: 11px;
        background: rgba(248, 113, 113, .055);
        border: 1px solid rgba(248, 113, 113, .16);
    }

    .expired-objective-info {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .expired-objective-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 9px;
        color: #fca5a5;
        background: rgba(248, 113, 113, .07);
        border: 1px solid rgba(248, 113, 113, .15);
    }

    .expired-objective-icon i {
        width: 17px;
        height: 17px;
    }

    .expired-objective-title {
        color: #fca5a5;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 3px;
    }

    .expired-objective-text {
        color: #71829a;
        font-size: 11px;
        line-height: 1.4;
    }

    .expired-objective-date {
        color: #94a3b8;
        font-weight: 700;
    }

    /* =========================================================
       FORMULARIO OBJETIVO
    ========================================================= */

    .objective-form-wrapper {
        margin-top: 18px;
        padding: 18px;
        border-radius: 12px;
        background: #0b1626;
        border: 1px solid #1c2d44;
    }

    .objective-form-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        color: #dbe5f0;
        font-size: 13px;
        font-weight: 800;
    }

    .objective-form-title i {
        width: 16px;
        height: 16px;
        color: #35c6ff;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .form-label {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 750;
    }

    .form-control {
        width: 100%;
        min-height: 40px;
        padding: 0 12px;
        color: #e2e8f0;
        background: #101d31;
        border: 1px solid #243650;
        border-radius: 9px;
        outline: none;
        font-size: 13px;
        transition: .2s ease;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: rgba(53, 198, 255, .65);
        box-shadow: 0 0 0 3px rgba(53, 198, 255, .08);
    }

    .form-help {
        color: #52627a;
        font-size: 10px;
    }

    .objective-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 17px;
        padding-top: 16px;
        border-top: 1px solid #18263a;
    }

    /* =========================================================
       OBJETIVO ACTIVO
    ========================================================= */

    .objective-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .objective-stat {
        padding: 15px;
        border-radius: 11px;
        background: #101d31;
        border: 1px solid #1a2a40;
    }

    .objective-stat-label {
        color: #52627a;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .7px;
        margin-bottom: 7px;
    }

    .objective-stat-value {
        color: #f8fafc;
        font-size: 20px;
        font-weight: 850;
    }

    .objective-stat-value.cyan {
        color: #35c6ff;
    }

    .objective-stat-value.green {
        color: #34d399;
    }

    .objective-stat-value.yellow {
        color: #fbbf24;
    }

    .progress-section {
        padding: 17px;
        border-radius: 12px;
        background: #101d31;
        border: 1px solid #1a2a40;
    }

    .progress-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 9px;
    }

    .progress-label {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
    }

    .progress-value {
        color: #35c6ff;
        font-size: 12px;
        font-weight: 800;
    }

    .progress-track {
        width: 100%;
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #18263a;
    }

    .progress-bar {
        height: 100%;
        border-radius: inherit;
        background: #35c6ff;
        box-shadow: 0 0 12px rgba(53, 198, 255, .28);
        transition: width .3s ease;
    }

    .objective-status {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-top: 13px;
        padding: 11px 13px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
    }

    .objective-status i {
        width: 16px;
        height: 16px;
    }

    .objective-status.cumplido {
        color: #86efac;
        background: rgba(52, 211, 153, .07);
        border: 1px solid rgba(52, 211, 153, .17);
    }

    .objective-status.proximo {
        color: #fde68a;
        background: rgba(251, 191, 36, .07);
        border: 1px solid rgba(251, 191, 36, .17);
    }

    .objective-status.vencido {
        color: #fca5a5;
        background: rgba(248, 113, 113, .07);
        border: 1px solid rgba(248, 113, 113, .17);
    }

    .objective-status.progreso {
        color: #7dd3fc;
        background: rgba(53, 198, 255, .07);
        border: 1px solid rgba(53, 198, 255, .17);
    }

    /* =========================================================
       DETAILS / CAMBIAR OBJETIVO
    ========================================================= */

    .change-objective {
        margin-top: 17px;
    }

    .change-objective summary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 9px 12px;
        border-radius: 9px;
        color: #94a3b8;
        background: #101d31;
        border: 1px solid #243650;
        font-size: 12px;
        font-weight: 750;
        cursor: pointer;
        list-style: none;
        transition: .2s ease;
    }

    .change-objective summary::-webkit-details-marker {
        display: none;
    }

    .change-objective summary:hover {
        color: #35c6ff;
        border-color: rgba(53, 198, 255, .30);
        background: rgba(53, 198, 255, .04);
    }

    .change-objective summary i {
        width: 15px;
        height: 15px;
    }

    /* =========================================================
       ACTIVIDAD / ORGANIZACIÓN
    ========================================================= */

    .bottom-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(300px, .75fr);
        gap: 18px;
        margin-bottom: 18px;
    }

    .activity-empty {
        min-height: 145px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
        gap: 8px;
        color: #64748b;
    }

    .activity-empty i {
        width: 27px;
        height: 27px;
        opacity: .7;
    }

    .activity-empty-title {
        color: #94a3b8;
        font-size: 13px;
        font-weight: 700;
    }

    .activity-empty-text {
        font-size: 11px;
    }

    .company-box {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        padding: 15px;
        border-radius: 11px;
        background: #101d31;
        border: 1px solid #1a2a40;
    }

    .company-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 11px;
        color: #35c6ff;
        background: rgba(53, 198, 255, .07);
        border: 1px solid rgba(53, 198, 255, .20);
    }

    .company-icon i {
        width: 20px;
        height: 20px;
    }

    .company-name {
        color: #f1f5f9;
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .company-text {
        color: #64748b;
        font-size: 11px;
        line-height: 1.5;
    }

    /* =========================================================
       HISTORIAL OBJETIVOS
    ========================================================= */

    .history-card {
        margin-top: 18px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .history-table th {
        padding: 12px 15px;
        text-align: left;
        color: #52627a;
        background: #0b1626;
        border-bottom: 1px solid #1a2a40;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .7px;
        font-weight: 800;
    }

    .history-table td {
        padding: 14px 15px;
        color: #cbd5e1;
        border-bottom: 1px solid #142237;
        font-size: 12px;
    }

    .history-table tr:last-child td {
        border-bottom: none;
    }

    .history-table tbody tr {
        transition: .2s ease;
    }

    .history-table tbody tr:hover {
        background: rgba(53, 198, 255, .025);
    }

    .history-number {
        color: #35c6ff;
        font-weight: 850;
    }

    .history-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .history-status.pendiente {
        color: #fde68a;
        background: rgba(251, 191, 36, .08);
    }

    .history-status.en_progreso {
        color: #7dd3fc;
        background: rgba(53, 198, 255, .08);
    }

    .history-status.cumplido {
        color: #86efac;
        background: rgba(52, 211, 153, .08);
    }

    .history-status.vencido {
        color: #fca5a5;
        background: rgba(248, 113, 113, .08);
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1050px) {
        .content-grid,
        .bottom-grid {
            grid-template-columns: 1fr;
        }

        .objective-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .operario-page {
            padding: 22px 15px 35px;
        }

        .page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions .btn {
            flex: 1;
        }

        .info-list,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .objective-empty-info {
            align-items: flex-start;
        }

        .objective-empty-action {
            justify-content: stretch;
        }

        .objective-empty-action .btn {
            width: 100%;
        }

        .objective-stats {
            grid-template-columns: 1fr 1fr;
        }

        .objective-header {
            align-items: flex-start;
        }

        .profile-layout {
            flex-direction: column;
        }

        .expired-objective {
            align-items: flex-start;
            flex-direction: column;
        }

        .expired-objective .btn {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .objective-stats {
            grid-template-columns: 1fr;
        }

        .objective-form-actions {
            flex-direction: column;
        }

        .objective-form-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="operario-page">

    {{-- =====================================================
         ALERTAS
    ====================================================== --}}

    @if(session('success'))
        <div class="alert alert-success">
            <i data-lucide="check-circle-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i data-lucide="circle-alert"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <i data-lucide="circle-alert"></i>

            <ul class="errors-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="page-header">

        <div class="page-header-left">

            <div class="breadcrumb">
                <span>Gestión del equipo</span>
                <i data-lucide="chevron-right"></i>
                <span>Operarios</span>
                <i data-lucide="chevron-right"></i>
                <span>{{ $operario->name }}</span>
            </div>

            <div class="page-kicker">
                <span class="page-kicker-dot"></span>
                Información del operario
            </div>

            <h1 class="page-title">
                {{ $operario->name }}
            </h1>

            <p class="page-subtitle">
                Consulta la información, estado y seguimiento del operario.
            </p>

        </div>

        <div class="header-actions">

            <a
                href="{{ route('operarios.index') }}"
                class="btn btn-secondary"
            >
                <i data-lucide="arrow-left"></i>
                Regresar
            </a>

            @if(in_array(auth()->user()->id_rol, [1, 2]))

                <a
                    href="{{ route('operarios.edit', $operario) }}"
                    class="btn btn-primary"
                >
                    <i data-lucide="pencil"></i>
                    Editar operario
                </a>

            @endif

        </div>

    </div>


    {{-- =====================================================
         INFORMACIÓN + ESTADO
    ====================================================== --}}

    <div class="content-grid">

        {{-- INFORMACIÓN --}}

        <div class="card">

            <div class="card-header">

                <div class="card-title-wrap">

                    <div class="card-icon">
                        <i data-lucide="user-round"></i>
                    </div>

                    <h2 class="card-title">
                        Información del operario
                    </h2>

                </div>

            </div>

            <div class="card-body">

                <div class="profile-layout">

                    <div class="avatar">
                        <i data-lucide="user-round"></i>
                    </div>

                    <div>

                        <div class="profile-name">
                            {{ $operario->name }}
                        </div>

                        <div class="profile-role">
                            Operario
                        </div>

                    </div>

                </div>

                <div class="info-list">

                    <div class="info-item">

                        <div class="info-label">
                            Correo electrónico
                        </div>

                        <div class="info-value">
                            {{ $operario->email }}
                        </div>

                    </div>

                    <div class="info-item">

                        <div class="info-label">
                            Empresa
                        </div>

                        <div class="info-value">
                            {{ $operario->empresa->nombre ?? 'Sin empresa asignada' }}
                        </div>

                    </div>

                    <div class="info-item">

                        <div class="info-label">
                            Rol
                        </div>

                        <div class="info-value">
                            Operario
                        </div>

                    </div>

                    <div class="info-item">

                        <div class="info-label">
                            ID del operario
                        </div>

                        <div class="info-value">
                            #{{ $operario->id }}
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ESTADO --}}

        <div class="card status-card">

            <div class="card-header">

                <div class="card-title-wrap">

                    <div class="card-icon">
                        <i data-lucide="activity"></i>
                    </div>

                    <h2 class="card-title">
                        Estado
                    </h2>

                </div>

            </div>

            <div class="card-body">

                <div class="status-content">

                    @if($operario->estado)

                        <div class="status-icon active">
                            <i data-lucide="circle-check"></i>
                        </div>

                        <div class="status-title active">
                            Activo
                        </div>

                        <div class="status-description">
                            El operario puede realizar actividades.
                        </div>

                    @else

                        <div class="status-icon inactive">
                            <i data-lucide="circle-off"></i>
                        </div>

                        <div class="status-title inactive">
                            Inactivo
                        </div>

                        <div class="status-description">
                            El operario está deshabilitado.
                        </div>

                    @endif


                    @if(in_array(auth()->user()->id_rol, [1, 2]))

                        <form
                            action="{{ route('operarios.toggleEstado', $operario) }}"
                            method="POST"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn {{ $operario->estado ? 'btn-secondary' : 'btn-success' }}"
                            >

                                @if($operario->estado)

                                    <i data-lucide="user-round-x"></i>
                                    Desactivar operario

                                @else

                                    <i data-lucide="user-round-check"></i>
                                    Activar operario

                                @endif

                            </button>

                        </form>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         OBJETIVO DE LLAMADAS
    ====================================================== --}}

    <div class="card objective-card">

        <div class="card-header">

            <div class="objective-header">

                <div class="objective-header-left">

                    <div class="objective-header-icon">
                        <i data-lucide="phone-call"></i>
                    </div>

                    <div>

                        <h2 class="objective-title">
                            Seguimiento / Objetivo de llamadas
                        </h2>

                        <p class="objective-subtitle">
                            Control del objetivo de llamadas asignado al operario.
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <div class="card-body">

            @php
                /*
                 * Buscamos el último objetivo vencido directamente
                 * desde el historial para poder mostrar la alerta
                 * cuando ya terminó su periodo.
                 */
                $objetivoVencido = isset($objetivos)
                    ? $objetivos
                        ->where('estado', 'vencido')
                        ->sortByDesc('fecha_fin')
                        ->first()
                    : null;
            @endphp


            {{-- =================================================
                 SIN OBJETIVO ACTIVO
            ================================================== --}}

            @if(!$objetivoActual)

                @if($objetivoVencido)

                    {{-- =============================================
                         ALERTA DE OBJETIVO VENCIDO
                    ============================================== --}}

                    <div class="expired-objective">

                        <div class="expired-objective-info">

                            <div class="expired-objective-icon">
                                <i data-lucide="triangle-alert"></i>
                            </div>

                            <div>

                                <div class="expired-objective-title">
                                    Objetivo vencido
                                </div>

                                <div class="expired-objective-text">
                                    El periodo establecido para este objetivo terminó el
                                    <span class="expired-objective-date">
                                        {{ \Carbon\Carbon::parse($objetivoVencido->fecha_fin)->format('d/m/Y') }}
                                    </span>.
                                </div>

                            </div>

                        </div>

                    </div>

                @else

                    {{-- =============================================
                         REALMENTE NO EXISTE OBJETIVO
                    ============================================== --}}

                    <div class="objective-empty-info">

                        <div class="empty-icon">
                            <i data-lucide="target"></i>
                        </div>

                        <div>

                            <div class="empty-title">
                                No hay un objetivo activo
                            </div>

                            <div class="empty-description">
                                Este operario todavía no tiene un objetivo de llamadas asignado.
                            </div>

                        </div>

                    </div>

                @endif


                {{-- =============================================
                     BOTÓN AGREGAR OBJETIVO
                ============================================== --}}

                @if(in_array(auth()->user()->id_rol, [1, 2]))

                    <div class="objective-empty-action">

                        <details>

                            <summary class="btn btn-primary">

                                <i data-lucide="plus"></i>
                                {{ $objetivoVencido ? 'Agregar nuevo objetivo' : 'Agregar objetivo' }}

                            </summary>


                            <div class="objective-form-wrapper">

                                <div class="objective-form-title">

                                    <i data-lucide="target"></i>

                                    Nuevo objetivo de llamadas

                                </div>


                                <form
                                    action="{{ route('operarios.asignarObjetivo', $operario) }}"
                                    method="POST"
                                >

                                    @csrf

                                    <div class="form-grid">

                                        {{-- CANTIDAD --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Objetivo de llamadas
                                            </label>

                                            <input
                                                type="number"
                                                name="objetivo_llamadas"
                                                class="form-control"
                                                min="1"
                                                max="100000"
                                                value="{{ old('objetivo_llamadas') }}"
                                                placeholder="Ej. 100"
                                                required
                                            >

                                            <span class="form-help">
                                                Cantidad de llamadas que debe realizar.
                                            </span>

                                        </div>


                                        {{-- PERIODO --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Periodo
                                            </label>

                                            <select
                                                name="periodo"
                                                class="form-control"
                                                required
                                            >

                                                <option
                                                    value="semanal"
                                                    {{ old('periodo', 'semanal') === 'semanal' ? 'selected' : '' }}
                                                >
                                                    Semanal
                                                </option>

                                                <option
                                                    value="mensual"
                                                    {{ old('periodo') === 'mensual' ? 'selected' : '' }}
                                                >
                                                    Mensual
                                                </option>

                                                <option
                                                    value="personalizado"
                                                    {{ old('periodo') === 'personalizado' ? 'selected' : '' }}
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
                                                class="form-control"
                                                value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}"
                                                required
                                            >

                                        </div>


                                        {{-- FECHA FIN --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Fecha de finalización
                                            </label>

                                            <input
                                                type="date"
                                                name="fecha_fin"
                                                class="form-control"
                                                value="{{ old('fecha_fin') }}"
                                                required
                                            >

                                        </div>

                                    </div>


                                    <div class="objective-form-actions">

                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >

                                            <i data-lucide="check"></i>
                                            Guardar objetivo

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </details>

                    </div>

                @endif


            {{-- =================================================
                 CON OBJETIVO ACTIVO
            ================================================== --}}

            @else

                @php

                    $objetivo = (int) $objetivoActual->objetivo_llamadas;

                    $realizadas = (int) ($llamadasRealizadas ?? 0);

                    $restantes = max(
                        $objetivo - $realizadas,
                        0
                    );

                    $progreso = $objetivo > 0
                        ? min(
                            ($realizadas / $objetivo) * 100,
                            100
                        )
                        : 0;

                @endphp


                {{-- ESTADÍSTICAS --}}

                <div class="objective-stats">

                    <div class="objective-stat">

                        <div class="objective-stat-label">
                            Objetivo
                        </div>

                        <div class="objective-stat-value cyan">
                            {{ $objetivo }}
                        </div>

                    </div>


                    <div class="objective-stat">

                        <div class="objective-stat-label">
                            Realizadas
                        </div>

                        <div class="objective-stat-value green">
                            {{ $realizadas }}
                        </div>

                    </div>


                    <div class="objective-stat">

                        <div class="objective-stat-label">
                            Restantes
                        </div>

                        <div class="objective-stat-value yellow">
                            {{ $restantes }}
                        </div>

                    </div>


                    <div class="objective-stat">

                        <div class="objective-stat-label">
                            Periodo
                        </div>

                        <div class="objective-stat-value">

                            @if($objetivoActual->periodo === 'semanal')

                                Semanal

                            @elseif($objetivoActual->periodo === 'mensual')

                                Mensual

                            @else

                                Personalizado

                            @endif

                        </div>

                    </div>

                </div>


                {{-- PROGRESO --}}

                <div class="progress-section">

                    <div class="progress-top">

                        <span class="progress-label">
                            Progreso del objetivo
                        </span>

                        <span class="progress-value">
                            {{ number_format($progreso, 0) }}%
                        </span>

                    </div>


                    <div class="progress-track">

                        <div
                            class="progress-bar"
                            style="width: {{ $progreso }}%;"
                        ></div>

                    </div>


                    {{-- ESTADO DEL OBJETIVO --}}

                    @if(($situacionObjetivo ?? null) === 'cumplido')

                        <div class="objective-status cumplido">

                            <i data-lucide="circle-check"></i>

                            <span>
                                Objetivo cumplido.
                            </span>

                        </div>

                    @elseif(($situacionObjetivo ?? null) === 'proximo_vencer')

                        <div class="objective-status proximo">

                            <i data-lucide="clock-3"></i>

                            <span>
                                El objetivo está próximo a vencer.
                            </span>

                        </div>

                    @elseif(($situacionObjetivo ?? null) === 'vencido')

                        <div class="objective-status vencido">

                            <i data-lucide="triangle-alert"></i>

                            <span>
                                El objetivo ya venció.
                            </span>

                        </div>

                    @else

                        <div class="objective-status progreso">

                            <i data-lucide="trending-up"></i>

                            <span>
                                Objetivo en progreso.
                            </span>

                        </div>

                    @endif

                </div>


                {{-- =================================================
                     CAMBIAR OBJETIVO
                ================================================== --}}

                @if(in_array(auth()->user()->id_rol, [1, 2]))

                    <details class="change-objective">

                        <summary>

                            <i data-lucide="pencil"></i>
                            Cambiar objetivo

                        </summary>


                        <div class="objective-form-wrapper">

                            <div class="objective-form-title">

                                <i data-lucide="target"></i>

                                Asignar nuevo objetivo

                            </div>


                            <form
                                action="{{ route('operarios.asignarObjetivo', $operario) }}"
                                method="POST"
                            >

                                @csrf

                                <div class="form-grid">

                                    {{-- CANTIDAD --}}

                                    <div class="form-group">

                                        <label class="form-label">
                                            Objetivo de llamadas
                                        </label>

                                        <input
                                            type="number"
                                            name="objetivo_llamadas"
                                            class="form-control"
                                            min="1"
                                            max="100000"
                                            value="{{ old('objetivo_llamadas') }}"
                                            placeholder="Ej. 100"
                                            required
                                        >

                                        <span class="form-help">
                                            El objetivo anterior se conservará en el historial.
                                        </span>

                                    </div>


                                    {{-- PERIODO --}}

                                    <div class="form-group">

                                        <label class="form-label">
                                            Periodo
                                        </label>

                                        <select
                                            name="periodo"
                                            class="form-control"
                                            required
                                        >

                                            <option
                                                value="semanal"
                                                {{ old('periodo', 'semanal') === 'semanal' ? 'selected' : '' }}
                                            >
                                                Semanal
                                            </option>

                                            <option
                                                value="mensual"
                                                {{ old('periodo') === 'mensual' ? 'selected' : '' }}
                                            >
                                                Mensual
                                            </option>

                                            <option
                                                value="personalizado"
                                                {{ old('periodo') === 'personalizado' ? 'selected' : '' }}
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
                                            class="form-control"
                                            value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}"
                                            required
                                        >

                                    </div>


                                    {{-- FECHA FIN --}}

                                    <div class="form-group">

                                        <label class="form-label">
                                            Fecha de finalización
                                        </label>

                                        <input
                                            type="date"
                                            name="fecha_fin"
                                            class="form-control"
                                            value="{{ old('fecha_fin') }}"
                                            required
                                        >

                                    </div>

                                </div>


                                <div class="objective-form-actions">

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >

                                        <i data-lucide="save"></i>

                                        Guardar nuevo objetivo

                                    </button>

                                </div>

                            </form>

                        </div>

                    </details>

                @endif

            @endif

        </div>

    </div>


    {{-- =====================================================
         ACTIVIDAD + ORGANIZACIÓN
    ====================================================== --}}

    <div class="bottom-grid">

        {{-- ACTIVIDAD --}}

        <div class="card">

            <div class="card-header">

                <div class="card-title-wrap">

                    <div class="card-icon">
                        <i data-lucide="phone-call"></i>
                    </div>

                    <h2 class="card-title">
                        Actividad
                    </h2>

                </div>

            </div>

            <div class="card-body">

                <div class="activity-empty">

                    <i data-lucide="phone"></i>

                    <div class="activity-empty-title">
                        Llamadas realizadas
                    </div>

                    <div class="activity-empty-text">
                        Aquí se mostrará la actividad registrada por el operario.
                    </div>

                </div>

            </div>

        </div>


        {{-- ORGANIZACIÓN --}}

        <div class="card">

            <div class="card-header">

                <div class="card-title-wrap">

                    <div class="card-icon">
                        <i data-lucide="building-2"></i>
                    </div>

                    <h2 class="card-title">
                        Organización
                    </h2>

                </div>

            </div>

            <div class="card-body">

                <div class="company-box">

                    <div class="company-icon">
                        <i data-lucide="building-2"></i>
                    </div>

                    <div>

                        <div class="company-name">
                            {{ $operario->empresa->nombre ?? 'Sin empresa asignada' }}
                        </div>

                        <div class="company-text">
                            Empresa a la que pertenece actualmente este operario.
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         HISTORIAL DE OBJETIVOS
    ====================================================== --}}

    @if(isset($objetivos) && $objetivos->count() > 0)

        <div class="card history-card">

            <div class="card-header">

                <div class="card-title-wrap">

                    <div class="card-icon">
                        <i data-lucide="history"></i>
                    </div>

                    <h2 class="card-title">
                        Historial de objetivos
                    </h2>

                </div>

            </div>


            <div class="table-wrapper">

                <table class="history-table">

                    <thead>

                        <tr>

                            <th>
                                Objetivo
                            </th>

                            <th>
                                Periodo
                            </th>

                            <th>
                                Inicio
                            </th>

                            <th>
                                Fin
                            </th>

                            <th>
                                Estado
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($objetivos as $objetivo)

                            <tr>

                                <td>

                                    <span class="history-number">

                                        {{ $objetivo->objetivo_llamadas }}
                                        llamadas

                                    </span>

                                </td>


                                <td>

                                    @if($objetivo->periodo === 'semanal')

                                        Semanal

                                    @elseif($objetivo->periodo === 'mensual')

                                        Mensual

                                    @else

                                        Personalizado

                                    @endif

                                </td>


                                <td>

                                    {{ \Carbon\Carbon::parse($objetivo->fecha_inicio)->format('d/m/Y') }}

                                </td>


                                <td>

                                    {{ \Carbon\Carbon::parse($objetivo->fecha_fin)->format('d/m/Y') }}

                                </td>


                                <td>

                                    <span class="history-status {{ $objetivo->estado }}">

                                        @if($objetivo->estado === 'pendiente')

                                            Pendiente

                                        @elseif($objetivo->estado === 'en_progreso')

                                            En progreso

                                        @elseif($objetivo->estado === 'cumplido')

                                            Cumplido

                                        @elseif($objetivo->estado === 'vencido')

                                            Vencido

                                        @else

                                            {{ ucfirst(str_replace('_', ' ', $objetivo->estado)) }}

                                        @endif

                                    </span>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    @endif

</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    });
</script>

@endsection