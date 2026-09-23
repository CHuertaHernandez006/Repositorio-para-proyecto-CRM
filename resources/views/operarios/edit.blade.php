@extends('layouts.app')

@section('header-title', 'Editar operario')

@section('content')

<div style="
    min-height:100%;
    padding:40px;
    color:white;
    background:#0b1424;
">

    {{-- ENCABEZADO --}}
    <div style="
        margin-bottom:30px;
        display:flex;
        justify-content:space-between;
        align-items:flex-end;
        gap:20px;
        flex-wrap:wrap;
    ">

        <div>

            <div style="
                margin-bottom:8px;
                color:#8190a7;
                font-size:12px;
                font-weight:600;
                text-transform:uppercase;
                letter-spacing:2px;
            ">
                Gestión del equipo
                <span style="color:#35c6ff;"> / </span>
                <span style="color:#35c6ff;">Operarios</span>
                <span style="color:#8190a7;"> / Editar</span>
            </div>

            <h1 style="
                margin:0;
                color:white;
                font-size:32px;
                font-weight:700;
                letter-spacing:-.5px;
            ">
                Editar operario
            </h1>

            <p style="
                margin:8px 0 0;
                color:#8190a7;
                font-size:14px;
            ">
                Actualiza la información de {{ $operario->name }}
            </p>

        </div>


        {{-- REGRESAR --}}
        <a
            href="{{ route('operarios.show', $operario) }}"
            style="
                display:inline-flex;
                align-items:center;
                gap:8px;
                padding:11px 16px;
                border:1px solid #334155;
                border-radius:12px;
                background:#111c30;
                color:#cbd5e1;
                text-decoration:none;
                font-size:14px;
                font-weight:600;
                transition:.2s;
            "
        >
            ← Regresar
        </a>

    </div>


    {{-- MENSAJES DE ERROR --}}
    @if ($errors->any())

        <div style="
            margin-bottom:20px;
            padding:16px 18px;
            border:1px solid rgba(244,63,94,.25);
            border-radius:14px;
            background:rgba(244,63,94,.08);
        ">

            <div style="
                margin-bottom:8px;
                color:#fb7185;
                font-size:14px;
                font-weight:700;
            ">
                No se pudo actualizar el operario
            </div>

            <ul style="
                margin:0;
                padding-left:20px;
                color:#cbd5e1;
                font-size:13px;
            ">

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- MENSAJE DE SESIÓN --}}
    @if(session('success'))

        <div style="
            margin-bottom:20px;
            padding:15px 18px;
            border:1px solid rgba(52,211,153,.2);
            border-radius:14px;
            background:rgba(52,211,153,.08);
            color:#34d399;
            font-size:14px;
            font-weight:600;
        ">
            {{ session('success') }}
        </div>

    @endif


    {{-- CONTENIDO --}}
    <div style="
        display:grid;
        grid-template-columns:minmax(0, 2fr) minmax(280px, 1fr);
        gap:20px;
        align-items:start;
    ">


        {{-- FORMULARIO --}}
        <div style="
            background:#111c30;
            border:1px solid rgba(53,198,255,.18);
            border-radius:16px;
            overflow:hidden;
        ">

            {{-- CABECERA --}}
            <div style="
                padding:25px;
                border-bottom:1px solid #27364b;
            ">

                <div style="
                    display:flex;
                    align-items:center;
                    gap:15px;
                ">

                    {{-- AVATAR --}}
                    <div style="
                        width:56px;
                        height:56px;
                        min-width:56px;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        border-radius:14px;
                        border:1px solid rgba(53,198,255,.3);
                        background:rgba(53,198,255,.1);
                    ">

                        <span style="
                            color:#35c6ff;
                            font-size:23px;
                            font-weight:700;
                        ">
                            {{ strtoupper(substr($operario->name, 0, 1)) }}
                        </span>

                    </div>


                    <div>

                        <h2 style="
                            margin:0;
                            color:white;
                            font-size:19px;
                            font-weight:700;
                        ">
                            Datos del operario
                        </h2>

                        <p style="
                            margin:5px 0 0;
                            color:#8190a7;
                            font-size:13px;
                        ">
                            Modifica los datos necesarios
                        </p>

                    </div>

                </div>

            </div>


            {{-- FORM --}}
            <form
                action="{{ route('operarios.update', $operario) }}"
                method="POST"
            >

                @csrf
                @method('PUT')


                <div style="
                    padding:25px;
                ">


                    {{-- NOMBRE --}}
                    <div style="
                        margin-bottom:22px;
                    ">

                        <label
                            for="name"
                            style="
                                display:block;
                                margin-bottom:8px;
                                color:#cbd5e1;
                                font-size:13px;
                                font-weight:600;
                            "
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
                            style="
                                width:100%;
                                box-sizing:border-box;
                                padding:13px 15px;
                                border:1px solid #334155;
                                border-radius:11px;
                                outline:none;
                                background:#0c1628;
                                color:white;
                                font-size:14px;
                            "
                        >

                        @error('name')

                            <p style="
                                margin:7px 0 0;
                                color:#fb7185;
                                font-size:12px;
                            ">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- CORREO --}}
                    <div style="
                        margin-bottom:22px;
                    ">

                        <label
                            for="email"
                            style="
                                display:block;
                                margin-bottom:8px;
                                color:#cbd5e1;
                                font-size:13px;
                                font-weight:600;
                            "
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
                            style="
                                width:100%;
                                box-sizing:border-box;
                                padding:13px 15px;
                                border:1px solid #334155;
                                border-radius:11px;
                                outline:none;
                                background:#0c1628;
                                color:white;
                                font-size:14px;
                            "
                        >

                        @error('email')

                            <p style="
                                margin:7px 0 0;
                                color:#fb7185;
                                font-size:12px;
                            ">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- EMPRESA --}}
                    <div style="
                        margin-bottom:22px;
                    ">

                        <label
                            for="empresa"
                            style="
                                display:block;
                                margin-bottom:8px;
                                color:#cbd5e1;
                                font-size:13px;
                                font-weight:600;
                            "
                        >
                            Empresa
                        </label>

                        <div style="
                            width:100%;
                            box-sizing:border-box;
                            padding:13px 15px;
                            border:1px solid #334155;
                            border-radius:11px;
                            background:#0c1628;
                            color:white;
                            font-size:14px;
                        ">
                            {{ $operario->empresa->nombre ?? 'Sin empresa asignada' }}
                        </div>

                        <p style="
                            margin:7px 0 0;
                            color:#64748b;
                            font-size:12px;
                        ">
                            La empresa del operario no puede modificarse desde esta pantalla.
                        </p>

                    </div>


                  {{-- CONTRASEÑA --}}
