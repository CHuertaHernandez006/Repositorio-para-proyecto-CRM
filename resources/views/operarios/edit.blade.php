@extends('layouts.app')

@section('title', 'Editar operario - CRM')
@section('header-title', 'Editar operario')

@section('content')

<style>
    .editar-operario-page {
        min-height: 100%;
        padding: 40px;
        color: #ffffff;
        background: #0b1424;
    }

    /* =========================================================
       HEADER
    ========================================================== */

    .editar-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }

    .breadcrumb {
        margin-bottom: 8px;

        color: #8190a7;

        font-size: 12px;
        font-weight: 600;

        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .breadcrumb-blue {
        color: #35c6ff;
    }

    .editar-title {
        margin: 0;

        color: #ffffff;

        font-size: 32px;
        font-weight: 700;

        letter-spacing: -.5px;
    }

    .editar-subtitle {
        margin: 8px 0 0;

        color: #8190a7;

        font-size: 14px;
    }

    .btn-regresar {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 11px 16px;

        border: 1px solid #334155;
        border-radius: 12px;

        background: #111c30;
        color: #cbd5e1;

        text-decoration: none;

        font-size: 14px;
        font-weight: 600;

        transition: .2s;
    }

    .btn-regresar:hover {
        border-color: rgba(53, 198, 255, .35);
        color: #35c6ff;
    }

    .btn-regresar svg {
        width: 15px;
        height: 15px;
    }


    /* =========================================================
       ALERTAS
    ========================================================== */

    .alert-error {
        margin-bottom: 20px;

        padding: 16px 18px;

        border: 1px solid rgba(244, 63, 94, .25);
        border-radius: 14px;

        background: rgba(244, 63, 94, .08);
    }

    .alert-error-title {
        margin-bottom: 8px;

        color: #fb7185;

        font-size: 14px;
        font-weight: 700;
    }

    .alert-error ul {
        margin: 0;

        padding-left: 20px;

        color: #cbd5e1;

        font-size: 13px;
    }

    .alert-success {
        margin-bottom: 20px;

        padding: 15px 18px;

        border: 1px solid rgba(52, 211, 153, .2);
        border-radius: 14px;

        background: rgba(52, 211, 153, .08);
        color: #34d399;

        font-size: 14px;
        font-weight: 600;
    }


    /* =========================================================
       LAYOUT
    ========================================================== */

    .editar-grid {
        display: grid;

        grid-template-columns:
            minmax(0, 2fr)
            minmax(280px, 1fr);

        gap: 20px;

        align-items: start;
    }

    .panel {
        overflow: hidden;

        border: 1px solid rgba(53, 198, 255, .18);
        border-radius: 16px;

        background: #111c30;
    }

    .panel-header {
        padding: 25px;

        border-bottom: 1px solid #27364b;
    }

    .panel-body {
        padding: 25px;
    }


    /* =========================================================
       CABECERA DEL FORMULARIO
    ========================================================== */

    .operario-heading {
        display: flex;
        align-items: center;

        gap: 15px;
    }

    .operario-avatar {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 56px;
        height: 56px;

        min-width: 56px;

        border: 1px solid rgba(53, 198, 255, .3);
        border-radius: 14px;

        background: rgba(53, 198, 255, .1);
        color: #35c6ff;

        font-size: 23px;
        font-weight: 700;
    }

    .operario-heading-title {
        margin: 0;

        color: #ffffff;

        font-size: 19px;
        font-weight: 700;
    }

    .operario-heading-text {
        margin: 5px 0 0;

        color: #8190a7;

        font-size: 13px;
    }


    /* =========================================================
       CAMPOS
    ========================================================== */

    .form-group {
        margin-bottom: 22px;
    }

    .form-label {
        display: block;

        margin-bottom: 8px;

        color: #cbd5e1;

        font-size: 13px;
        font-weight: 600;
    }

    .required {
        color: #35c6ff;
    }

    .form-input {
        width: 100%;

        box-sizing: border-box;

        padding: 13px 15px;

        border: 1px solid #334155;
        border-radius: 11px;

        outline: none;

        background: #0c1628;
        color: #ffffff;

        font-family: inherit;
        font-size: 14px;

        transition: .2s;
    }

    .form-input:focus {
        border-color: rgba(53, 198, 255, .55);

        box-shadow:
            0 0 0 3px
            rgba(53, 198, 255, .05);
    }

    .form-static {
        width: 100%;

        box-sizing: border-box;

        padding: 13px 15px;

        border: 1px solid #334155;
        border-radius: 11px;

        background: #0c1628;
        color: #ffffff;

        font-size: 14px;
    }

    .field-helper {
        margin: 7px 0 0;

        color: #64748b;

        font-size: 12px;
        line-height: 1.55;
    }

    .field-error {
        margin: 7px 0 0;

        color: #fb7185;

        font-size: 12px;
    }


    /* =========================================================
       SECCIÓN
    ========================================================== */

    .section-header {
        display: flex;
        align-items: flex-start;

        gap: 11px;

        margin-bottom: 15px;
    }

    .section-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 34px;
        height: 34px;

        flex: 0 0 34px;

        border-radius: 9px;

        background: rgba(53, 198, 255, .08);
        color: #35c6ff;
    }

    .section-icon svg {
        width: 16px;
        height: 16px;
    }

    .section-title {
        margin: 0;

        color: #ffffff;

        font-size: 14px;
        font-weight: 700;
    }

    .section-description {
        margin: 4px 0 0;

        color: #64748b;

        font-size: 11px;
        line-height: 1.5;
    }


    /* =========================================================
       TIPOS DE OPERARIO
    ========================================================== */

    .tipo-grid {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 12px;
    }

    .tipo-option {
        position: relative;

        display: block;

        cursor: pointer;
    }

    .tipo-option input {
        position: absolute;

        opacity: 0;

        pointer-events: none;
    }

    .tipo-card {
        position: relative;

        display: flex;
        align-items: flex-start;

        gap: 12px;

        min-height: 115px;

        padding: 15px;

        border:
            1px solid
            rgba(255, 255, 255, .065);

        border-radius: 12px;

        background: #0c1628;

        transition: .2s ease;

        box-sizing: border-box;
    }

    .tipo-option:hover .tipo-card {
        border-color:
            rgba(53, 198, 255, .22);

        background:
            rgba(53, 198, 255, .025);
    }

    .tipo-option input:checked + .tipo-card {
        border-color:
            rgba(53, 198, 255, .48);

        background:
            rgba(53, 198, 255, .07);

        box-shadow:
            0 0 0 3px
            rgba(53, 198, 255, .035);
    }

    .tipo-card-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 40px;
        height: 40px;

        flex: 0 0 40px;

        border-radius: 10px;

        background:
            rgba(53, 198, 255, .08);

        color: #35c6ff;
    }

    .tipo-card-icon svg {
        width: 18px;
        height: 18px;
    }

    .tipo-card-content {
        flex: 1;

        min-width: 0;

        padding-right: 23px;
    }

    .tipo-card-name {
        margin: 1px 0 0;

        color: #eef5fc;

        font-size: 12px;
        font-weight: 700;
    }

    .tipo-card-description {
        margin: 5px 0 0;

        color: #68788e;

        font-size: 10px;
        line-height: 1.55;
    }

    .tipo-card-check {
        position: absolute;

        top: 12px;
        right: 12px;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 18px;
        height: 18px;

        border:
            1px solid
            rgba(148, 163, 184, .18);

        border-radius: 50%;

        color: transparent;

        transition: .2s ease;
    }

    .tipo-card-check svg {
        width: 10px;
        height: 10px;
    }

    .tipo-option input:checked
    + .tipo-card
    .tipo-card-check {
        border-color: #35c6ff;

        background: #35c6ff;
        color: #06111e;
    }

    .tipo-info {
        display: flex;
        align-items: flex-start;

        gap: 9px;

        margin-top: 11px;

        padding: 10px 12px;

        border:
            1px solid
            rgba(255,255,255,.045);

        border-radius: 9px;

        background:
            rgba(255,255,255,.018);

        color: #64748b;

        font-size: 10px;
        line-height: 1.55;
    }

    .tipo-info svg {
        width: 13px;
        height: 13px;

        flex: 0 0 13px;

        margin-top: 1px;

        color: #35c6ff;
    }

    .reapproval-warning {
        display: flex;
        align-items: flex-start;

        gap: 8px;

        margin-top: 10px;

        padding: 10px 12px;

        border:
            1px solid
            rgba(251, 191, 36, .13);

        border-radius: 9px;

        background:
            rgba(251, 191, 36, .045);

        color: #cba84c;

        font-size: 10px;
        line-height: 1.55;
    }

    .reapproval-warning svg {
        width: 13px;
        height: 13px;

        flex: 0 0 13px;

        margin-top: 1px;

        color: #fbbf24;
    }


    /* =========================================================
       PASSWORD
    ========================================================== */

    .password-section {
        margin-bottom: 22px;

        padding-top: 3px;
    }

    .password-header {
        margin-bottom: 15px;

        padding-bottom: 12px;

        border-bottom: 1px solid #27364b;
    }

    .password-title {
        margin: 0;

        color: #ffffff;

        font-size: 15px;
        font-weight: 700;
    }

    .password-description {
        margin: 5px 0 0;

        color: #8190a7;

        font-size: 12px;
    }

    .password-rules {
        margin-top: 10px;

        padding: 12px 14px;

        border: 1px solid #27364b;
        border-radius: 10px;

        background: #0c1628;
    }

    .password-rules-title {
        margin: 0 0 8px;

        color: #94a3b8;

        font-size: 12px;
        font-weight: 600;
    }

    .password-rule {
        margin-bottom: 5px;

        color: #64748b;

        font-size: 12px;
    }

    .password-rule:last-child {
        margin-bottom: 0;
    }

    .password-match {
        display: none;

        margin: 7px 0 0;

        font-size: 12px;
    }


    /* =========================================================
       BOTONES
    ========================================================== */

    .form-actions {
        display: flex;
        justify-content: flex-end;

        gap: 10px;

        flex-wrap: wrap;

        padding-top: 20px;

        border-top: 1px solid #27364b;
    }

    .btn-cancelar,
    .btn-guardar {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        padding: 12px 18px;

        border-radius: 11px;

        font-size: 14px;
        font-weight: 600;
    }

    .btn-cancelar {
        border: 1px solid #334155;

        background: #0c1628;
        color: #cbd5e1;

        text-decoration: none;
    }

    .btn-guardar {
        border: 0;

        background: #35c6ff;
        color: #07111f;

        font-weight: 700;

        cursor: pointer;

        transition: .2s ease;
    }

    .btn-guardar:hover {
        background: #65d4ff;

        transform: translateY(-1px);
    }

    .btn-cancelar svg,
    .btn-guardar svg {
        width: 14px;
        height: 14px;
    }


    /* =========================================================
       PANEL LATERAL
    ========================================================== */

    .summary-eyebrow {
        margin: 0;

        color: #8190a7;

        font-size: 11px;
        font-weight: 600;

        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .summary-title {
        margin: 6px 0 0;

        color: #ffffff;

        font-size: 18px;
        font-weight: 700;
    }

    .summary-item {
        margin-bottom: 20px;
    }

    .summary-item:last-child {
        margin-bottom: 0;
    }

    .summary-label {
        margin: 0 0 6px;

        color: #64748b;

        font-size: 11px;
        font-weight: 600;

        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .summary-value {
        margin: 0;

        color: #ffffff;

        font-size: 14px;
        font-weight: 600;

        word-break: break-word;
    }

    .summary-value.secondary {
        color: #cbd5e1;

        font-size: 13px;
    }

    .summary-value.blue {
        color: #35c6ff;
    }

    .tipo-badge {
        display: inline-flex;
        align-items: center;

        gap: 7px;

        padding: 7px 10px;

        border:
            1px solid
            rgba(53, 198, 255, .14);

        border-radius: 8px;

        background:
            rgba(53, 198, 255, .06);

        color: #55ceff;

        font-size: 12px;
        font-weight: 600;
    }

    .tipo-badge-dot {
        width: 6px;
        height: 6px;

        border-radius: 50%;

        background: #35c6ff;

        box-shadow:
            0 0 7px
            rgba(53, 198, 255, .5);
    }

    .tipo-sin-asignar {
        color: #fbbf24;

        font-size: 12px;
    }

    .approval-badge {
        display: inline-flex;
        align-items: center;

        gap: 6px;

        padding: 6px 9px;

        border-radius: 30px;

        font-size: 11px;
        font-weight: 700;
    }

    .approval-badge.pending {
        border:
            1px solid
            rgba(251, 191, 36, .15);

        background:
            rgba(251, 191, 36, .06);

        color: #fbbf24;
    }

    .approval-badge.approved {
        border:
            1px solid
            rgba(52, 211, 153, .15);

        background:
            rgba(52, 211, 153, .06);

        color: #34d399;
    }

    .approval-badge.rejected {
        border:
            1px solid
            rgba(248, 113, 113, .15);

        background:
            rgba(248, 113, 113, .06);

        color: #f87171;
    }

    .approval-badge svg {
        width: 12px;
        height: 12px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 900px) {
        .editar-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .tipo-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .editar-operario-page {
            padding: 20px;
        }

        .form-input {
            font-size: 16px;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-cancelar,
        .btn-guardar {
            width: 100%;

            box-sizing: border-box;
        }
    }
</style>


<div class="editar-operario-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="editar-header">

        <div>

            <div class="breadcrumb">

                Gestión del equipo

                <span class="breadcrumb-blue">
                    /
                </span>

                <span class="breadcrumb-blue">
                    Operarios
                </span>

                <span>
                    / Editar
                </span>

            </div>


            <h1 class="editar-title">
                Editar operario
            </h1>


            <p class="editar-subtitle">

                Actualiza la información de
                {{ $operario->name }}

            </p>

        </div>


        <a
            href="{{ route('operarios.show', $operario) }}"
            class="btn-regresar"
        >

            <i data-lucide="arrow-left"></i>

            Regresar

        </a>

    </div>


    {{-- =========================================================
         ERRORES
    ========================================================== --}}

    @if($errors->any())

        <div class="alert-error">

            <div class="alert-error-title">
                No se pudo actualizar el operario
            </div>

            <ul>

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         ÉXITO
    ========================================================== --}}

    @if(session('success'))

        <div class="alert-success">

            {{ session('success') }}

        </div>

    @endif


    {{-- =========================================================
         CONTENIDO
    ========================================================== --}}

    <div class="editar-grid">

        {{-- =====================================================
             FORMULARIO
        ====================================================== --}}

        <div class="panel">

            <div class="panel-header">

                <div class="operario-heading">

                    <div class="operario-avatar">

                        {{
                            mb_strtoupper(
                                mb_substr(
                                    $operario->name ?? 'O',
                                    0,
                                    1
                                )
                            )
                        }}

                    </div>


                    <div>

                        <h2 class="operario-heading-title">
                            Datos del operario
                        </h2>

                        <p class="operario-heading-text">
                            Modifica los datos necesarios
                        </p>

                    </div>

                </div>

            </div>


            <form
                action="{{ route('operarios.update', $operario) }}"
                method="POST"
                id="editarOperarioForm"
            >

                @csrf
                @method('PUT')


                <div class="panel-body">


                    {{-- =================================================
                         NOMBRE
                    ================================================== --}}

                    <div class="form-group">

                        <label
                            for="name"
                            class="form-label"
                        >
                            Nombre completo
                        </label>


                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $operario->name) }}"
                            required
                            autocomplete="name"
                            class="form-input"
                        >


                        @error('name')

                            <p class="field-error">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- =================================================
                         CORREO
                    ================================================== --}}

                    <div class="form-group">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Correo electrónico
                        </label>


                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $operario->email) }}"
                            required
                            autocomplete="email"
                            class="form-input"
                        >


                        @error('email')

                            <p class="field-error">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- =================================================
                         TIPO DE OPERARIO
                    ================================================== --}}

                    <div class="form-group">

                        <div class="section-header">

                            <div class="section-icon">

                                <i data-lucide="workflow"></i>

                            </div>


                            <div>

                                <h3 class="section-title">

                                    Tipo de operario

                                    <span class="required">*</span>

                                </h3>


                                <p class="section-description">

                                    Selecciona la forma en que este
                                    operario trabajará con los clientes.

                                </p>

                            </div>

                        </div>


                        <div class="tipo-grid">

                            @forelse($tiposOperario as $tipo)

                                @php
                                    $nombreTipo =
                                        mb_strtolower(
                                            $tipo->nombre
                                        );

                                    $esProspeccion =
                                        str_contains(
                                            $nombreTipo,
                                            'prospec'
                                        );

                                    $iconoTipo =
                                        $esProspeccion
                                            ? 'search'
                                            : 'user-round-check';
                                @endphp


                                <label class="tipo-option">

                                    <input
                                        type="radio"
                                        name="id_tipo_operario"
                                        value="{{ $tipo->id_tipo_operario }}"
                                        required
                                        {{
                                            (string) old(
                                                'id_tipo_operario',
                                                $operario->id_tipo_operario
                                            )
                                            ===
                                            (string) $tipo->id_tipo_operario
                                                ? 'checked'
                                                : ''
                                        }}
                                    >


                                    <div class="tipo-card">

                                        <div class="tipo-card-icon">

                                            <i
                                                data-lucide="{{ $iconoTipo }}"
                                            ></i>

                                        </div>


                                        <div class="tipo-card-content">

                                            <p class="tipo-card-name">

                                                {{ $tipo->nombre }}

                                            </p>


                                            <p class="tipo-card-description">

                                                {{
                                                    $tipo->descripcion
                                                    ??
                                                    'Tipo de operación asignado al usuario.'
                                                }}

                                            </p>

                                        </div>


                                        <div class="tipo-card-check">

                                            <i data-lucide="check"></i>

                                        </div>

                                    </div>

                                </label>


                            @empty

                                <div
                                    style="
                                        grid-column:1 / -1;
                                        padding:13px 15px;
                                        border:1px solid rgba(248,113,113,.16);
                                        border-radius:10px;
                                        background:rgba(248,113,113,.05);
                                        color:#fca5a5;
                                        font-size:12px;
                                    "
                                >

                                    No existen tipos de operario activos.

                                </div>

                            @endforelse

                        </div>


                        @error('id_tipo_operario')

                            <p class="field-error">
                                {{ $message }}
                            </p>

                        @enderror


                        <div class="tipo-info">

                            <i data-lucide="info"></i>

                            <span>

                                El tipo seleccionado define cómo
                                trabajará el operario con los clientes.

                                Los operarios de prospección podrán
                                trabajar con búsqueda de prospectos,
                                mientras que los de asignación
                                trabajarán con clientes enviados
                                por el administrador.

                            </span>

                        </div>


                        @if(
                            (int) auth()->user()->id_rol === 2 &&
                            $operario->estado_aprobacion === 'aprobado'
                        )

                            <div class="reapproval-warning">

                                <i data-lucide="triangle-alert"></i>

                                <span>

                                    Si cambias el tipo de este
                                    operario, su cuenta quedará
                                    nuevamente pendiente hasta que
                                    el Super Administrador autorice
                                    el cambio.

                                </span>

                            </div>

                        @endif

                    </div>


                    {{-- =================================================
                         EMPRESA
                    ================================================== --}}

                    <div class="form-group">

                        <label class="form-label">
                            Empresa
                        </label>


                        <div class="form-static">

                            {{
                                $operario->empresa->nombre
                                ??
                                $operario->empresa->nombre_empresa
                                ??
                                $operario->empresa->razon_social
                                ??
                                'Sin empresa asignada'
                            }}

                        </div>


                        <p class="field-helper">

                            La empresa del operario no puede
                            modificarse desde esta pantalla.

                        </p>

                    </div>


                    {{-- =================================================
                         CONTRASEÑA
                    ================================================== --}}

                    <div class="password-section">

                        <div class="password-header">

                            <h3 class="password-title">
                                Cambiar contraseña
                            </h3>


                            <p class="password-description">

                                Si no deseas modificarla,
                                deja los campos de esta sección vacíos.

                            </p>

                        </div>


                        {{-- CONTRASEÑA ACTUAL --}}

                        <div class="form-group">

                            <label
                                for="current_password"
                                class="form-label"
                            >
                                Contraseña actual
                            </label>


                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                autocomplete="current-password"
                                class="form-input"
                            >


                            @error('current_password')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- NUEVA CONTRASEÑA --}}

                        <div class="form-group">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Nueva contraseña
                            </label>


                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                class="form-input"
                            >


                            <div
                                id="password-rules"
                                class="password-rules"
                            >

                                <p class="password-rules-title">
                                    La contraseña debe contener:
                                </p>


                                <div
                                    class="password-rule"
                                    data-rule="length"
                                >

                                    <span class="rule-icon">○</span>

                                    Mínimo 8 caracteres

                                </div>


                                <div
                                    class="password-rule"
                                    data-rule="uppercase"
                                >

                                    <span class="rule-icon">○</span>

                                    Al menos una letra mayúscula

                                </div>


                                <div
                                    class="password-rule"
                                    data-rule="lowercase"
                                >

                                    <span class="rule-icon">○</span>

                                    Al menos una letra minúscula

                                </div>


                                <div
                                    class="password-rule"
                                    data-rule="number"
                                >

                                    <span class="rule-icon">○</span>

                                    Al menos un número

                                </div>


                                <div
                                    class="password-rule"
                                    data-rule="special"
                                >

                                    <span class="rule-icon">○</span>

                                    Al menos un carácter especial

                                </div>


                                <div
                                    class="password-rule"
                                    data-rule="spaces"
                                >

                                    <span class="rule-icon">○</span>

                                    No debe contener espacios

                                </div>

                            </div>


                            @error('password')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- CONFIRMAR --}}

                        <div>

                            <label
                                for="password_confirmation"
                                class="form-label"
                            >
                                Confirmar nueva contraseña
                            </label>


                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                class="form-input"
                            >


                            <p
                                id="password-match"
                                class="password-match"
                            ></p>

                        </div>

                    </div>


                    {{-- =================================================
                         BOTONES
                    ================================================== --}}

                    <div class="form-actions">

                        <a
                            href="{{ route('operarios.show', $operario) }}"
                            class="btn-cancelar"
                        >

                            <i data-lucide="x"></i>

                            Cancelar

                        </a>


                        <button
                            type="submit"
                            class="btn-guardar"
                        >

                            <i data-lucide="save"></i>

                            Guardar cambios

                        </button>

                    </div>

                </div>

            </form>

        </div>


        {{-- =====================================================
             PANEL LATERAL
        ====================================================== --}}

        <div class="panel">

            <div class="panel-header">

                <p class="summary-eyebrow">
                    Resumen
                </p>

                <h3 class="summary-title">
                    Información actual
                </h3>

            </div>


            <div class="panel-body">

                {{-- NOMBRE --}}

                <div class="summary-item">

                    <p class="summary-label">
                        Operario
                    </p>

                    <p class="summary-value">
                        {{ $operario->name }}
                    </p>

                </div>


                {{-- CORREO --}}

                <div class="summary-item">

                    <p class="summary-label">
                        Correo
                    </p>

                    <p class="summary-value secondary">
                        {{ $operario->email }}
                    </p>

                </div>


                {{-- EMPRESA --}}

                <div class="summary-item">

                    <p class="summary-label">
                        Empresa
                    </p>

                    <p class="summary-value">

                        {{
                            $operario->empresa->nombre
                            ??
                            $operario->empresa->nombre_empresa
                            ??
                            $operario->empresa->razon_social
                            ??
                            'Sin empresa asignada'
                        }}

                    </p>

                </div>


                {{-- TIPO --}}

                <div class="summary-item">

                    <p class="summary-label">
                        Tipo de operario
                    </p>


                    @if($operario->tipoOperario)

                        <span class="tipo-badge">

                            <span class="tipo-badge-dot"></span>

                            {{ $operario->tipoOperario->nombre }}

                        </span>

                    @else

                        <span class="tipo-sin-asignar">
                            Sin tipo asignado
                        </span>

                    @endif

                </div>


                {{-- APROBACIÓN --}}

                <div class="summary-item">

                    <p class="summary-label">
                        Aprobación
                    </p>


                    @if(
                        $operario->estado_aprobacion
                        === 'pendiente'
                    )

                        <span class="approval-badge pending">

                            <i data-lucide="clock-3"></i>

                            Pendiente

                        </span>


                    @elseif(
                        $operario->estado_aprobacion
                        === 'rechazado'
                    )

                        <span class="approval-badge rejected">

                            <i data-lucide="circle-x"></i>

                            Rechazado

                        </span>


                    @else

                        <span class="approval-badge approved">

                            <i data-lucide="circle-check"></i>

                            Aprobado

                        </span>

                    @endif

                </div>


                {{-- ROL --}}

                <div class="summary-item">

                    <p class="summary-label">
                        Rol
                    </p>

                    <p class="summary-value blue">
                        Operario
                    </p>

                </div>


                {{-- ID --}}

                <div class="summary-item">

                    <p class="summary-label">
                        Identificador
                    </p>

                    <p class="summary-value">
                        #{{ $operario->id }}
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | LUCIDE
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

        const formulario =
            document.getElementById(
                'editarOperarioForm'
            );

        const password =
            document.getElementById(
                'password'
            );

        const passwordConfirmation =
            document.getElementById(
                'password_confirmation'
            );

        const passwordMatch =
            document.getElementById(
                'password-match'
            );

        const currentPassword =
            document.getElementById(
                'current_password'
            );


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR REGLAS
        |--------------------------------------------------------------------------
        */

        function actualizarRegla(
            rule,
            cumple
        ) {
            const elemento =
                document.querySelector(
                    `.password-rule[data-rule="${rule}"]`
                );

            if (!elemento) {
                return;
            }

            const icono =
                elemento.querySelector(
                    '.rule-icon'
                );

            if (cumple) {

                elemento.style.color =
                    '#34d399';

                if (icono) {
                    icono.textContent =
                        '✓';
                }

            } else {

                elemento.style.color =
                    '#64748b';

                if (icono) {
                    icono.textContent =
                        '○';
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR CONTRASEÑA
        |--------------------------------------------------------------------------
        */

        function validarPassword() {

            if (!password) {
                return true;
            }

            const valor =
                password.value;

            const reglas = {

                length:
                    valor.length >= 8,

                uppercase:
                    /[A-Z]/.test(valor),

                lowercase:
                    /[a-z]/.test(valor),

                number:
                    /[0-9]/.test(valor),

                special:
                    /[^A-Za-z0-9\s]/.test(valor),

                spaces:
                    !/\s/.test(valor),
            };


            actualizarRegla(
                'length',
                reglas.length
            );

            actualizarRegla(
                'uppercase',
                reglas.uppercase
            );

            actualizarRegla(
                'lowercase',
                reglas.lowercase
            );

            actualizarRegla(
                'number',
                reglas.number
            );

            actualizarRegla(
                'special',
                reglas.special
            );

            actualizarRegla(
                'spaces',
                reglas.spaces
            );


            const todasCumplen =
                reglas.length &&
                reglas.uppercase &&
                reglas.lowercase &&
                reglas.number &&
                reglas.special &&
                reglas.spaces;


            if (valor.length === 0) {

                password.style.borderColor =
                    '#334155';

            } else if (todasCumplen) {

                password.style.borderColor =
                    '#34d399';

            } else {

                password.style.borderColor =
                    '#fb7185';
            }


            validarCoincidencia();

            return todasCumplen;
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR CONFIRMACIÓN
        |--------------------------------------------------------------------------
        */

        function validarCoincidencia() {

            if (
                !password ||
                !passwordConfirmation ||
                !passwordMatch
            ) {
                return true;
            }


            const nueva =
                password.value;

            const confirmacion =
                passwordConfirmation.value;


            /*
            |--------------------------------------------------------------------------
            | No se está cambiando contraseña
            |--------------------------------------------------------------------------
            */

            if (
                nueva === '' &&
                confirmacion === ''
            ) {
                passwordConfirmation
                    .style
                    .borderColor =
                    '#334155';

                passwordMatch.style.display =
                    'none';

                return true;
            }


            if (confirmacion === '') {

                passwordConfirmation
                    .style
                    .borderColor =
                    '#334155';

                passwordMatch.style.display =
                    'none';

                return false;
            }


            if (
                nueva ===
                confirmacion
            ) {
                passwordConfirmation
                    .style
                    .borderColor =
                    '#34d399';

                passwordMatch.style.display =
                    'block';

                passwordMatch.style.color =
                    '#34d399';

                passwordMatch.textContent =
                    '✓ Las contraseñas coinciden.';

                return true;
            }


            passwordConfirmation
                .style
                .borderColor =
                '#fb7185';

            passwordMatch.style.display =
                'block';

            passwordMatch.style.color =
                '#fb7185';

            passwordMatch.textContent =
                'Las contraseñas no coinciden.';

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | EVENTOS
        |--------------------------------------------------------------------------
        */

        if (password) {

            password.addEventListener(
                'input',
                validarPassword
            );
        }


        if (passwordConfirmation) {

            passwordConfirmation
                .addEventListener(
                    'input',
                    validarCoincidencia
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN AL GUARDAR
        |--------------------------------------------------------------------------
        */

        if (formulario) {

            formulario.addEventListener(
                'submit',
                function (e) {

                    /*
                    |--------------------------------------------------------------------------
                    | TIPO DE OPERARIO
                    |--------------------------------------------------------------------------
                    */

                    const tipoSeleccionado =
                        document.querySelector(
                            'input[name="id_tipo_operario"]:checked'
                        );


                    if (!tipoSeleccionado) {

                        e.preventDefault();

                        alert(
                            'Selecciona el tipo de operario.'
                        );

                        const tipoGrid =
                            document.querySelector(
                                '.tipo-grid'
                            );

                        if (tipoGrid) {

                            tipoGrid.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center',
                            });
                        }

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CONTRASEÑA
                    |--------------------------------------------------------------------------
                    */

                    const nueva =
                        password
                            ? password.value.trim()
                            : '';

                    const actual =
                        currentPassword
                            ? currentPassword.value.trim()
                            : '';

                    const confirmacion =
                        passwordConfirmation
                            ? passwordConfirmation.value.trim()
                            : '';


                    /*
                    |--------------------------------------------------------------------------
                    | No quiere cambiar contraseña.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        nueva === '' &&
                        actual === '' &&
                        confirmacion === ''
                    ) {
                        return;
                    }


                    if (actual === '') {

                        e.preventDefault();

                        alert(
                            'Debes ingresar la contraseña actual para poder cambiarla.'
                        );

                        currentPassword.focus();

                        return;
                    }


                    if (nueva === '') {

                        e.preventDefault();

                        alert(
                            'Ingresa la nueva contraseña.'
                        );

                        password.focus();

                        return;
                    }


                    const passwordValida =
                        validarPassword();


                    if (!passwordValida) {

                        e.preventDefault();

                        alert(
                            'La nueva contraseña no cumple con todos los requisitos de seguridad.'
                        );

                        password.focus();

                        return;
                    }


                    if (!validarCoincidencia()) {

                        e.preventDefault();

                        alert(
                            'Las contraseñas nuevas no coinciden.'
                        );

                        passwordConfirmation.focus();

                        return;
                    }

                }
            );
        }

    }
);
</script>

@endsection