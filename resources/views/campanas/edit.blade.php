@extends('layouts.app')

@section('title', 'Editar campaña - CRM')
@section('header-title', 'Editar Campaña')

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

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        grid-column: 1 / -1;
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
    }

    .btn-cancel {
        border: 1px solid rgba(148,163,184,.14);
        background: rgba(148,163,184,.05);
        color: #94a3b8;
    }

    .btn-save {
        border: 1px solid rgba(53,198,255,.3);
        background: #35c6ff;
        color: #07111f;
    }

    .btn-save:hover {
        background: #64d4ff;
    }

    .btn-save svg {
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
    }

    @media (max-width: 700px) {
        .campaign-form {
            grid-template-columns: 1fr;
        }

        .form-group.full,
        .form-actions {
            grid-column: 1;
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
                Editar campaña
            </h1>

            <p class="page-description">
                Modifica la información registrada de
                {{ $campana->nombre }}.
            </p>

        </div>

        <a
            href="{{ route(
                'campanas.show',
                $campana->id_campana
            ) }}"
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
        </div>

    @endif


    <div class="form-card">

        <div class="form-card-header">

            <div class="form-card-icon">
                <i data-lucide="pencil"></i>
            </div>

            <div>

                <div class="form-card-title">
                    Información de campaña
                </div>

                <div class="form-card-description">
                    Actualiza los datos necesarios.
                </div>

            </div>

        </div>


        <form
            action="{{ route(
                'campanas.update',
                $campana->id_campana
            ) }}"
            method="POST"
            class="campaign-form"
        >

            @csrf
            @method('PUT')


            <div class="form-group full">

                <label class="form-label">
                    Nombre
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="nombre"
                    class="form-control"
                    maxlength="150"
                    value="{{ old(
                        'nombre',
                        $campana->nombre
                    ) }}"
                    required
                >

            </div>


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

                    @foreach($estados as $estado)

                        <option
                            value="{{ $estado->id_estado_campana }}"
                            {{ (string) old(
                                'id_estado_campana',
                                $campana->id_estado_campana
                            ) ===
                            (string) $estado->id_estado_campana
                                ? 'selected'
                                : ''
                            }}
                        >
                            {{ $estado->nombre }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Fecha inicio
                    <span class="required">*</span>
                </label>

                <input
                    type="date"
                    name="fecha_inicio"
                    id="fecha_inicio"
                    class="form-control"
                    value="{{ old(
                        'fecha_inicio',
                        $campana->fecha_inicio->format('Y-m-d')
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Fecha final
                    <span class="required">*</span>
                </label>

                <input
                    type="date"
                    name="fecha_fin"
                    id="fecha_fin"
                    class="form-control"
                    value="{{ old(
                        'fecha_fin',
                        $campana->fecha_fin->format('Y-m-d')
                    ) }}"
                    required
                >

            </div>


            <div class="form-group full">

                <label class="form-label">
                    Descripción
                </label>

                <textarea
                    name="descripcion"
                    class="form-control"
                    maxlength="2000"
                >{{ old(
                    'descripcion',
                    $campana->descripcion
                ) }}</textarea>

            </div>


            <div class="form-group full">

                <label class="form-label">
                    Objetivo
                </label>

                <textarea
                    name="objetivo"
                    class="form-control"
                    maxlength="2000"
                >{{ old(
                    'objetivo',
                    $campana->objetivo
                ) }}</textarea>

            </div>


            <div class="form-actions">

                <a
                    href="{{ route(
                        'campanas.show',
                        $campana->id_campana
                    ) }}"
                    class="btn-cancel"
                >
                    Cancelar
                </a>


                <button
                    type="submit"
                    class="btn-save"
                >
                    <i data-lucide="save"></i>
                    Guardar cambios
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

        fin.min = inicio.value;

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

    }

});
</script>

@endsection