<div style="
    margin-bottom:22px;
    padding-top:3px;
">

    <div style="
        margin-bottom:15px;
        padding-bottom:12px;
        border-bottom:1px solid #27364b;
    ">

        <h3 style="
            margin:0;
            color:white;
            font-size:15px;
            font-weight:700;
        ">
            Cambiar contraseña
        </h3>

        <p style="
            margin:5px 0 0;
            color:#8190a7;
            font-size:12px;
        ">
            Confirma tu contraseña actual para establecer una nueva.
        </p>

    </div>


    {{-- CONTRASEÑA ACTUAL --}}
    <div style="
        margin-bottom:18px;
    ">

        <label
            for="current_password"
            style="
                display:block;
                margin-bottom:8px;
                color:#cbd5e1;
                font-size:13px;
                font-weight:600;
            "
        >
            Contraseña actual
        </label>

        <input
            id="current_password"
            name="current_password"
            type="password"
            autocomplete="current-password"
            style="
                width:100%;
                box-sizing:border-box;
                padding:13px 15px;
                border:1px solid #334155;
                border-radius:11px;
                outline:none;
                background:#0c1628;
                color:white;
                font-size:14px;
            "
        >

        @error('current_password')

            <p style="
                margin:7px 0 0;
                color:#fb7185;
                font-size:12px;
            ">
                {{ $message }}
            </p>

        @enderror

    </div>


    {{-- NUEVA CONTRASEÑA --}}
    <div style="
        margin-bottom:18px;
    ">

        <label
            for="password"
            style="
                display:block;
                margin-bottom:8px;
                color:#cbd5e1;
                font-size:13px;
                font-weight:600;
            "
        >
            Nueva contraseña
        </label>

        <input
            id="password"
            name="password"
            type="password"
            autocomplete="new-password"
            style="
                width:100%;
                box-sizing:border-box;
                padding:13px 15px;
                border:1px solid #334155;
                border-radius:11px;
                outline:none;
                background:#0c1628;
                color:white;
                font-size:14px;
            "
        >

        {{-- REGLAS DE CONTRASEÑA --}}
        <div
            id="password-rules"
            style="
                margin-top:10px;
                padding:12px 14px;
                border:1px solid #27364b;
                border-radius:10px;
                background:#0c1628;
            "
        >

            <p style="
                margin:0 0 8px;
                color:#94a3b8;
                font-size:12px;
                font-weight:600;
            ">
                La contraseña debe contener:
            </p>

            <div
                class="password-rule"
                data-rule="length"
                style="
                    margin-bottom:5px;
                    color:#64748b;
                    font-size:12px;
                "
            >
                <span class="rule-icon">○</span>
                Mínimo 8 caracteres
            </div>

            <div
                class="password-rule"
                data-rule="uppercase"
                style="
                    margin-bottom:5px;
                    color:#64748b;
                    font-size:12px;
                "
            >
                <span class="rule-icon">○</span>
                Al menos una letra mayúscula
            </div>

            <div
                class="password-rule"
                data-rule="lowercase"
                style="
                    margin-bottom:5px;
                    color:#64748b;
                    font-size:12px;
                "
            >
                <span class="rule-icon">○</span>
                Al menos una letra minúscula
            </div>

            <div
                class="password-rule"
                data-rule="number"
                style="
                    margin-bottom:5px;
                    color:#64748b;
                    font-size:12px;
                "
            >
                <span class="rule-icon">○</span>
                Al menos un número
            </div>

            <div
                class="password-rule"
                data-rule="special"
                style="
                    margin-bottom:5px;
                    color:#64748b;
                    font-size:12px;
                "
            >
                <span class="rule-icon">○</span>
                Al menos un carácter especial
            </div>

            <div
                class="password-rule"
                data-rule="spaces"
                style="
                    color:#64748b;
                    font-size:12px;
                "
            >
                <span class="rule-icon">○</span>
                No debe contener espacios
            </div>

        </div>

        @error('password')

            <p style="
                margin:7px 0 0;
                color:#fb7185;
                font-size:12px;
            ">
                {{ $message }}
            </p>

        @enderror

    </div>


    {{-- CONFIRMAR NUEVA CONTRASEÑA --}}
    <div>

        <label
            for="password_confirmation"
            style="
                display:block;
                margin-bottom:8px;
                color:#cbd5e1;
                font-size:13px;
                font-weight:600;
            "
        >
            Confirmar nueva contraseña
        </label>

        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            autocomplete="new-password"
            style="
                width:100%;
                box-sizing:border-box;
                padding:13px 15px;
                border:1px solid #334155;
                border-radius:11px;
                outline:none;
                background:#0c1628;
                color:white;
                font-size:14px;
            "
        >

        <p
            id="password-match"
            style="
                display:none;
                margin:7px 0 0;
                font-size:12px;
            "
        ></p>

    </div>

