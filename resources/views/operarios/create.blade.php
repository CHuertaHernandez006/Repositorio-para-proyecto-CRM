@extends('layouts.app')

@section('header-title', 'Nuevo operario')

@section('content')

<style>
    .crear-operario-page {
        max-width: 1050px;
        margin: 0 auto;
        color: #e8eef7;
    }

    /* =========================================================
       ENCABEZADO
    ========================================================= */

    .crear-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 25px;
        margin-bottom: 28px;
    }

    .crear-header-left {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .crear-icon-main {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border: 1px solid rgba(53, 198, 255, .13);
        border-radius: 13px;
        background: rgba(53, 198, 255, .07);
        color: #35c6ff;
    }

    .crear-icon-main svg {
        width: 20px;
        height: 20px;
    }

    .crear-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 7px;
        color: #35c6ff;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .crear-eyebrow-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 9px rgba(53, 198, 255, .6);
    }

    .crear-title {
        margin: 0;
        color: #ffffff;
        font-size: 30px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.025em;
    }

    .crear-description {
        margin: 7px 0 0;
        color: #718098;
        font-size: 13px;
        line-height: 1.6;
    }

    .volver-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 12px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 9px;
        background: rgba(255,255,255,.02);
        color: #718098;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        transition: .2s ease;
        white-space: nowrap;
    }

    .volver-link svg {
        width: 14px;
        height: 14px;
    }

    .volver-link:hover {
        border-color: rgba(53,198,255,.15);
        background: rgba(53,198,255,.04);
        color: #35c6ff;
    }


    /* =========================================================
       FORMULARIO PRINCIPAL
    ========================================================= */

    .crear-card {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 16px;
        background: #111c30;
        box-shadow: 0 20px 50px rgba(0,0,0,.10);
    }

    .crear-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 19px 22px;
        border-bottom: 1px solid rgba(255,255,255,.055);
    }

    .crear-card-title {
        margin: 0;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
    }

    .crear-card-subtitle {
        margin: 4px 0 0;
        color: #65748b;
        font-size: 11px;
    }

    .rol-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid rgba(53,198,255,.12);
        border-radius: 8px;
        background: rgba(53,198,255,.05);
        color: #54ccf9;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .rol-badge svg {
        width: 13px;
        height: 13px;
    }


    /* =========================================================
       FORM
    ========================================================= */

    .crear-form {
        padding: 24px 22px;
    }

    .form-section {
        margin-bottom: 27px;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .section-heading {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-bottom: 18px;
    }

    .section-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        border-radius: 9px;
        background: rgba(53,198,255,.07);
        color: #35c6ff;
    }

    .section-icon svg {
        width: 15px;
        height: 15px;
    }

    .section-title {
        margin: 0;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
    }

    .section-description {
        margin: 3px 0 0;
        color: #5f6e84;
        font-size: 10px;
        line-height: 1.5;
    }


    /* =========================================================
       GRID
    ========================================================= */

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .form-field.full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        margin-bottom: 7px;
        color: #a3afbf;
        font-size: 10px;
        font-weight: 700;
    }

    .required {
        color: #35c6ff;
    }

    .input-wrapper {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        width: 15px;
        height: 15px;
        transform: translateY(-50%);
        color: #536278;
        pointer-events: none;
    }

    .form-input {
        width: 100%;
        height: 42px;
        box-sizing: border-box;
        padding: 0 13px 0 37px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 10px;
        outline: none;
        background: #0c1628;
        color: #ffffff;
        font-family: inherit;
        font-size: 12px;
        transition: .2s ease;
    }

    .form-input::placeholder {
        color: #4f5d72;
    }

    .form-input:hover {
        border-color: rgba(255,255,255,.11);
    }

    .form-input:focus {
        border-color: rgba(53,198,255,.38);
        background: #0b1526;
        box-shadow: 0 0 0 3px rgba(53,198,255,.045);
    }

    .form-input[type="number"] {
        appearance: textfield;
    }

    .form-input[type="number"]::-webkit-inner-spin-button,
    .form-input[type="number"]::-webkit-outer-spin-button {
        opacity: .5;
    }


    /* =========================================================
       ERRORES
    ========================================================= */

    .field-error {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin-top: 7px;
        color: #f87171;
        font-size: 10px;
        line-height: 1.5;
    }

    .field-error svg {
        width: 13px;
        height: 13px;
        flex: 0 0 13px;
        margin-top: 1px;
    }


    /* =========================================================
       INFO PASSWORD
    ========================================================= */

    .password-info {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-top: 13px;
        padding: 10px 12px;
        border: 1px solid rgba(255,255,255,.045);
        border-radius: 9px;
        background: rgba(255,255,255,.018);
    }

    .password-info svg {
        width: 14px;
        height: 14px;
        flex: 0 0 14px;
        color: #65748a;
        margin-top: 1px;
    }

    .password-info span {
        color: #64738a;
        font-size: 10px;
        line-height: 1.55;
    }


    /* =========================================================
       EMPRESA AUTOMÁTICA
    ========================================================= */

    .empresa-auto {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 14px;
        border: 1px solid rgba(53,198,255,.10);
        border-radius: 11px;
        background: rgba(53,198,255,.035);
    }

    .empresa-auto-left {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .empresa-auto-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 9px;
        background: rgba(53,198,255,.08);
        color: #35c6ff;
    }

    .empresa-auto-icon svg {
        width: 17px;
        height: 17px;
    }

    .empresa-auto-title {
        margin: 0;
        color: #dce5f0;
        font-size: 11px;
        font-weight: 700;
    }

    .empresa-auto-text {
        margin: 3px 0 0;
        color: #5e6d83;
        font-size: 10px;
        line-height: 1.5;
    }

    .empresa-check {
        width: 17px;
        height: 17px;
        flex: 0 0 17px;
        color: #34d399;
    }


    /* =========================================================
       ROL
    ========================================================= */

    .rol-info {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 13px 14px;
        border: 1px solid rgba(255,255,255,.05);
        border-radius: 11px;
        background: #0c1628;
    }

    .rol-info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 9px;
        background: rgba(53,198,255,.08);
        color: #35c6ff;
    }

    .rol-info-icon svg {
        width: 15px;
        height: 15px;
    }

    .rol-info-title {
        margin: 0;
        color: #e2e9f2;
        font-size: 11px;
        font-weight: 700;
    }

    .rol-info-text {
        margin: 3px 0 0;
        color: #5f6e84;
        font-size: 10px;
        line-height: 1.6;
    }

    .rol-info-code {
        color: #49caff;
        font-weight: 700;
    }


    /* =========================================================
       SEPARADORES
    ========================================================= */

    .form-divider {
        height: 1px;
        margin: 27px 0;
        background: rgba(255,255,255,.045);
    }


    /* =========================================================
       BOTONES
    ========================================================= */

    .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
        padding-top: 21px;
        border-top: 1px solid rgba(255,255,255,.045);
    }

    .btn-cancelar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-width: 105px;
        height: 40px;
        padding: 0 15px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 9px;
        background: rgba(255,255,255,.02);
        color: #7b899d;
        font-family: inherit;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
        box-sizing: border-box;
    }

    .btn-cancelar svg {
        width: 14px;
        height: 14px;
    }

    .btn-cancelar:hover {
        border-color: rgba(255,255,255,.12);
        background: rgba(255,255,255,.04);
        color: #ffffff;
    }

    .btn-crear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 145px;
        height: 40px;
        padding: 0 18px;
        border: 0;
        border-radius: 9px;
        background: #35c6ff;
        color: #06111e;
        font-family: inherit;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 8px 22px rgba(53,198,255,.12);
        transition: .2s ease;
    }

    .btn-crear svg {
        width: 14px;
        height: 14px;
    }

    .btn-crear:hover {
        background: #65d4ff;
        transform: translateY(-1px);
        box-shadow: 0 10px 25px rgba(53,198,255,.18);
    }

    .btn-crear:active {
        transform: translateY(0);
    }


    /* =========================================================
       ALERTA GENERAL
    ========================================================= */

    .general-error {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 20px;
        padding: 12px 14px;
        border: 1px solid rgba(248,113,113,.14);
        border-radius: 10px;
        background: rgba(248,113,113,.055);
        color: #fca5a5;
        font-size: 11px;
        line-height: 1.5;
    }

    .general-error svg {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        color: #f87171;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 800px) {

        .crear-header {
            flex-direction: column;
        }

        .volver-link {
            align-self: flex-start;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-field.full {
            grid-column: auto;
        }

        .rol-badge {
            display: none;
        }
    }

    @media (max-width: 560px) {

        .crear-operario-page {
            padding: 0 2px;
        }

        .crear-header-left {
            gap: 11px;
        }

        .crear-icon-main {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
        }

        .crear-title {
            font-size: 25px;
        }

        .crear-form {
            padding: 20px 16px;
        }

        .crear-card-header {
            padding: 17px 16px;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-cancelar,
        .btn-crear {
            width: 100%;
        }

        .empresa-auto {
            align-items: flex-start;
        }
    }
</style>


<div class="crear-operario-page">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}

    <div class="crear-header">

        <div class="crear-header-left">

            <div class="crear-icon-main">
                <i data-lucide="user-plus"></i>
            </div>

            <div>

                <div class="crear-eyebrow">
                    <span class="crear-eyebrow-dot"></span>
                    Gestión del equipo
                </div>

                <h1 class="crear-title">
                    Nuevo operario
                </h1>

                <p class="crear-description">
                    Registra una nueva cuenta para un integrante de tu equipo.
                </p>

            </div>

        </div>


        <a
            href="{{ route('operarios.index') }}"
            class="volver-link"
        >
            <i data-lucide="arrow-left"></i>
            Volver a operarios
        </a>

    </div>


    {{-- =========================================================
         ERRORES GENERALES
    ========================================================== --}}

    @if($errors->any())

        <div class="general-error">

            <i data-lucide="circle-alert"></i>

            <div>
                Revisa los campos del formulario antes de continuar.
            </div>

        </div>

    @endif


    {{-- =========================================================
         CARD
    ========================================================== --}}

    <div class="crear-card">

        {{-- CABECERA --}}

        <div class="crear-card-header">

            <div>

                <h2 class="crear-card-title">
                    Información de acceso
                </h2>

                <p class="crear-card-subtitle">
                    Estos datos serán utilizados para ingresar al sistema.
                </p>

            </div>


            <div class="rol-badge">

                <i data-lucide="shield"></i>

                Rol: Operario

            </div>

        </div>


        {{-- FORMULARIO --}}

        <form
            action="{{ route('operarios.store') }}"
            method="POST"
            class="crear-form"
        >

            @csrf


            {{-- =================================================
                 DATOS PERSONALES
            ================================================== --}}

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i data-lucide="user"></i>
                    </div>

                    <div>

                        <h3 class="section-title">
                            Datos del operario
                        </h3>

                        <p class="section-description">
                            Información básica de la cuenta.
                        </p>

                    </div>

                </div>


                <div class="form-grid">

                    {{-- NOMBRE --}}

                    <div class="form-field full">

                        <label
                            for="name"
                            class="form-label"
                        >
                            Nombre completo
                            <span class="required">*</span>
                        </label>

                        <div class="input-wrapper">

                            <i
                                data-lucide="user"
                                class="input-icon"
                            ></i>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autocomplete="name"
                                placeholder="Ej. Alan Ortega"
                                class="form-input"
                            >

                        </div>

                        @error('name')

                            <p class="field-error">

                                <i data-lucide="circle-alert"></i>

                                {{ $message }}

                            </p>

                        @enderror

                    </div>


                    {{-- CORREO --}}

                    <div class="form-field full">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Correo electrónico
                            <span class="required">*</span>
                        </label>

                        <div class="input-wrapper">

                            <i
                                data-lucide="mail"
                                class="input-icon"
                            ></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="operario@empresa.com"
                                class="form-input"
                            >

                        </div>

                        @error('email')

                            <p class="field-error">

                                <i data-lucide="circle-alert"></i>

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                </div>

            </section>


            <div class="form-divider"></div>


            {{-- =================================================
                 SEGURIDAD
            ================================================== --}}

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i data-lucide="lock-keyhole"></i>
                    </div>

                    <div>

                        <h3 class="section-title">
                            Seguridad
                        </h3>

                        <p class="section-description">
                            Define las credenciales de acceso del operario.
                        </p>

                    </div>

                </div>


                <div class="form-grid">

                    {{-- CONTRASEÑA --}}

                    <div class="form-field">

                        <label
                            for="password"
                            class="form-label"
                        >
                            Contraseña
                            <span class="required">*</span>
                        </label>

                        <div class="input-wrapper">

                            <i
                                data-lucide="key-round"
                                class="input-icon"
                            ></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                placeholder="Mínimo 8 caracteres"
                                class="form-input"
                            >

                        </div>

                        @error('password')

                            <p class="field-error">

                                <i data-lucide="circle-alert"></i>

                                {{ $message }}

                            </p>

                        @enderror

                    </div>


                    {{-- CONFIRMAR --}}

                    <div class="form-field">

                        <label
                            for="password_confirmation"
                            class="form-label"
                        >
                            Confirmar contraseña
                            <span class="required">*</span>
                        </label>

                        <div class="input-wrapper">

                            <i
                                data-lucide="key-round"
                                class="input-icon"
                            ></i>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                placeholder="Repite la contraseña"
                                class="form-input"
                            >

                        </div>

                    </div>

                </div>


                <div class="password-info">

                    <i data-lucide="info"></i>

                    <span>
                        La contraseña debe contener al menos 8 caracteres.
                    </span>

                </div>

            </section>


            <div class="form-divider"></div>


            {{-- =================================================
                 EMPRESA
            ================================================== --}}

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i data-lucide="building-2"></i>
                    </div>

                    <div>

                        <h3 class="section-title">
                            Empresa
                        </h3>

                        <p class="section-description">
                            Define la empresa a la que pertenecerá el operario.
                        </p>

                    </div>

                </div>


                @if((int) auth()->user()->id_rol === 1)

                    {{-- SUPER ADMIN --}}

                    <div class="form-field">

                        <label
                            for="id_empresa"
                            class="form-label"
                        >
                            ID de empresa
                            <span class="required">*</span>
                        </label>

                        <div class="input-wrapper">

                            <i
                                data-lucide="building-2"
                                class="input-icon"
                            ></i>

                            <input
                                type="number"
                                id="id_empresa"
                                name="id_empresa"
                                value="{{ old('id_empresa') }}"
                                required
                                min="1"
                                placeholder="Ej. 1"
                                class="form-input"
                            >

                        </div>

                        @error('id_empresa')

                            <p class="field-error">

                                <i data-lucide="circle-alert"></i>

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                @else

                    {{-- ADMINISTRADOR CLIENTE --}}

                    <div class="empresa-auto">

                        <div class="empresa-auto-left">

                            <div class="empresa-auto-icon">

                                <i data-lucide="building-2"></i>

                            </div>

                            <div>

                                <p class="empresa-auto-title">
                                    Empresa asignada automáticamente
                                </p>

                                <p class="empresa-auto-text">
                                    El operario quedará asociado a la empresa
                                    de tu cuenta.
                                </p>

                            </div>

                        </div>


                        <i
                            data-lucide="check-circle-2"
                            class="empresa-check"
                        ></i>

                    </div>

                @endif

            </section>


            {{-- =================================================
                 ROL
            ================================================== --}}

            <div class="rol-info">

                <div class="rol-info-icon">

                    <i data-lucide="shield-check"></i>

                </div>

                <div>

                    <p class="rol-info-title">
                        Cuenta de operario
                    </p>

                    <p class="rol-info-text">
                        La cuenta se creará automáticamente con
                        <span class="rol-info-code">id_rol = 3</span>.
                        El operario tendrá acceso únicamente a las funciones
                        correspondientes a su rol.
                    </p>

                </div>

            </div>


            {{-- =================================================
                 BOTONES
            ================================================== --}}

            <div class="form-actions">

                <a
                    href="{{ route('operarios.index') }}"
                    class="btn-cancelar"
                >

                    <i data-lucide="x"></i>

                    Cancelar

                </a>


                <button
                    type="submit"
                    class="btn-crear"
                >

                    <i data-lucide="user-plus"></i>

                    Crear operario

                </button>

            </div>

        </form>

    </div>

</div>

@endsection