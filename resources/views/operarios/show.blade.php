@extends('layouts.app')

@section('header-title', 'Operario')

@section('content')

<div style="
    padding:40px;
    color:white;
">

    {{-- ENCABEZADO --}}
    <div style="
        margin-bottom:30px;
        display:flex;
        justify-content:space-between;
        align-items:center;
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
            </div>

            <h1 style="
                margin:0;
                color:white;
                font-size:32px;
                font-weight:700;
            ">
                {{ $operario->name }}
            </h1>

            <p style="
                margin-top:8px;
                color:#8190a7;
                font-size:14px;
            ">
                Información del operario
            </p>

        </div>


        {{-- BOTONES --}}
        <div style="
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        ">

            <a
                href="{{ route('operarios.index') }}"
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
                "
            >
                ← Regresar
            </a>


            <a
                href="{{ route('operarios.edit', $operario) }}"
                style="
                    display:inline-flex;
                    align-items:center;
                    gap:8px;
                    padding:11px 16px;
                    border-radius:12px;
                    background:#35c6ff;
                    color:#07111f;
                    text-decoration:none;
                    font-size:14px;
                    font-weight:700;
                "
            >
                ✎ Editar operario
            </a>

        </div>

    </div>


    {{-- CONTENEDOR PRINCIPAL --}}
    <div style="
        display:grid;
        grid-template-columns:minmax(0, 2fr) minmax(280px, 1fr);
        gap:20px;
    ">


        {{-- INFORMACIÓN DEL OPERARIO --}}
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
                display:flex;
                align-items:center;
                gap:16px;
            ">

                {{-- AVATAR --}}
                <div style="
                    width:64px;
                    height:64px;
                    min-width:64px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border-radius:16px;
                    border:1px solid rgba(53,198,255,.3);
                    background:rgba(53,198,255,.1);
                ">

                    <span style="
                        color:#35c6ff;
                        font-size:26px;
                        font-weight:700;
                    ">
                        {{ strtoupper(substr($operario->name, 0, 1)) }}
                    </span>

                </div>


                <div style="
                    min-width:0;
                ">

                    <h2 style="
                        margin:0;
                        color:white;
                        font-size:21px;
                        font-weight:700;
                    ">
                        {{ $operario->name }}
                    </h2>

                    <p style="
                        margin:6px 0 0;
                        color:#8190a7;
                        font-size:14px;
                    ">
                        Operario del sistema
                    </p>

                </div>

            </div>


            {{-- DATOS --}}
            <div style="
                display:grid;
                grid-template-columns:repeat(2, minmax(0, 1fr));
            ">


                {{-- CORREO --}}
                <div style="
                    padding:24px;
                    border-right:1px solid #27364b;
                    border-bottom:1px solid #27364b;
                ">

                    <div style="
                        margin-bottom:9px;
                        color:#8190a7;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1.5px;
                    ">
                        Correo electrónico
                    </div>

                    <p style="
                        margin:0;
                        color:white;
                        font-size:14px;
                        font-weight:500;
                        word-break:break-word;
                    ">
                        {{ $operario->email }}
                    </p>

                </div>


                {{-- EMPRESA --}}
                <div style="
                    padding:24px;
                    border-bottom:1px solid #27364b;
                ">

                    <div style="
                        margin-bottom:9px;
                        color:#8190a7;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1.5px;
                    ">
                        Empresa
                    </div>

                    <p style="
                        margin:0;
                        color:white;
                        font-size:14px;
                        font-weight:500;
                    ">
                        {{ $operario->empresa->nombre ?? 'Sin empresa asignada' }}
                    </p>

                </div>


                {{-- ROL --}}
                <div style="
                    padding:24px;
                    border-right:1px solid #27364b;
                ">

                    <div style="
                        margin-bottom:9px;
                        color:#8190a7;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1.5px;
                    ">
                        Rol
                    </div>

                    <p style="
                        margin:0;
                        color:white;
                        font-size:14px;
                        font-weight:500;
                    ">
                        Operario
                    </p>

                </div>


                {{-- ID --}}
                <div style="
                    padding:24px;
                ">

                    <div style="
                        margin-bottom:9px;
                        color:#8190a7;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1.5px;
                    ">
                        Identificador
                    </div>

                    <p style="
                        margin:0;
                        color:white;
                        font-size:14px;
                        font-weight:500;
                    ">
                        #{{ $operario->id }}
                    </p>

                </div>

            </div>

        </div>


        {{-- ESTADO --}}
        <div style="
            background:#111c30;
            border:1px solid rgba(53,198,255,.18);
            border-radius:16px;
            overflow:hidden;
        ">

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
                    Estado de cuenta
                </p>

            </div>


            <div style="
                padding:25px;
            ">

                {{-- ESTADO ACTIVO --}}
                @if($operario->estado ?? true)

                    <div style="
                        padding:16px;
                        display:flex;
                        align-items:center;
                        gap:12px;
                        border:1px solid rgba(52,211,153,.2);
                        border-radius:12px;
                        background:rgba(52,211,153,.08);
                    ">

                        <div style="
                            width:40px;
                            height:40px;
                            min-width:40px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            border-radius:11px;
                            background:rgba(52,211,153,.1);
                            color:#34d399;
                            font-size:20px;
                        ">
                            ✓
                        </div>


                        <div>

                            <p style="
                                margin:0;
                                color:white;
                                font-size:14px;
                                font-weight:700;
                            ">
                                Activo
                            </p>

                            <p style="
                                margin:5px 0 0;
                                color:#8190a7;
                                font-size:12px;
                            ">
                                El operario puede acceder al sistema.
                            </p>

                        </div>

                    </div>

                @else

                    {{-- ESTADO INACTIVO --}}
                    <div style="
                        padding:16px;
                        display:flex;
                        align-items:center;
                        gap:12px;
                        border:1px solid rgba(244,63,94,.2);
                        border-radius:12px;
                        background:rgba(244,63,94,.08);
                    ">

                        <div style="
                            width:40px;
                            height:40px;
                            min-width:40px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            border-radius:11px;
                            background:rgba(244,63,94,.1);
                            color:#fb7185;
                            font-size:20px;
                        ">
                            ×
                        </div>


                        <div>

                            <p style="
                                margin:0;
                                color:white;
                                font-size:14px;
                                font-weight:700;
                            ">
                                Inactivo
                            </p>

                            <p style="
                                margin:5px 0 0;
                                color:#8190a7;
                                font-size:12px;
                            ">
                                El acceso del operario está deshabilitado.
                            </p>

                        </div>

                    </div>

                @endif


                {{-- BOTÓN ESTADO --}}
                <div style="
                    margin-top:20px;
                ">

                    <form
                        action="{{ route('operarios.toggleEstado', $operario) }}"
                        method="POST"
                    >

                        @csrf
                        @method('PATCH')


                        @if($operario->estado ?? true)

                            <button
                                type="submit"
                                style="
                                    width:100%;
                                    padding:13px 16px;
                                    border:1px solid rgba(244,63,94,.2);
                                    border-radius:12px;
                                    background:rgba(244,63,94,.08);
                                    color:#fb7185;
                                    font-size:14px;
                                    font-weight:600;
                                    cursor:pointer;
                                "
                            >
                                Desactivar operario
                            </button>

                        @else

                            <button
                                type="submit"
                                style="
                                    width:100%;
                                    padding:13px 16px;
                                    border:1px solid rgba(52,211,153,.2);
                                    border-radius:12px;
                                    background:rgba(52,211,153,.08);
                                    color:#34d399;
                                    font-size:14px;
                                    font-weight:600;
                                    cursor:pointer;
                                "
                            >
                                Activar operario
                            </button>

                        @endif

                    </form>

                </div>

            </div>

        </div>

    </div>


    {{-- INFORMACIÓN INFERIOR --}}
    <div style="
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:20px;
        margin-top:20px;
    ">


        {{-- ACTIVIDAD --}}
        <div style="
            background:#111c30;
            border:1px solid rgba(53,198,255,.18);
            border-radius:16px;
            overflow:hidden;
        ">

            <div style="
                padding:22px 25px;
                border-bottom:1px solid #27364b;
                display:flex;
                align-items:center;
                justify-content:space-between;
            ">

                <div>

                    <p style="
                        margin:0;
                        color:#8190a7;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1.5px;
                    ">
                        Actividad
                    </p>

                    <h3 style="
                        margin:6px 0 0;
                        color:white;
                        font-size:18px;
                        font-weight:700;
                    ">
                        Llamadas realizadas
                    </h3>

                </div>


                <div style="
                    width:44px;
                    height:44px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border-radius:12px;
                    border:1px solid rgba(53,198,255,.2);
                    background:rgba(53,198,255,.1);
                    color:#35c6ff;
                    font-size:20px;
                ">
                    ☎
                </div>

            </div>


            <div style="
                padding:30px 25px;
            ">

                <p style="
                    margin:0;
                    color:white;
                    font-size:38px;
                    font-weight:700;
                ">
                    0
                </p>

                <p style="
                    margin:8px 0 0;
                    color:#8190a7;
                    font-size:14px;
                ">
                    Llamadas registradas
                </p>

            </div>

        </div>


        {{-- EMPRESA --}}
        <div style="
            background:#111c30;
            border:1px solid rgba(53,198,255,.18);
            border-radius:16px;
            overflow:hidden;
        ">

            <div style="
                padding:22px 25px;
                border-bottom:1px solid #27364b;
                display:flex;
                align-items:center;
                justify-content:space-between;
            ">

                <div>

                    <p style="
                        margin:0;
                        color:#8190a7;
                        font-size:11px;
                        font-weight:600;
                        text-transform:uppercase;
                        letter-spacing:1.5px;
                    ">
                        Organización
                    </p>

                    <h3 style="
                        margin:6px 0 0;
                        color:white;
                        font-size:18px;
                        font-weight:700;
                    ">
                        Empresa asignada
                    </h3>

                </div>


                <div style="
                    width:44px;
                    height:44px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border-radius:12px;
                    border:1px solid rgba(53,198,255,.2);
                    background:rgba(53,198,255,.1);
                    color:#35c6ff;
                    font-size:20px;
                ">
                    🏢
                </div>

            </div>


            <div style="
                padding:30px 25px;
            ">

                <p style="
                    margin:0;
                    color:white;
                    font-size:18px;
                    font-weight:600;
                ">
                    {{ $operario->empresa->nombre ?? 'Sin empresa asignada' }}
                </p>

                <p style="
                    margin:8px 0 0;
                    color:#8190a7;
                    font-size:14px;
                ">
                    Empresa a la que pertenece este operario.
                </p>

            </div>

        </div>

    </div>

</div>


{{-- RESPONSIVE --}}
<style>

@media (max-width: 900px) {

    .min-h-full > div {
        grid-template-columns: 1fr !important;
    }

}

@media (max-width: 650px) {

    .min-h-full {
        padding: 20px !important;
    }

    .min-h-full > div:first-child {
        display:block !important;
    }

}

</style>

@endsection