@extends('layouts.app')

@section('title', 'Nueva campaña - CRM')
@section('header-title', 'Nueva Campaña')

@section('content')

<style>
    .campaign-form-page {
        max-width: 1000px;
        margin: 0 auto;
        color: #e8eef7;
    }

    .form-page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 25px;
    }

    .page-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: #35c6ff;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 9px rgba(53,198,255,.6);
    }

    .page-title {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .page-description {
        max-width: 650px;
        margin: 8px 0 0;
        color: #7e8da3;
        font-size: 13px;
        line-height: 1.6;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        height: 40px;
        padding: 0 14px;
        border: 1px solid rgba(148,163,184,.15);
        border-radius: 9px;
        background: rgba(148,163,184,.05);
        color: #9aa8ba;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        transition: .2s ease;
    }

    .btn-back:hover {
        background: rgba(148,163,184,.1);
        color: #e2e8f0;
    }

    .btn-back svg {
        width: 14px;
        height: 14px;
    }

    .form-card {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 16px;
        background: #111c30;
        box-shadow: 0 18px 40px rgba(0,0,0,.08);
    }

    .form-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 19px 21px;
        border-bottom: 1px solid rgba(255,255,255,.05);
    }

    .form-card-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 39px;
        height: 39px;
        border: 1px solid rgba(53,198,255,.18);
        border-radius: 10px;
        background: rgba(53,198,255,.08);
        color: #35c6ff;
    }

    .form-card-icon svg {
        width: 18px;
        height: 18px;
    }

    .form-card-title {
        color: #fff;
        font-size: 14px;
        font-weight: 750;
    }

    .form-card-description {
        margin-top: 3px;
        color: #617087;
        font-size: 10px;
    }

    .campaign-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        padding: 22px;
    }

    .form-group {
        min-width: 0;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        margin-bottom: 7px;
        color: #8090a7;
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .required {
        color: #35c6ff;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 9px;
        outline: none;
        background: #091426;
        color: #edf4fc;
        font-size: 12px;
        box-sizing: border-box;
        transition: .2s ease;
        color-scheme: dark;
    }

    textarea.form-control {
        min-height: 110px;
        padding: 12px;
        resize: vertical;
        line-height: 1.55;
    }

    .form-control:focus {
        border-color: rgba(53,198,255,.38);
        box-shadow: 0 0 0 3px rgba(53,198,255,.045);
    }

    .form-control option {
        background: #101a2c;
        color: #e2e8f0;
    }

    .form-hint {
        margin-top: 5px;
        color: #536278;
        font-size: 9px;
        line-height: 1.45;
    }

    .input-error {
        margin-top: 5px;
        color: #fca5a5;
        font-size: 9px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        grid-column: 1 / -1;
        margin-top: 4px;
        padding-top: 18px;
        border-top: 1px solid rgba(255,255,255,.05);
    }

    .btn-cancel,
    .btn-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 16px;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn-cancel {
        border: 1px solid rgba(148,163,184,.14);
        background: rgba(148,163,184,.05);
        color: #94a3b8;
    }

    .btn-cancel:hover {
        background: rgba(148,163,184,.1);
        color: #d5dde7;
    }

    .btn-save {
        border: 1px solid rgba(53,198,255,.3);
        background: #35c6ff;
        color: #07111f;
    }

    .btn-save:hover {
        background: #64d4ff;
        transform: translateY(-1px);
        box-shadow: 0 0 17px rgba(53,198,255,.14);
    }

    .btn-save svg,
    .btn-cancel svg {
        width: 14px;
        height: 14px;
    }

    .error-alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border: 1px solid rgba(248,113,113,.16);
        border-radius: 11px;
        background: rgba(248,113,113,.06);
        color: #fca5a5;
        font-size: 11px;
        line-height: 1.6;
    }

    .error-alert ul {
        margin: 6px 0 0;
        padding-left: 18px;
    }

    @media (max-width: 700px) {
        .form-page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .btn-back {
            width: 100%;
        }

        .campaign-form {
            grid-template-columns: 1fr;
        }

        .form-group.full,
        .form-actions {
            grid-column: 1;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-cancel,
        .btn-save {
            width: 100%;
        }
    }
</style>


<div class="campaign-form-page">

    <div class="form-page-header">

        <div>

            <div class="page-eyebrow">
                <span class="eyebrow-dot"></span>
                Gestión de campañas
            </div>

            <h1 class="page-title">
                Nueva campaña
            </h1>

            <p class="page-description">
                Registra la información general de la campaña.
                Después podrás agregar los clientes que formarán
                parte de ella.
            </p>

        </div>


        <a
            href="{{ route('campanas.index') }}"
            class="btn-back"
        >
            <i data-lucide="arrow-left"></i>
            Volver
        </a>

    </div>


    @if($errors->any())

        <div class="error-alert">

            <strong>
                Revisa la información del formulario.
            </strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    <div class="form-card">

        <div class="form-card-header">

            <div class="form-card-icon">
                <i data-lucide="megaphone"></i>
            </div>

            <div>

                <div class="form-card-title">
                    Información de campaña
                </div>

                <div class="form-card-description">
                    Define el nombre, objetivo, estado y duración.
                </div>

            </div>

        </div>


        <form
            action="{{ route('campanas.store') }}"
            method="POST"
            class="campaign-form"
        >

            @csrf


            {{-- NOMBRE --}}

            <div class="form-group full">

                <label class="form-label">
                    Nombre de la campaña
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="nombre"
                    class="form-control"
                    maxlength="150"
                    value="{{ old('nombre') }}"
                    placeholder="Ej. Seguimiento de prospectos septiembre"
                    required
                >

                @error('nombre')
                    <div class="input-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- ESTADO --}}

            <div class="form-group">

                <label class="form-label">
                    Estado
                    <span class="required">*</span>
                </label>

                <select
                    name="id_estado_campana"
                    class="form-control"
                    required
                >

                    <option value="">
                        Selecciona un estado
                    </option>

                    @foreach($estados as $estado)

                        <option
                            value="{{ $estado->id_estado_campana }}"
                            {{ (string) old('id_estado_campana')
                                === (string) $estado->id_estado_campana
                                ? 'selected'
                                : ''
                            }}
                        >
                            {{ $estado->nombre }}
                        </option>

                    @endforeach

                </select>

                @error('id_estado_campana')
                    <div class="input-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- FECHA INICIO --}}

            <div class="form-group">

                <label class="form-label">
                    Fecha de inicio
                    <span class="required">*</span>
                </label>

                <input
                    type="date"
                    name="fecha_inicio"
                    id="fecha_inicio"
                    class="form-control"
                    value="{{ old('fecha_inicio') }}"
                    required
                >

                @error('fecha_inicio')
                    <div class="input-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- FECHA FIN --}}

            <div class="form-group">

                <label class="form-label">
                    Fecha de finalización
                    <span class="required">*</span>
                </label>

                <input
                    type="date"
                    name="fecha_fin"
                    id="fecha_fin"
                    class="form-control"
                    value="{{ old('fecha_fin') }}"
                    required
                >

                @error('fecha_fin')
                    <div class="input-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- DESCRIPCIÓN --}}

            <div class="form-group full">

                <label class="form-label">
                    Descripción
                </label>

                <textarea
                    name="descripcion"
                    class="form-control"
                    maxlength="2000"
                    placeholder="Describe brevemente en qué consiste la campaña..."
                >{{ old('descripcion') }}</textarea>

                <div class="form-hint">
                    Información general sobre la campaña.
                </div>

            </div>


            {{-- OBJETIVO --}}

            <div class="form-group full">

                <label class="form-label">
                    Objetivo
                </label>

                <textarea
                    name="objetivo"
                    class="form-control"
                    maxlength="2000"
                    placeholder="Ej. Contactar prospectos interesados para dar seguimiento..."
                >{{ old('objetivo') }}</textarea>

                <div class="form-hint">
                    Define qué se pretende lograr durante la campaña.
                </div>

            </div>


            {{-- ACCIONES --}}

            <div class="form-actions">

                <a
                    href="{{ route('campanas.index') }}"
                    class="btn-cancel"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn-save"
                >
                    <i data-lucide="save"></i>
                    Crear campaña
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const inicio =
        document.getElementById('fecha_inicio');

    const fin =
        document.getElementById('fecha_fin');

    if (inicio && fin) {

        inicio.addEventListener(
            'change',
            function () {

                fin.min = inicio.value;

                if (
                    fin.value &&
                    fin.value < inicio.value
                ) {
                    fin.value = '';
                }

            }
        );

        if (inicio.value) {
            fin.min = inicio.value;
        }
    }

});
</script>

@endsection