</div>
                    {{-- BOTONES --}}
                    <div style="
                        padding-top:5px;
                        display:flex;
                        justify-content:flex-end;
                        gap:10px;
                        flex-wrap:wrap;
                        border-top:1px solid #27364b;
                    ">

                        <a
                            href="{{ route('operarios.show', $operario) }}"
                            style="
                                margin-top:20px;
                                display:inline-flex;
                                align-items:center;
                                justify-content:center;
                                padding:12px 18px;
                                border:1px solid #334155;
                                border-radius:11px;
                                background:#0c1628;
                                color:#cbd5e1;
                                text-decoration:none;
                                font-size:14px;
                                font-weight:600;
                            "
                        >
                            Cancelar
                        </a>


                        <button
                            type="submit"
                            style="
                                margin-top:20px;
                                display:inline-flex;
                                align-items:center;
                                justify-content:center;
                                padding:12px 20px;
                                border:0;
                                border-radius:11px;
                                background:#35c6ff;
                                color:#07111f;
                                font-size:14px;
                                font-weight:700;
                                cursor:pointer;
                            "
                        >
                            Guardar cambios
                        </button>

                    </div>

                </div>

            </form>

        </div>


        {{-- PANEL LATERAL --}}
        <div style="
            background:#111c30;
            border:1px solid rgba(53,198,255,.18);
            border-radius:16px;
            overflow:hidden;
        ">

            {{-- CABECERA --}}
            <div style="
                padding:25px;
                border-bottom:1px solid #27364b;
            ">

                <p style="
                    margin:0;
                    color:#8190a7;
                    font-size:11px;
                    font-weight:600;
                    text-transform:uppercase;
                    letter-spacing:1.5px;
                ">
                    Resumen
                </p>

                <h3 style="
                    margin:6px 0 0;
                    color:white;
                    font-size:18px;
                    font-weight:700;
                ">
                    Información actual
                </h3>

            </div>


            <div style="
                padding:25px;
            ">

                {{-- NOMBRE --}}
                <div style="
                    margin-bottom:20px;
                ">

                    <p style="
                        margin:0 0 6px;
                        color:#64748b;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1px;
                    ">
                        Operario
                    </p>

                    <p style="
                        margin:0;
                        color:white;
                        font-size:15px;
                        font-weight:600;
                    ">
                        {{ $operario->name }}
                    </p>

                </div>


                {{-- CORREO --}}
                <div style="
                    margin-bottom:20px;
                ">

                    <p style="
                        margin:0 0 6px;
                        color:#64748b;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1px;
                    ">
                        Correo
                    </p>

                    <p style="
                        margin:0;
                        color:#cbd5e1;
                        font-size:13px;
                        word-break:break-word;
                    ">
                        {{ $operario->email }}
                    </p>

                </div>


                {{-- EMPRESA --}}
                <div style="
                    margin-bottom:20px;
                ">

                    <p style="
                        margin:0 0 6px;
                        color:#64748b;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1px;
                    ">
                        Empresa
                    </p>

                    <p style="
                        margin:0;
                        color:white;
                        font-size:14px;
                        font-weight:600;
                    ">
                        {{ $operario->empresa->nombre ?? 'Sin empresa asignada' }}
                    </p>

                </div>


                {{-- ROL --}}
                <div style="
                    margin-bottom:20px;
                ">

                    <p style="
                        margin:0 0 6px;
                        color:#64748b;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1px;
                    ">
                        Rol
                    </p>

                    <p style="
                        margin:0;
                        color:#35c6ff;
                        font-size:14px;
                        font-weight:600;
                    ">
                        Operario
                    </p>

                </div>


                {{-- ID --}}
                <div>

                    <p style="
                        margin:0 0 6px;
                        color:#64748b;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1px;
                    ">
                        Identificador
                    </p>

                    <p style="
                        margin:0;
                        color:white;
                        font-size:14px;
                        font-weight:600;
                    ">
                        #{{ $operario->id }}
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- RESPONSIVE --}}
<style>

