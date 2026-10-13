@extends('layouts.app')

@section('title', 'Configuración - CRM')

@section('header-title', 'Configuración')


@push('styles')

<style>

.config-page {
    max-width: 1180px;
    margin: 0 auto;
    color: #e8eef7;
}


/* ============================================================
   ENCABEZADO
============================================================ */

.config-header {
    margin-bottom: 26px;
}

.config-eyebrow {
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

.config-eyebrow-dot {
    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: #35c6ff;

    box-shadow:
        0 0 10px
        rgba(53, 198, 255, .65);
}

.config-title {
    margin: 0;

    color: #fff;

    font-size: 32px;
    line-height: 1.15;

    font-weight: 750;

    letter-spacing: -.03em;
}

.config-description {
    max-width: 720px;

    margin: 9px 0 0;

    color: #8190a7;

    font-size: 14px;
    line-height: 1.6;
}


/* ============================================================
   GRID
============================================================ */

.config-grid {
    display: grid;

    grid-template-columns:
        minmax(250px, .75fr)
        minmax(0, 1.5fr);

    gap: 20px;

    align-items: start;
}


/* ============================================================
   TARJETAS
============================================================ */

.config-card {
    border:
        1px solid
        rgba(148, 163, 184, .12);

    border-radius: 16px;

    background:
        rgba(18, 30, 49, .94);

    box-shadow:
        0 12px 28px
        rgba(0, 0, 0, .15);

    overflow: hidden;
}

.config-card-header {
    padding: 20px 22px;

    border-bottom:
        1px solid
        rgba(148, 163, 184, .10);
}

.config-card-title {
    display: flex;
    align-items: center;
    gap: 10px;

    margin: 0;

    color: #fff;

    font-size: 16px;
    font-weight: 750;
}

.config-card-description {
    margin: 7px 0 0;

    color: #718198;

    font-size: 12px;
    line-height: 1.5;
}

.config-card-body {
    padding: 22px;
}


/* ============================================================
   INFORMACIÓN DEL USUARIO
============================================================ */

.config-profile {
    display: flex;
    align-items: center;
    gap: 14px;

    padding-bottom: 20px;

    border-bottom:
        1px solid
        rgba(148, 163, 184, .10);
}

.config-avatar {
    display: grid;
    place-items: center;

    width: 52px;
    height: 52px;

    flex-shrink: 0;

    border:
        1px solid
        #31516a;

    border-radius: 14px;

    background: #17364a;

    color: #7dd3fc;
}

.config-avatar svg {
    width: 23px;
    height: 23px;
}

.config-profile-name {
    color: #f1f5f9;

    font-size: 15px;
    font-weight: 700;
}

.config-profile-role {
    margin-top: 4px;

    color: #7dd3fc;

    font-size: 11px;
}

.config-data {
    display: grid;
    gap: 18px;

    margin-top: 20px;
}

.config-data-item span {
    display: block;

    margin-bottom: 5px;

    color: #68788f;

    font-size: 10px;
    font-weight: 700;

    letter-spacing: .08em;
    text-transform: uppercase;
}

.config-data-item strong {
    display: block;

    color: #d8e1ec;

    font-size: 13px;
    font-weight: 600;

    overflow-wrap: anywhere;
}


/* ============================================================
   CAMPOS
============================================================ */

.config-field {
    margin-bottom: 19px;
}

.config-field label {
    display: block;

    margin-bottom: 7px;

    color: #cbd5e1;

    font-size: 12px;
    font-weight: 650;
}

.config-password-wrap {
    position: relative;
}

.config-input {
    width: 100%;

    min-height: 45px;

    padding:
        0
        48px
        0
        13px;

    border:
        1px solid
        #2c3d55;

    border-radius: 10px;

    outline: none;

    background:
        #0e1b2d;

    color:
        #e7edf6;

    font: inherit;
    font-size: 13px;

    transition: .2s ease;
}

.config-input:focus {
    border-color:
        rgba(53, 198, 255, .70);

    box-shadow:
        0 0 0 3px
        rgba(53, 198, 255, .08);
}


/* ============================================================
   BOTÓN MOSTRAR CONTRASEÑA
============================================================ */

.config-password-toggle {
    position: absolute;

    top: 50%;
    right: 8px;

    transform:
        translateY(-50%);

    display: grid;
    place-items: center;

    width: 34px;
    height: 34px;

    border: 0;
    border-radius: 8px;

    background: transparent;

    color: #718198;

    cursor: pointer;
}

.config-password-toggle:hover {
    background:
        rgba(148, 163, 184, .08);

    color: #cbd5e1;
}

.config-password-toggle svg {
    width: 17px;
    height: 17px;
}


/* ============================================================
   REQUISITOS DE CONTRASEÑA
============================================================ */

.password-requirements {
    margin-top: 14px;
    padding: 15px 16px;

    border:
        1px solid
        #2c3d55;

    border-radius: 12px;

    background:
        #0e1b2d;
}

.password-requirements-title {
    margin:
        0
        0
        12px;

    color:
        #b8c8dd;

    font-size: 12px;
    font-weight: 650;
}

.password-requirements-list {
    display: grid;
    gap: 9px;

    margin: 0;
    padding: 0;

    list-style: none;
}

.password-requirement {
    display: flex;
    align-items: center;
    gap: 8px;

    color: #718198;

    font-size: 12px;

    transition:
        color .18s ease;
}

.password-requirement-icon {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 17px;
    height: 17px;

    flex-shrink: 0;

    border-radius: 50%;

    color: #f87171;

    transition:
        color .18s ease,
        background-color .18s ease;
}

.password-requirement-icon svg {
    width: 15px;
    height: 15px;
}

.password-requirement.valid {
    color: #a7f3d0;
}

.password-requirement.valid
.password-requirement-icon {
    color: #34d399;
}


/* ============================================================
   CONFIRMACIÓN DE CONTRASEÑA
============================================================ */

.password-match {
    display: none;

    align-items: center;
    gap: 7px;

    margin-top: 8px;

    font-size: 11px;
    font-weight: 600;
}

.password-match.visible {
    display: flex;
}

.password-match.valid {
    color: #34d399;
}

.password-match.invalid {
    color: #f87171;
}

.password-match svg {
    width: 15px;
    height: 15px;
}


/* ============================================================
   ERRORES
============================================================ */

.config-error {
    margin-top: 7px;

    color: #fda4af;

    font-size: 11px;
}


/* ============================================================
   BOTÓN
============================================================ */

.config-actions {
    display: flex;

    justify-content: flex-end;

    padding-top: 5px;
}

.config-submit {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    min-height: 42px;

    padding: 0 17px;

    border:
        1px solid
        #0284c7;

    border-radius: 10px;

    background:
        #0284c7;

    color: #fff;

    font: inherit;

    font-size: 12px;
    font-weight: 700;

    cursor: pointer;

    transition: .18s ease;
}

.config-submit:hover:not(:disabled) {
    background: #0369a1;

    border-color: #0369a1;
}

.config-submit:disabled {
    opacity: .45;

    cursor: not-allowed;

    background: #334155;

    border-color: #475569;

    color: #94a3b8;
}


/* ============================================================
   MENSAJE DE ÉXITO
============================================================ */

.config-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;

    margin-bottom: 20px;

    padding: 14px 16px;

    border-radius: 11px;

    font-size: 12px;
    line-height: 1.5;
}

