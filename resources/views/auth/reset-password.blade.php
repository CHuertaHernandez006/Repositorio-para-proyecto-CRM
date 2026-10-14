<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Nueva contraseña - COMICenter
    </title>


    <script
        src="https://cdn.tailwindcss.com"
    ></script>


    <style>

        .requirement {
            color: #94a3b8;
            transition: .18s ease;
        }

        .requirement.valid {
            color: #34d399;
        }

        .requirement-icon {
            display: inline-flex;

            width: 18px;
            justify-content: center;

            color: #f87171;

            font-weight: 700;
        }

        .requirement.valid
        .requirement-icon {
            color: #34d399;
        }

    </style>

</head>


<body
    class="
        bg-[#0F172A]
        text-[#F8FAFC]
        min-h-screen
        flex
        items-center
        justify-center
        p-4
    "
>


<div
    class="
        w-full
        max-w-lg
        bg-[#1E293B]
        border
        border-[#334155]
        rounded-xl
        shadow-xl
        p-7
    "
>


    <div class="mb-7">

        <div
            class="
                text-xl
                font-bold
            "
        >

            COMI<span class="text-[#38BDF8]">Center</span>

        </div>


        <h1
            class="
                text-2xl
                font-bold
                mt-6
            "
        >
            Crea una nueva contraseña
        </h1>


        <p
            class="
                mt-2
                text-sm
                text-[#94A3B8]
            "
        >

            Elige una contraseña segura
            para recuperar el acceso a tu cuenta.

        </p>

    </div>


    @if($errors->any())

        <div
            class="
                mb-5
                p-4
                border
                border-red-800
                bg-red-950/30
                rounded-lg
                text-red-300
                text-xs
            "
        >

            @foreach($errors->all() as $error)

                <div>
                    {{ $error }}
                </div>

            @endforeach

        </div>

    @endif


    <form
        id="reset-form"
        method="POST"
        action="{{ route('password.update') }}"
    >

        @csrf


        <input
            type="hidden"
            name="token"
            value="{{ $token }}"
        >


        <div class="mb-5">

            <label
                for="password"
                class="
                    block
                    text-xs
                    font-bold
                    text-[#94A3B8]
                    uppercase
                    tracking-wider
                    mb-2
                "
            >
                Nueva contraseña
            </label>


            <input
                id="password"
                type="password"
                name="password"
                minlength="8"
                maxlength="64"
                required
                autocomplete="new-password"

                class="
                    w-full
                    px-4
                    py-3
                    text-sm
                    bg-[#0F172A]
                    border
                    border-[#334155]
                    rounded-lg
                    focus:outline-none
                    focus:ring-2
                    focus:ring-[#38BDF8]
                    text-white
                "
            >

        </div>


        <div
            class="
                mb-6
                p-4
                border
                border-[#334155]
                rounded-lg
                bg-[#0F172A]
            "
        >

            <p
                class="
                    text-sm
                    font-semibold
                    text-[#CBD5E1]
                    mb-3
                "
            >
                La contraseña debe contener:
            </p>


            <div class="space-y-2 text-sm">

                <div
                    class="requirement"
                    data-rule="length"
                >
                    <span class="requirement-icon">✕</span>
                    Mínimo 8 caracteres
                </div>


                <div
                    class="requirement"
                    data-rule="uppercase"
                >
                    <span class="requirement-icon">✕</span>
                    Al menos una letra mayúscula
                </div>


                <div
                    class="requirement"
                    data-rule="lowercase"
                >
                    <span class="requirement-icon">✕</span>
                    Al menos una letra minúscula
                </div>


                <div
                    class="requirement"
                    data-rule="number"
                >
                    <span class="requirement-icon">✕</span>
                    Al menos un número
                </div>


                <div
                    class="requirement"
                    data-rule="special"
                >
                    <span class="requirement-icon">✕</span>
                    Al menos un carácter especial
                </div>


                <div
                    class="requirement"
                    data-rule="spaces"
                >
                    <span class="requirement-icon">✕</span>
                    No debe contener espacios
                </div>

            </div>

        </div>


        <div class="mb-4">

            <label
                for="password_confirmation"
                class="
                    block
                    text-xs
                    font-bold
                    text-[#94A3B8]
                    uppercase
                    tracking-wider
                    mb-2
                "
            >
                Confirmar contraseña
            </label>


            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                minlength="8"
                maxlength="64"
                required
                autocomplete="new-password"

                class="
                    w-full
                    px-4
                    py-3
                    text-sm
                    bg-[#0F172A]
                    border
                    border-[#334155]
                    rounded-lg
                    focus:outline-none
                    focus:ring-2
                    focus:ring-[#38BDF8]
                    text-white
                "
            >


            <div
                id="match-message"
                class="
                    hidden
                    mt-2
                    text-xs
                "
            ></div>

        </div>


        <button
            id="submit-button"
            type="submit"
            disabled

            class="
                w-full
                mt-3
                bg-[#0284C7]
                enabled:hover:bg-[#0369A1]
                disabled:bg-[#334155]
                disabled:text-[#64748B]
                disabled:cursor-not-allowed
                text-white
                font-semibold
                text-sm
                py-3
                rounded-lg
                transition
            "
        >
            Restablecer contraseña
        </button>

    </form>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    () => {

        const password =
            document.getElementById(
                'password'
            );

        const confirmation =
            document.getElementById(
                'password_confirmation'
            );

        const button =
            document.getElementById(
                'submit-button'
            );

        const matchMessage =
            document.getElementById(
                'match-message'
            );


        const rules = {

            length:
                value =>
                    value.length >= 8,

            uppercase:
                value =>
                    /[A-Z]/.test(value),

            lowercase:
                value =>
                    /[a-z]/.test(value),

            number:
                value =>
                    /[0-9]/.test(value),

            special:
                value =>
                    /[^A-Za-z0-9\s]/.test(value),

            spaces:
                value =>
                    value.length > 0
                    &&
                    !/\s/.test(value),

        };


        function validate()
        {
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


                        const item =
                            document.querySelector(
                                '[data-rule="' +
                                name +
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
                                '.requirement-icon'
                            );


                        icon.textContent =
                            valid
                                ? '✓'
                                : '✕';


                        if (!valid) {
                            allValid = false;
                        }

                    }
                );


            const hasConfirmation =
                confirmation.value.length > 0;


            const matches =
                hasConfirmation
                &&
                value ===
                confirmation.value;


            if (!hasConfirmation) {

                matchMessage.className =
                    'hidden mt-2 text-xs';

                matchMessage.textContent =
                    '';

            } else if (matches) {

                matchMessage.className =
                    'mt-2 text-xs text-emerald-400';

                matchMessage.textContent =
                    '✓ Las contraseñas coinciden';

            } else {

                matchMessage.className =
                    'mt-2 text-xs text-red-400';

                matchMessage.textContent =
                    '✕ Las contraseñas no coinciden';

            }


            button.disabled =
                !(
                    allValid
                    &&
                    matches
                );
        }


        password.addEventListener(
            'input',
            validate
        );


        confirmation.addEventListener(
            'input',
            validate
        );


        validate();

    }
);

</script>

</body>

</html>