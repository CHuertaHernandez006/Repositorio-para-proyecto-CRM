@extends('layouts.app')

@section('content')

<style>
    .cita-edit-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 32px 26px;
        color: #e8eef7;
    }

    /* =========================================================
       ENCABEZADO
    ========================================================== */

    .cita-edit-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px;
    }

    .cita-edit-kicker {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 9px;
        color: #35c6ff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .cita-edit-kicker-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #35c6ff;
        box-shadow: 0 0 10px rgba(53, 198, 255, .6);
    }

    .cita-edit-title {
        margin: 0;
        color: #ffffff;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.03em;
    }

    .cita-edit-subtitle {
        margin: 9px 0 0;
        color: #8190a7;
        font-size: 14px;
        line-height: 1.6;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 39px;
        padding: 0 14px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 9px;
        background: transparent;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .back-btn svg {
        width: 15px;
        height: 15px;
    }

    .back-btn:hover {
        border-color: rgba(148, 163, 184, .2);
        background: rgba(255, 255, 255, .035);
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* =========================================================
       ERRORES
    ========================================================== */

    .validation-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 13px 15px;
        margin-bottom: 20px;
        border: 1px solid rgba(248, 113, 113, .16);
        border-radius: 10px;
        background: rgba(248, 113, 113, .06);
        color: #fca5a5;
        font-size: 12px;
        line-height: 1.5;
    }

    .validation-alert svg {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        margin-top: 1px;
    }

    .validation-alert ul {
        margin: 5px 0 0;
        padding-left: 18px;
    }

    /* =========================================================
       FORMULARIO
    ========================================================== */

    .cita-form {
        display: grid;
        gap: 20px;
    }

    .form-card {
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 16px;
        background: #111c30;
        box-shadow: 0 20px 50px rgba(0, 0, 0, .10);
    }

    .form-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 19px 21px;
        border-bottom: 1px solid rgba(255, 255, 255, .055);
    }

    .form-card-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: 1px solid rgba(53, 198, 255, .12);
        border-radius: 9px;
        background: rgba(53, 198, 255, .08);
        color: #35c6ff;
    }

    .form-card-icon svg {
        width: 16px;
        height: 16px;
    }

    .form-card-title {
        margin: 0;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
    }

    .form-card-subtitle {
        margin: 3px 0 0;
        color: #66758d;
        font-size: 11px;
    }

    .form-card-body {
        padding: 22px;
    }

    /* =========================================================
       GRID DE CAMPOS
    ========================================================== */

    .fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field-label {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
    }

    .field-label span {
        color: #35c6ff;
    }

    .field-control {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 9px;
        outline: none;
        background: #0c1628;
        color: #e8eef7;
        font-family: inherit;
        font-size: 12px;
        transition: .2s ease;
        box-sizing: border-box;
    }

    .field-control::placeholder {
        color: #526177;
    }

    .field-control:hover {
        border-color: rgba(255, 255, 255, .12);
    }

    .field-control:focus {
        border-color: rgba(53, 198, 255, .45);
        box-shadow: 0 0 0 3px rgba(53, 198, 255, .07);
    }

    .field-control:disabled {
        opacity: .75;
        cursor: not-allowed;
        background: #0a1424;
    }

    select.field-control {
        cursor: pointer;
    }

    textarea.field-control {
        min-height: 115px;
        resize: vertical;
        line-height: 1.6;
    }

    .field-error {
        color: #f87171;
        font-size: 10px;
        line-height: 1.4;
    }

    .field-help {
        margin: 0;
        color: #5f6f86;
        font-size: 10px;
        line-height: 1.5;
    }

    /* =========================================================
       AVISO HORA DE FINALIZACIÓN
    ========================================================== */

    .finish-info {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 13px;
        margin-top: 18px;
        border: 1px solid rgba(52, 211, 153, .10);
        border-radius: 10px;
        background: rgba(52, 211, 153, .035);
    }

    .finish-info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        border: 1px solid rgba(52, 211, 153, .13);
        border-radius: 8px;
        background: rgba(52, 211, 153, .07);
        color: #34d399;
    }

    .finish-info-icon svg {
        width: 15px;
        height: 15px;
    }

    .finish-info-title {
        margin: 0 0 3px;
        color: #8ee8c5;
        font-size: 11px;
        font-weight: 700;
    }

    .finish-info-text {
        margin: 0;
        color: #62748b;
        font-size: 10px;
        line-height: 1.5;
    }

    /* =========================================================
       ACCIONES
    ========================================================== */

    .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
        padding-top: 2px;
    }

    .form-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 41px;
        padding: 0 16px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .form-action svg {
        width: 15px;
        height: 15px;
    }

    .form-action.cancel {
        border: 1px solid rgba(255, 255, 255, .08);
        background: transparent;
        color: #94a3b8;
    }

    .form-action.cancel:hover {
        border-color: rgba(255, 255, 255, .14);
        background: rgba(255, 255, 255, .035);
        color: #ffffff;
    }

    .form-action.save {
        border: 1px solid rgba(53, 198, 255, .28);
        background: rgba(53, 198, 255, .10);
        color: #55ceff;
    }

    .form-action.save:hover {
        border-color: rgba(53, 198, 255, .45);
        background: rgba(53, 198, 255, .16);
        color: #7bdcff;
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(53, 198, 255, .08);
    }

    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 750px) {

        .cita-edit-page {
            padding: 22px 15px;
        }

        .cita-edit-header {
            flex-direction: column;
        }

        .fields-grid {
            grid-template-columns: 1fr;
        }

        .field.full {
            grid-column: auto;
        }

        .back-btn {
            width: 100%;
        }

        .form-card-body {
            padding: 17px;
        }

        .form-actions {
            flex-direction: column-reverse;
            width: 100%;
        }

        .form-action {
            width: 100%;
        }
    }