.config-alert.success {
    border:
        1px solid
        #236354;

    background:
        #12362e;

    color:
        #a7f3d0;
}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 800px) {

    .config-grid {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 520px) {

    .config-title {
        font-size: 27px;
    }

    .config-card-header,
    .config-card-body {
        padding: 18px;
    }

    .config-submit {
        width: 100%;
    }

}

</style>

@endpush



@section('content')

<div class="config-page">


    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}

    <header class="config-header">

        <div class="config-eyebrow">

            <span class="config-eyebrow-dot"></span>

            Mi cuenta

        </div>


        <h1 class="config-title">
            Configuración
        </h1>


        <p class="config-description">

            Consulta la información de tu cuenta
            y administra la seguridad de tu acceso.

        </p>

    </header>



    {{-- ============================================================
        ÉXITO
    ============================================================ --}}

    @if(session('exito'))

        <div
            class="config-alert success"
            role="status"
        >

            <i
                data-lucide="circle-check"
                aria-hidden="true"
            ></i>

            <div>
                {{ session('exito') }}
            </div>

        </div>

    @endif



    <div class="config-grid">


        {{-- ========================================================
            INFORMACIÓN DE CUENTA
        ======================================================== --}}

        <section class="config-card">

            <div class="config-card-header">

                <h2 class="config-card-title">

                    <i
                        data-lucide="user-round"
                        aria-hidden="true"
                    ></i>

                    Mi cuenta

                </h2>

            </div>


            <div class="config-card-body">

                <div class="config-profile">

                    <div class="config-avatar">

                        <i
                            data-lucide="user"
                            aria-hidden="true"
                        ></i>

                    </div>


                    <div>

                        <div class="config-profile-name">

                            {{
                                trim(
                                    ($usuario->nombre ?? '')
                                    . ' '
                                    . ($usuario->apellido_paterno ?? '')
                                    . ' '
                                    . ($usuario->apellido_materno ?? '')
                                )
                            }}

                        </div>


                        <div class="config-profile-role">
                            {{ $rolNombre }}
                        </div>

                    </div>

                </div>


                <div class="config-data">

                    <div class="config-data-item">

                        <span>
                            Correo electrónico
                        </span>

                        <strong>
                            {{ $usuario->correo }}
                        </strong>

                    </div>


                    <div class="config-data-item">

                        <span>
                            Rol
                        </span>

                        <strong>
                            {{ $rolNombre }}
                        </strong>

                    </div>


                    <div class="config-data-item">

                        <span>
                            Estado de la cuenta
                        </span>

                        <strong>

                            {{
                                $usuario->activo
                                    ? 'Activa'
                                    : 'Inactiva'
                            }}

                        </strong>

                    </div>

                </div>

            </div>

        </section>



        {{-- ========================================================
            SEGURIDAD
        ======================================================== --}}

        <section class="config-card">

            <div class="config-card-header">

                <h2 class="config-card-title">

                    <i
                        data-lucide="shield-check"
                        aria-hidden="true"
                    ></i>

                    Seguridad de la cuenta

                </h2>


                <p class="config-card-description">

                    Para cambiar tu contraseña
                    primero debes confirmar la actual.

                </p>

            </div>


            <div class="config-card-body">

                <form
                    id="password-form"
                    method="POST"
                    action="{{
                        route(
                            'configuracion.password.update'
                        )
                    }}"
                >

                    @csrf

                    @method('PUT')



                    {{-- ==================================================
                        CONTRASEÑA ACTUAL
                    =================================================== --}}

                    <div class="config-field">

                        <label for="password_actual">
                            Contraseña actual
                        </label>


                        <div class="config-password-wrap">

                            <input
                                id="password_actual"
                                class="config-input"
                                type="password"
                                name="password_actual"
                                autocomplete="current-password"
                                required
                            >


                            <button
                                type="button"
                                class="config-password-toggle"
                                data-password-toggle="password_actual"
                                aria-label="Mostrar contraseña actual"
                            >

                                <i
                                    data-lucide="eye"
                                    aria-hidden="true"
                                ></i>

                            </button>

                        </div>


                        @error('password_actual')

                            <div class="config-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>



                    {{-- ==================================================
                        NUEVA CONTRASEÑA
                    =================================================== --}}

                    <div class="config-field">

                        <label for="password">
                            Nueva contraseña
                        </label>


                        <div class="config-password-wrap">

                            <input
                                id="password"
                                class="config-input"
                                type="password"
                                name="password"
                                minlength="8"
                                maxlength="64"
                                autocomplete="new-password"
                                required
                            >


                            <button
                                type="button"
                                class="config-password-toggle"
                                data-password-toggle="password"
                                aria-label="Mostrar nueva contraseña"
                            >

                                <i
                                    data-lucide="eye"
                                    aria-hidden="true"
                                ></i>

                            </button>

                        </div>


                        {{-- ==============================================
                            CHECKLIST
                        =============================================== --}}

                        <div class="password-requirements">

                            <p class="password-requirements-title">
                                La contraseña debe contener:
                            </p>


                            <ul class="password-requirements-list">


                                <li
                                    class="password-requirement"
                                    data-requirement="length"
                                >

                                    <span class="password-requirement-icon">

                                        <i
                                            data-lucide="x"
                                            aria-hidden="true"
                                        ></i>

                                    </span>

                                    <span>
                                        Mínimo 8 caracteres
                                    </span>

                                </li>


                                <li
                                    class="password-requirement"
                                    data-requirement="uppercase"
                                >

                                    <span class="password-requirement-icon">

                                        <i
                                            data-lucide="x"
                                            aria-hidden="true"
                                        ></i>

                                    </span>

                                    <span>
                                        Al menos una letra mayúscula
                                    </span>

                                </li>


                                <li
                                    class="password-requirement"
                                    data-requirement="lowercase"
                                >

                                    <span class="password-requirement-icon">

                                        <i
                                            data-lucide="x"
                                            aria-hidden="true"
                                        ></i>

                                    </span>

                                    <span>
                                        Al menos una letra minúscula
                                    </span>

                                </li>


                                <li
                                    class="password-requirement"
                                    data-requirement="number"
                                >

                                    <span class="password-requirement-icon">

                                        <i
                                            data-lucide="x"
                                            aria-hidden="true"
                                        ></i>

                                    </span>

                                    <span>
                                        Al menos un número
                                    </span>

                                </li>


                                <li
                                    class="password-requirement"
                                    data-requirement="special"
                                >

                                    <span class="password-requirement-icon">

                                        <i
                                            data-lucide="x"
                                            aria-hidden="true"
                                        ></i>

                                    </span>

                                    <span>
                                        Al menos un carácter especial
                                    </span>

                                </li>


                                <li
                                    class="password-requirement"
                                    data-requirement="spaces"
                                >

                                    <span class="password-requirement-icon">

                                        <i
                                            data-lucide="x"
                                            aria-hidden="true"
                                        ></i>

                                    </span>

                                    <span>
                                        No debe contener espacios
                                    </span>

                                </li>

                            </ul>

                        </div>


                        @error('password')

                            <div class="config-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>



                    {{-- ==================================================
                        CONFIRMAR CONTRASEÑA
                    =================================================== --}}

                    <div class="config-field">

                        <label for="password_confirmation">
                            Confirmar nueva contraseña
                        </label>


                        <div class="config-password-wrap">

                            <input
                                id="password_confirmation"
                                class="config-input"
                                type="password"
                                name="password_confirmation"
                                minlength="8"
                                maxlength="64"
                                autocomplete="new-password"
                                required
                            >


                            <button
                                type="button"
                                class="config-password-toggle"
                                data-password-toggle="password_confirmation"
                                aria-label="Mostrar confirmación"
                            >

                                <i
                                    data-lucide="eye"
                                    aria-hidden="true"
                                ></i>

                            </button>

                        </div>


                        <div
                            id="password-match"
                            class="password-match"
                            aria-live="polite"
                        >

                            <i
                                data-lucide="x"
                                aria-hidden="true"
                            ></i>

                            <span>
                                Las contraseñas no coinciden
                            </span>

                        </div>

                    </div>



                    {{-- ==================================================
                        BOTÓN
                    =================================================== --}}

                    <div class="config-actions">

                        <button
                            id="password-submit"
                            type="submit"
                            class="config-submit"
                            disabled
                        >

                            <i
                                data-lucide="key-round"
                                aria-hidden="true"
                            ></i>

                            Actualizar contraseña

                        </button>

                    </div>

                </form>

            </div>

        </section>

    </div>