@media (max-width: 900px) {

    div[style*="grid-template-columns:minmax(0, 2fr)"] {
        grid-template-columns:1fr !important;
    }

}

@media (max-width: 650px) {

    div[style*="min-height:100%"] {
        padding:20px !important;
    }

    input {
        font-size:16px !important;
    }

}

</style>
<script>
// ==========================================================
// VALIDACIÓN DE CONTRASEÑA SEGURA
// ==========================================================

const password = document.getElementById('password');
const passwordConfirmation = document.getElementById('password_confirmation');
const passwordMatch = document.getElementById('password-match');

if (password) {

    function actualizarRegla(rule, cumple) {

        const elemento = document.querySelector(
            `.password-rule[data-rule="${rule}"]`
        );

        if (!elemento) return;

        const icono = elemento.querySelector('.rule-icon');

        if (cumple) {

            elemento.style.color = '#34d399';
            icono.textContent = '✓';

        } else {

            elemento.style.color = '#64748b';
            icono.textContent = '○';

        }
    }


    function validarPassword() {

        const valor = password.value;

        const reglas = {
            length: valor.length >= 8,
            uppercase: /[A-ZÁÉÍÓÚÑ]/.test(valor),
            lowercase: /[a-záéíóúñ]/.test(valor),
            number: /[0-9]/.test(valor),
            special: /[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]/.test(valor),
            spaces: !/\s/.test(valor)
        };

        actualizarRegla('length', reglas.length);
        actualizarRegla('uppercase', reglas.uppercase);
        actualizarRegla('lowercase', reglas.lowercase);
        actualizarRegla('number', reglas.number);
        actualizarRegla('special', reglas.special);
        actualizarRegla('spaces', reglas.spaces);

        const todasCumplen =
            reglas.length &&
            reglas.uppercase &&
            reglas.lowercase &&
            reglas.number &&
            reglas.special &&
            reglas.spaces;

        if (valor.length === 0) {

            password.style.borderColor = '#334155';

        } else if (todasCumplen) {

            password.style.borderColor = '#34d399';

        } else {

            password.style.borderColor = '#fb7185';
        }

        validarCoincidencia();

        return todasCumplen;
    }


    function validarCoincidencia() {

        if (!passwordConfirmation || !passwordMatch) return true;

        const nueva = password.value;
        const confirmacion = passwordConfirmation.value;

        // Si todavía no ha escrito confirmación
        if (confirmacion === '') {

            passwordConfirmation.style.borderColor = '#334155';
            passwordMatch.style.display = 'none';

            return false;
        }

        if (nueva === confirmacion) {

            passwordConfirmation.style.borderColor = '#34d399';

            passwordMatch.style.display = 'block';
            passwordMatch.style.color = '#34d399';
            passwordMatch.textContent =
                '✓ Las contraseñas coinciden.';

            return true;

        } else {

            passwordConfirmation.style.borderColor = '#fb7185';

            passwordMatch.style.display = 'block';
            passwordMatch.style.color = '#fb7185';
            passwordMatch.textContent =
                'Las contraseñas no coinciden.';

            return false;
        }
    }


    password.addEventListener('input', validarPassword);

    if (passwordConfirmation) {
        passwordConfirmation.addEventListener(
            'input',
            validarCoincidencia
        );
    }


    // ======================================================
    // VALIDAR ANTES DE ENVIAR
    // ======================================================

    const formulario = password.closest('form');

    if (formulario) {

        formulario.addEventListener('submit', function (e) {

            const nueva = password.value.trim();
            const actual =
                document.getElementById('current_password')?.value.trim() || '';

            // Si no quiere cambiar contraseña,
            // no validamos esta sección.
            if (nueva === '' && actual === '') {
                return;
            }

            // Si quiere cambiarla debe poner contraseña actual
            if (actual === '') {

                e.preventDefault();

                alert(
                    'Debes ingresar tu contraseña actual para poder cambiarla.'
                );

                document.getElementById('current_password').focus();

                return;
            }

            // Validar contraseña nueva
            const passwordValida = validarPassword();

            if (!passwordValida) {

                e.preventDefault();

                alert(
                    'La nueva contraseña no cumple con todos los requisitos de seguridad.'
                );

                password.focus();

                return;
            }

            // Validar confirmación
            if (!validarCoincidencia()) {

                e.preventDefault();

                alert(
                    'Las contraseñas nuevas no coinciden.'
                );

                passwordConfirmation.focus();

                return;
            }

        });
    }
}
</script>
@endsection 