</style>


<div class="cita-edit-page">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}

    <div class="cita-edit-header">

        <div>

            <div class="cita-edit-kicker">
                <span class="cita-edit-kicker-dot"></span>
                Gestión de citas
            </div>

            <h1 class="cita-edit-title">
                Editar cita #{{ $cita->id_cita }}
            </h1>

            <p class="cita-edit-subtitle">
                Modifica la información registrada de esta cita.
            </p>

        </div>

        <a
            href="{{ route('citas.show', $cita->id_cita) }}"
            class="back-btn"
        >
            <i data-lucide="arrow-left"></i>
            Volver al detalle
        </a>

    </div>


    {{-- =========================================================
         ERRORES DE VALIDACIÓN
    ========================================================== --}}

    @if($errors->any())

        <div class="validation-alert">

            <i data-lucide="circle-alert"></i>

            <div>

                <strong>
                    Revisa los datos del formulario.
                </strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        </div>

    @endif


    {{-- =========================================================
         FORMULARIO
    ========================================================== --}}

    <form
        action="{{ route('citas.update', $cita->id_cita) }}"
        method="POST"
        class="cita-form"
    >

        @csrf
        @method('PUT')


        <div class="form-card">

            <div class="form-card-header">

                <div class="form-card-icon">
                    <i data-lucide="calendar-check-2"></i>
                </div>

                <div>

                    <h2 class="form-card-title">
                        Información de la cita
                    </h2>

                    <p class="form-card-subtitle">
                        Actualiza los datos principales.
                    </p>

                </div>

            </div>


            <div class="form-card-body">

                <div class="fields-grid">


                    {{-- =================================================
                         CLIENTE
                    ================================================== --}}

                    <div class="field">

                        <label class="field-label">
                            Cliente <span>*</span>
                        </label>

                        <input
                            type="text"
                            class="field-control"
                            value="{{ $cita->cliente
                                ? trim(
                                    $cita->cliente->nombre . ' ' .
                                    $cita->cliente->apellido_paterno . ' ' .
                                    $cita->cliente->apellido_materno
                                )
                                : 'Cliente no disponible'
                            }}"
                            disabled
                        >

                        <input
                            type="hidden"
                            name="id_cliente"
                            value="{{ $cita->id_cliente }}"
                        >

                        <p class="field-help">
                            El cliente asociado a la cita no puede modificarse desde aquí.
                        </p>

                        @error('id_cliente')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- =================================================
                         OPERARIO
                    ================================================== --}}

                    <div class="field">

                        <label
                            for="id_usuario"
                            class="field-label"
                        >
                            Operario <span>*</span>
                        </label>

                        <select
                            id="id_usuario"
                            name="id_usuario"
                            class="field-control"
                            required
                        >

                            @if($cita->usuario)

                                <option
                                    value="{{ $cita->usuario->id }}"
                                    selected
                                >
                                    {{ $cita->usuario->name }}
                                </option>

                            @else

                                <option value="">
                                    Selecciona un operario
                                </option>

                            @endif


                            @foreach($operarios as $operario)

                                @if(!$cita->usuario || $operario->id != $cita->usuario->id)

                                    <option
                                        value="{{ $operario->id }}"
                                        {{ old('id_usuario') == $operario->id ? 'selected' : '' }}
                                    >
                                        {{ $operario->name }}
                                    </option>

                                @endif

                            @endforeach

                        </select>

                        @error('id_usuario')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- =================================================
                         FECHA Y HORA DE INICIO
                    ================================================== --}}

                    <div class="field">

                        <label
                            for="fecha_hora_inicio"
                            class="field-label"
                        >
                            Fecha y hora de inicio <span>*</span>
                        </label>

                        <input
                            type="datetime-local"
                            id="fecha_hora_inicio"
                            name="fecha_hora_inicio"
                            class="field-control"
                            value="{{ old(
                                'fecha_hora_inicio',
                                $cita->fecha_hora_inicio
                                    ? $cita->fecha_hora_inicio->format('Y-m-d\TH:i')
                                    : ''
                            ) }}"
                            required
                        >

                        @error('fecha_hora_inicio')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- =================================================
                         ESTADO
                    ================================================== --}}

                    <div class="field">

                        <label
                            for="id_estado_cita"
                            class="field-label"
                        >
                            Estado <span>*</span>
                        </label>

                        <select
                            id="id_estado_cita"
                            name="id_estado_cita"
                            class="field-control"
                            required
                        >

                            @foreach($estados as $estado)

                                <option
                                    value="{{ $estado->id_estado_cita }}"
                                    {{ old(
                                        'id_estado_cita',
                                        $cita->id_estado_cita
                                    ) == $estado->id_estado_cita ? 'selected' : '' }}
                                >
                                    {{ $estado->nombre }}
                                </option>

                            @endforeach

                        </select>

                        @error('id_estado_cita')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- =================================================
                         ID LLAMADA
                    ================================================== --}}

                    <div class="field">

                        <label
                            for="id_llamada"
                            class="field-label"
                        >
                            ID de llamada
                        </label>

                        <input
                            type="number"
                            id="id_llamada"
                            name="id_llamada"
                            class="field-control"
                            min="1"
                            value="{{ old('id_llamada', $cita->id_llamada) }}"
                            placeholder="Opcional"
                        >

                        @error('id_llamada')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- =================================================
                         MOTIVO
                    ================================================== --}}

                    <div class="field">

                        <label
                            for="motivo"
                            class="field-label"
                        >
                            Motivo
                        </label>

                        <input
                            type="text"
                            id="motivo"
                            name="motivo"
                            class="field-control"
                            maxlength="255"
                            value="{{ old('motivo', $cita->motivo) }}"
                            placeholder="Motivo de la cita..."
                        >

                        @error('motivo')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- =================================================
                         OBSERVACIONES
                    ================================================== --}}

                    <div class="field full">

                        <label
                            for="observaciones"
                            class="field-label"
                        >
                            Observaciones
                        </label>

                        <textarea
                            id="observaciones"
                            name="observaciones"
                            class="field-control"
                            maxlength="1000"
                            placeholder="Agrega información adicional sobre la cita..."
                        >{{ old('observaciones', $cita->observaciones) }}</textarea>

                        @error('observaciones')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>


                {{-- =================================================
                     AVISO DE FINALIZACIÓN
                ================================================== --}}

                <div class="finish-info">

                    <div class="finish-info-icon">
                        <i data-lucide="clock-3"></i>
                    </div>

                    <div>

                        <p class="finish-info-title">
                            Hora de finalización automática
                        </p>

                        <p class="finish-info-text">
                            La hora de finalización no se modifica desde este formulario.
                            Se registrará automáticamente cuando la cita sea marcada como terminada.
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             ACCIONES
        ========================================================== --}}

        <div class="form-actions">

            <a
                href="{{ route('citas.show', $cita->id_cita) }}"
                class="form-action cancel"
            >
                <i data-lucide="x"></i>
                Cancelar
            </a>

            <button
                type="submit"
                class="form-action save"
            >
                <i data-lucide="save"></i>
                Guardar cambios
            </button>

        </div>

    </form>

</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    });
</script>

@endsection