</div>

@endsection



@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    () => {


        /*
        |--------------------------------------------------------------------------
        | ELEMENTOS
        |--------------------------------------------------------------------------
        */

        const currentPassword =
            document.getElementById(
                'password_actual'
            );

        const password =
            document.getElementById(
                'password'
            );

        const confirmation =
            document.getElementById(
                'password_confirmation'
            );

        const submitButton =
            document.getElementById(
                'password-submit'
            );

        const matchMessage =
            document.getElementById(
                'password-match'
            );


        /*
        |--------------------------------------------------------------------------
        | REGLAS
        |--------------------------------------------------------------------------
        */

        const rules = {

            length:
                (value) =>
                    value.length >= 8,

            uppercase:
                (value) =>
                    /[A-Z]/.test(value),

            lowercase:
                (value) =>
                    /[a-z]/.test(value),

            number:
                (value) =>
                    /[0-9]/.test(value),

            special:
                (value) =>
                    /[^A-Za-z0-9\s]/.test(value),

            spaces:
                (value) =>
                    !/\s/.test(value),

        };


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR UNA REGLA
        |--------------------------------------------------------------------------
        */

        function setRequirementState(
            requirement,
            valid
        ) {

            const item =
                document.querySelector(
                    '[data-requirement="' +
                    requirement +
                    '"]'
                );

            if (!item) {
                return;
            }


            item.classList.toggle(
                'valid',
                valid
            );


            const icon =
                item.querySelector(
                    '[data-lucide]'
                );


            if (icon) {

                icon.setAttribute(
                    'data-lucide',
                    valid
                        ? 'check'
                        : 'x'
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR CONTRASEÑA
        |--------------------------------------------------------------------------
        */

        function validatePassword() {

            const value =
                password.value;


            let allValid =
                true;


            Object
                .entries(rules)
                .forEach(
                    ([name, validator]) => {

                        const valid =
                            validator(value);


                        setRequirementState(
                            name,
                            valid
                        );


                        if (!valid) {
                            allValid = false;
                        }

                    }
                );


            return allValid;
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR COINCIDENCIA
        |--------------------------------------------------------------------------
        */

        function validateConfirmation() {

            const hasConfirmation =
                confirmation.value.length > 0;


            const matches =
                hasConfirmation
                &&
                password.value ===
                confirmation.value;


            if (!hasConfirmation) {

                matchMessage.className =
                    'password-match';

                return false;
            }


            matchMessage.className =
                'password-match visible '
                +
                (
                    matches
                        ? 'valid'
                        : 'invalid'
                );


            const icon =
                matchMessage.querySelector(
                    '[data-lucide]'
                );


            const text =
                matchMessage.querySelector(
                    'span'
                );


            if (icon) {

                icon.setAttribute(
                    'data-lucide',
                    matches
                        ? 'check'
                        : 'x'
                );

            }


            if (text) {

                text.textContent =
                    matches
                        ? 'Las contraseñas coinciden'
                        : 'Las contraseñas no coinciden';

            }


            return matches;
        }


        /*
        |--------------------------------------------------------------------------
        | ESTADO DEL BOTÓN
        |--------------------------------------------------------------------------
        */

        function updateSubmitState() {

            const passwordValid =
                validatePassword();


            const confirmationValid =
                validateConfirmation();


            const currentValid =
                currentPassword
                .value
                .trim()
                .length > 0;


            submitButton.disabled =
                !(
                    passwordValid
                    &&
                    confirmationValid
                    &&
                    currentValid
                );


            /*
            |--------------------------------------------------------------------------
            | REDIBUJAR ICONOS LUCIDE
            |--------------------------------------------------------------------------
            */

            if (window.lucide) {

                window
                    .lucide
                    .createIcons();

            }

        }


        /*
        |--------------------------------------------------------------------------
        | EVENTOS DE VALIDACIÓN
        |--------------------------------------------------------------------------
        */

        password.addEventListener(
            'input',
            updateSubmitState
        );


        confirmation.addEventListener(
            'input',
            updateSubmitState
        );


        currentPassword.addEventListener(
            'input',
            updateSubmitState
        );


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR / OCULTAR CONTRASEÑAS
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-password-toggle]'
            )
            .forEach(
                (button) => {

                    button.addEventListener(
                        'click',
                        () => {

                            const input =
                                document.getElementById(
                                    button
                                        .dataset
                                        .passwordToggle
                                );


                            if (!input) {
                                return;
                            }


                            const mostrar =
                                input.type ===
                                'password';


                            input.type =
                                mostrar
                                    ? 'text'
                                    : 'password';


                            button.setAttribute(
                                'aria-label',
                                mostrar
                                    ? 'Ocultar contraseña'
                                    : 'Mostrar contraseña'
                            );


                            const icon =
                                button.querySelector(
                                    '[data-lucide]'
                                );


                            if (icon) {

                                icon.setAttribute(
                                    'data-lucide',
                                    mostrar
                                        ? 'eye-off'
                                        : 'eye'
                                );

                            }


                            if (window.lucide) {

                                window
                                    .lucide
                                    .createIcons();

                            }

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | ESTADO INICIAL
        |--------------------------------------------------------------------------
        */

        updateSubmitState();

    }
);

</script>

@endpush