@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')

<style>
    .cliente-edit-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 34px 34px 60px;
    }

    .cliente-edit-header {
        margin-bottom: 28px;
    }

    .cliente-edit-kicker {
        margin: 0 0 8px;
        color: #38bdf8;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .cliente-edit-title {
        margin: 0;
        color: #f8fafc;
        font-size: 34px;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .cliente-edit-subtitle {
        margin: 10px 0 0;
        color: #94a3b8;
        font-size: 15px;
    }

    .cliente-edit-card {
        background: #172236;
        border: 1px solid #293850;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.16);
    }

    .cliente-edit-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 22px 26px;
        border-bottom: 1px solid #293850;
        background: #1c2a40;
    }

    .cliente-edit-avatar {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: rgba(14, 165, 233, 0.13);
        border: 1px solid rgba(56, 189, 248, 0.28);
        color: #38bdf8;
        flex: 0 0 auto;
    }

    .cliente-edit-avatar svg {
        width: 22px;
        height: 22px;
    }

    .cliente-edit-card-header h2 {
        margin: 0;
        color: #f8fafc;
        font-size: 17px;
        font-weight: 700;
    }

    .cliente-edit-card-header p {
        margin: 4px 0 0;
        color: #8ea1ba;
        font-size: 13px;
    }

    .cliente-edit-body {
        padding: 28px;
    }

    .cliente-section {
        padding: 0 0 30px;
        margin-bottom: 30px;
        border-bottom: 1px solid #293850;
    }

    .cliente-section:last-of-type {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .cliente-section-heading {
        display: flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 20px;
    }

    .cliente-section-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        background: rgba(14, 165, 233, 0.10);
        border: 1px solid rgba(56, 189, 248, 0.20);
        color: #38bdf8;
    }

    .cliente-section-icon svg {
        width: 17px;
        height: 17px;
    }

    .cliente-section-heading h3 {
        margin: 0;
        color: #f1f5f9;
        font-size: 16px;
        font-weight: 700;
    }

    .cliente-section-heading p {
        margin: 3px 0 0;
        color: #71839d;
        font-size: 12px;
    }

    .cliente-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .cliente-form-grid.two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .cliente-form-grid.four {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .cliente-field {
        min-width: 0;
    }

    .cliente-field label {
        display: block;
        margin-bottom: 8px;
        color: #b8c5d8;
        font-size: 12px;
        font-weight: 600;
    }

    .cliente-field label .required {
        color: #38bdf8;
    }

    .cliente-input,
    .cliente-select {
        width: 100%;
        min-height: 45px;
        padding: 0 13px;
        box-sizing: border-box;
        border-radius: 10px;
        border: 1px solid #34445c;
        outline: none;
        background: #0f1829;
        color: #e5edf7;
        font-family: inherit;
        font-size: 13px;
        transition: border-color .18s, box-shadow .18s, background .18s;
    }

    .cliente-input::placeholder {
        color: #56677f;
    }

    .cliente-input:hover,
    .cliente-select:hover {
        border-color: #435873;
    }

    .cliente-input:focus,
    .cliente-select:focus {
        border-color: #0ea5e9;
        background: #111d30;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.11);
    }

    .cliente-select {
        cursor: pointer;
    }

    .cliente-select:disabled {
        opacity: .48;
        cursor: not-allowed;
        background: #111a2a;
    }

    .cliente-error {
        margin-top: 6px;
        color: #f87171;
        font-size: 11px;
    }

    .cliente-alert {
        margin-bottom: 22px;
        padding: 13px 15px;
        border-radius: 10px;
        border: 1px solid rgba(248, 113, 113, .25);
        background: rgba(127, 29, 29, .18);
        color: #fca5a5;
        font-size: 13px;
    }

    .cliente-alert ul {
        margin: 0;
        padding-left: 18px;
    }

    .cliente-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-top: 30px;
        padding-top: 24px;
        border-top: 1px solid #293850;
    }

    .cliente-actions-left {
        color: #64748b;
        font-size: 12px;
    }

    .cliente-actions-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cliente-btn {
        min-height: 43px;
        padding: 0 18px;
        border-radius: 10px;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: transform .16s, background .16s, border-color .16s;
    }

    .cliente-btn svg {
        width: 16px;
        height: 16px;
    }

    .cliente-btn-secondary {
        background: #202e43;
        border-color: #34445c;
        color: #b9c7d9;
    }

    .cliente-btn-secondary:hover {
        background: #293a52;
        border-color: #435873;
        color: #f1f5f9;
    }

    .cliente-btn-primary {
        background: #0ea5e9;
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(14, 165, 233, .16);
    }

    .cliente-btn-primary:hover {
        background: #0284c7;
        transform: translateY(-1px);
    }

    @media (max-width: 900px) {
        .cliente-edit-page {
            padding: 25px 20px 45px;
        }

        .cliente-form-grid,
        .cliente-form-grid.two,
        .cliente-form-grid.four {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 620px) {
        .cliente-edit-page {
            padding: 20px 14px 35px;
        }

        .cliente-edit-title {
            font-size: 27px;
        }

        .cliente-edit-body {
            padding: 20px;
        }

        .cliente-edit-card-header {
            padding: 18px 20px;
        }

        .cliente-form-grid,
        .cliente-form-grid.two,
        .cliente-form-grid.four {
            grid-template-columns: 1fr;
        }

        .cliente-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .cliente-actions-right {
            width: 100%;
        }

        .cliente-btn {
            flex: 1;
        }
    }
</style>

<div class="cliente-edit-page">

    {{-- ENCABEZADO --}}
    <div class="cliente-edit-header">
        <p class="cliente-edit-kicker">COMICENTER / GESTIÓN COMERCIAL</p>

        <h1 class="cliente-edit-title">
            Editar Cliente
        </h1>

        <p class="cliente-edit-subtitle">
            Actualiza la información y clasificación de
            <strong style="color:#dbeafe;">
                {{ $cliente->nombre }}
            </strong>.
        </p>
    </div>

    {{-- TARJETA PRINCIPAL --}}
    <div class="cliente-edit-card">

        <div class="cliente-edit-card-header">
            <div class="cliente-edit-avatar">
                <i data-lucide="user-round"></i>
            </div>

            <div>
                <h2>Información del cliente</h2>
                <p>Modifica los datos registrados en el CRM.</p>
            </div>
        </div>

        <div class="cliente-edit-body">

            {{-- ERRORES --}}
            @if ($errors->any())
                <div class="cliente-alert">
                    <strong>Revisa los siguientes campos:</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('clientes.update', $cliente->id_cliente) }}"
                method="POST"
            >

                @csrf
                @method('PUT')

                {{-- =========================
                     DATOS PERSONALES
                ========================== --}}
                <div class="cliente-section">

                    <div class="cliente-section-heading">
                        <div class="cliente-section-icon">
                            <i data-lucide="user"></i>
                        </div>

                        <div>
                            <h3>Datos personales</h3>
                            <p>Información básica del contacto.</p>
                        </div>
                    </div>

                    <div class="cliente-form-grid">

                        <div class="cliente-field">
                            <label>
                                Nombre <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                value="{{ old('nombre', $cliente->nombre) }}"
                                required
                                class="cliente-input"
                                placeholder="Nombre"
                            >
                        </div>

                        <div class="cliente-field">
                            <label>
                                Apellido Paterno <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                name="apellido_paterno"
                                value="{{ old('apellido_paterno', $cliente->apellido_paterno) }}"
                                required
                                class="cliente-input"
                                placeholder="Apellido paterno"
                            >
                        </div>

                        <div class="cliente-field">
                            <label>Apellido Materno</label>

                            <input
                                type="text"
                                name="apellido_materno"
                                value="{{ old('apellido_materno', $cliente->apellido_materno) }}"
                                class="cliente-input"
                                placeholder="Apellido materno"
                            >
                        </div>

                    </div>
                </div>

                {{-- =========================
                     CONTACTO
                ========================== --}}
                <div class="cliente-section">

                    <div class="cliente-section-heading">
                        <div class="cliente-section-icon">
                            <i data-lucide="phone"></i>
                        </div>

                        <div>
                            <h3>Contacto y empresa</h3>
                            <p>Medios de contacto y datos comerciales.</p>
                        </div>
                    </div>

                    <div class="cliente-form-grid two">

                        <div class="cliente-field">
                            <label>Empresa</label>

                            <input
                                type="text"
                                name="empresa"
                                value="{{ old('empresa', $cliente->empresa) }}"
                                class="cliente-input"
                                placeholder="Nombre de la empresa"
                            >
                        </div>

                        <div class="cliente-field">
                            <label>Correo electrónico</label>

                            <input
                                type="email"
                                name="correo"
                                value="{{ old('correo', $cliente->correo) }}"
                                class="cliente-input"
                                placeholder="correo@ejemplo.com"
                            >
                        </div>

                        <div class="cliente-field">
                            <label>
                                Teléfono principal <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                name="telefono_principal"
                                value="{{ old('telefono_principal', $cliente->telefono_principal) }}"
                                required
                                class="cliente-input"
                                placeholder="5512345678"
                            >
                        </div>

                        <div class="cliente-field">
                            <label>Teléfono secundario</label>

                            <input
                                type="text"
                                name="telefono_secundario"
                                value="{{ old('telefono_secundario', $cliente->telefono_secundario) }}"
                                class="cliente-input"
                                placeholder="Teléfono alternativo"
                            >
                        </div>

                    </div>
                </div>

                {{-- =========================
                     UBICACIÓN
                ========================== --}}
                <div class="cliente-section">

                    <div class="cliente-section-heading">
                        <div class="cliente-section-icon">
                            <i data-lucide="map-pin"></i>
                        </div>

                        <div>
                            <h3>Ubicación</h3>
                            <p>País, estado y ciudad del cliente.</p>
                        </div>
                    </div>

                    <div class="cliente-form-grid">

                        <div class="cliente-field">
                            <label>País</label>

                            <select
                                name="pais"
                                id="select-pais"
                                class="cliente-select"
                            >
                                <option value="">Selecciona un país...</option>
                            </select>
                        </div>

                        <div class="cliente-field">
                            <label>Estado / Provincia</label>

                            <select
                                name="estado"
                                id="select-estado"
                                disabled
                                class="cliente-select"
                            >
                                <option value="">Primero elige un país</option>
                            </select>
                        </div>

                        <div class="cliente-field">
                            <label>Ciudad</label>

                            <select
                                name="ciudad"
                                id="select-ciudad"
                                disabled
                                class="cliente-select"
                            >
                                <option value="">Primero elige un estado</option>
                            </select>
                        </div>

                    </div>
                </div>

                {{-- =========================
                     CLASIFICACIÓN
                ========================== --}}
                <div class="cliente-section">

                    <div class="cliente-section-heading">
                        <div class="cliente-section-icon">
                            <i data-lucide="tags"></i>
                        </div>

                        <div>
                            <h3>Clasificación comercial</h3>
                            <p>Origen, tipo y estado actual del cliente.</p>
                        </div>
                    </div>

                    <div class="cliente-form-grid">

                        <div class="cliente-field">
                            <label>Fuente (Origen)</label>

                            <input
                                type="text"
                                name="fuente"
                                id="fuente"
                                value="{{ old('fuente', $cliente->fuente) }}"
                                placeholder="Ej. Facebook, Referido"
                                class="cliente-input"
                            >
                        </div>

                        <div class="cliente-field">
                            <label>
                                Tipo de Cliente <span class="required">*</span>
                            </label>

                            <select
                                name="id_tipo_cliente"
                                id="id_tipo_cliente"
                                required
                                class="cliente-select"
                            >
                                <option value="1"
                                    {{ old('id_tipo_cliente', $cliente->id_tipo_cliente) == 1 ? 'selected' : '' }}>
                                    B2B (Empresa)
                                </option>

                                <option value="2"
                                    {{ old('id_tipo_cliente', $cliente->id_tipo_cliente) == 2 ? 'selected' : '' }}>
                                    B2C (Consumidor final)
                                </option>

                                <option value="3"
                                    {{ old('id_tipo_cliente', $cliente->id_tipo_cliente) == 3 ? 'selected' : '' }}>
                                    Socio Comercial
                                </option>
                            </select>
                        </div>

                        <div class="cliente-field">
                            <label>
                                Estado Lead <span class="required">*</span>
                            </label>

                            <select
                                name="id_estado_lead"
                                id="id_estado_lead"
                                required
                                class="cliente-select"
                            >
                                <option value="1"
                                    {{ old('id_estado_lead', $cliente->id_estado_lead) == 1 ? 'selected' : '' }}>
                                    Nuevo
                                </option>

                                <option value="2"
                                    {{ old('id_estado_lead', $cliente->id_estado_lead) == 2 ? 'selected' : '' }}>
                                    Contactado
                                </option>

                                <option value="3"
                                    {{ old('id_estado_lead', $cliente->id_estado_lead) == 3 ? 'selected' : '' }}>
                                    Interesado
                                </option>

                                <option value="4"
                                    {{ old('id_estado_lead', $cliente->id_estado_lead) == 4 ? 'selected' : '' }}>
                                    Cliente
                                </option>
                            </select>
                        </div>

                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="cliente-actions">

                    <div class="cliente-actions-left">
                        Los campos marcados con * son obligatorios.
                    </div>

                    <div class="cliente-actions-right">

                        <a
                            href="{{ route('clientes.index') }}"
                            class="cliente-btn cliente-btn-secondary"
                        >
                            <i data-lucide="arrow-left"></i>
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="cliente-btn cliente-btn-primary"
                        >
                            <i data-lucide="save"></i>
                            Actualizar cliente
                        </button>

                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const selectPais = document.getElementById('select-pais');
    const selectEstado = document.getElementById('select-estado');
    const selectCiudad = document.getElementById('select-ciudad');

    const paisActual = @json(old('pais', $cliente->pais));
    const estadoActual = @json(old('estado', $cliente->estado));
    const ciudadActual = @json(old('ciudad', $cliente->ciudad));

    /*
    |--------------------------------------------------------------------------
    | Cargar países
    |--------------------------------------------------------------------------
    */

    fetch('https://countriesnow.space/api/v0.1/countries')
        .then(response => response.json())
        .then(data => {

            data.data.forEach(pais => {

                const option = document.createElement('option');

                option.value = pais.country;
                option.textContent = pais.country;

                if (pais.country === paisActual) {
                    option.selected = true;
                }

                selectPais.appendChild(option);
            });

            if (paisActual) {
                cargarEstados(paisActual);
            }

        })
        .catch(error => {
            console.error('Error cargando países:', error);
        });


    /*
    |--------------------------------------------------------------------------
    | Cargar estados
    |--------------------------------------------------------------------------
    */

    function cargarEstados(pais) {

        selectEstado.innerHTML =
            '<option value="">Cargando estados...</option>';

        selectCiudad.innerHTML =
            '<option value="">Primero elige un estado</option>';

        selectEstado.disabled = true;
        selectCiudad.disabled = true;

        fetch('https://countriesnow.space/api/v0.1/countries/states', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json'
            },

            body: JSON.stringify({
                country: pais
            })

        })
        .then(response => response.json())
        .then(data => {

            selectEstado.innerHTML =
                '<option value="">Selecciona un estado...</option>';

            data.data.states.forEach(estado => {

                const option = document.createElement('option');

                option.value = estado.name;
                option.textContent = estado.name;

                if (estado.name === estadoActual) {
                    option.selected = true;
                }

                selectEstado.appendChild(option);
            });

            selectEstado.disabled = false;

            if (estadoActual) {
                cargarCiudades(pais, estadoActual);
            }

        })
        .catch(error => {

            console.error('Error cargando estados:', error);

            selectEstado.innerHTML =
                '<option value="">No se pudieron cargar los estados</option>';
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Cambio de país
    |--------------------------------------------------------------------------
    */

    selectPais.addEventListener('change', function () {

        if (!this.value) {

            selectEstado.innerHTML =
                '<option value="">Primero elige un país</option>';

            selectCiudad.innerHTML =
                '<option value="">Primero elige un estado</option>';

            selectEstado.disabled = true;
            selectCiudad.disabled = true;

            return;
        }

        cargarEstados(this.value);
    });


    /*
    |--------------------------------------------------------------------------
    | Cargar ciudades
    |--------------------------------------------------------------------------
    */

    function cargarCiudades(pais, estado) {

        selectCiudad.innerHTML =
            '<option value="">Cargando ciudades...</option>';

        selectCiudad.disabled = true;

        fetch('https://countriesnow.space/api/v0.1/countries/state/cities', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json'
            },

            body: JSON.stringify({
                country: pais,
                state: estado
            })

        })
        .then(response => response.json())
        .then(data => {

            selectCiudad.innerHTML =
                '<option value="">Selecciona una ciudad...</option>';

            data.data.forEach(ciudad => {

                const option = document.createElement('option');

                option.value = ciudad;
                option.textContent = ciudad;

                if (ciudad === ciudadActual) {
                    option.selected = true;
                }

                selectCiudad.appendChild(option);
            });

            selectCiudad.disabled = false;

        })
        .catch(error => {

            console.error('Error cargando ciudades:', error);

            selectCiudad.innerHTML =
                '<option value="">No se pudieron cargar las ciudades</option>';
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Cambio de estado
    |--------------------------------------------------------------------------
    */

    selectEstado.addEventListener('change', function () {

        if (!this.value) {

            selectCiudad.innerHTML =
                '<option value="">Primero elige un estado</option>';

            selectCiudad.disabled = true;

            return;
        }

        cargarCiudades(selectPais.value, this.value);
    });

});
</script>

@endsection