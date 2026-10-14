<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Recuperar contraseña - COMICenter
    </title>


    <script
        src="https://cdn.tailwindcss.com"
    ></script>

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
        max-w-md
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
                mb-1
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
            Recuperar contraseña
        </h1>


        <p
            class="
                text-sm
                text-[#94A3B8]
                mt-2
                leading-relaxed
            "
        >

            Ingresa el correo asociado
            a tu cuenta y te enviaremos
            un enlace para restablecerla.

        </p>

    </div>


    @if(session('status'))

        <div
            class="
                mb-5
                p-4
                rounded-lg
                border
                border-emerald-700
                bg-emerald-950/40
                text-emerald-300
                text-sm
            "
        >

            {{ session('status') }}

        </div>

    @endif


    @error('correo')

        <div
            class="
                mb-5
                p-4
                rounded-lg
                border
                border-red-800
                bg-red-950/30
                text-red-300
                text-sm
            "
        >

            {{ $message }}

        </div>

    @enderror


    <form
        method="POST"
        action="{{ route('password.email') }}"
    >

        @csrf


        <label
            for="correo"
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
            Correo electrónico
        </label>


        <input
            id="correo"
            type="email"
            name="correo"
            value="{{ old('correo') }}"
            required
            autofocus

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

            placeholder="usuario@correo.com"
        >


        <button
            type="submit"
            class="
                w-full
                mt-5
                bg-[#0284C7]
                hover:bg-[#0369A1]
                text-white
                font-semibold
                text-sm
                py-3
                rounded-lg
                transition
            "
        >
            Enviar enlace de recuperación
        </button>

    </form>


    <div
        class="
            mt-6
            pt-5
            border-t
            border-[#334155]
            text-center
        "
    >

        <a
            href="{{ route('login') }}"
            class="
                text-sm
                text-[#38BDF8]
                hover:text-[#7DD3FC]
            "
        >
            Volver al inicio de sesión
        </a>

    </div>

</div>

</body>

</html>