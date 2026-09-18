@extends('layouts.app')

@section('title', 'Registrar Cliente - CRM')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Estilos delimitados para el módulo de clientes */
    .comi-cliente-form {
        --cf-bg: #0f172a;
        --cf-panel: #1e293b;
        --cf-border: #334155;
        --cf-text: #f1f5f9;
        --cf-muted: #a5b4c8;
        --cf-accent: #38bdf8;
        min-width: 0;
        padding: clamp(18px, 3vw, 36px);
        border-radius: 16px;
        background: var(--cf-bg);
        color: var(--cf-text);
        font-family: inherit;
        line-height: 1.5;
        color-scheme: dark;
    }
    .comi-cliente-form, .comi-cliente-form *, .comi-cliente-form *::before, .comi-cliente-form *::after { box-sizing: border-box; }
    .comi-cliente-form a { text-decoration: none; }
    .comi-cliente-form button, .comi-cliente-form input, .comi-cliente-form select { font: inherit; }
    .comi-cliente-form .cf-back { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; color: #b6c8df; font-size: 13px; margin-bottom: 20px; transition: color .15s; }
    .comi-cliente-form .cf-back:hover { color: var(--cf-accent); }
    .comi-cliente-form .cf-header { margin-bottom: 28px; }
    .comi-cliente-form .cf-eyebrow { margin: 0 0 10px; color: var(--cf-accent); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .comi-cliente-form .cf-title { margin: 0; color: var(--cf-text); font-size: clamp(26px, 3vw, 34px); font-weight: 700; line-height: 1.2; letter-spacing: -.035em; }
    .comi-cliente-form .cf-subtitle { margin: 10px 0 0; color: var(--cf-muted); font-size: 14px; max-width: 650px; }
    .comi-cliente-form .cf-card { max-width: 940px; background: var(--cf-panel); border: 1px solid var(--cf-border); border-radius: 13px; overflow: hidden; box-shadow: 0 14px 35px rgb(0 0 0 / 12%); }
    .comi-cliente-form .cf-card-heading { display: flex; align-items: center; gap: 13px; padding: 24px 28px; border-bottom: 1px solid var(--cf-border); }
    .comi-cliente-form .cf-heading-icon { display: grid; place-items: center; width: 44px; height: 44px; flex-shrink: 0; border: 1px solid #31516a; border-radius: 11px; background: #17364a; color: var(--cf-accent); font-size: 21px; }
    .comi-cliente-form .cf-card-title { margin: 0; color: var(--cf-text); font-size: 16px; font-weight: 600; }
    .comi-cliente-form .cf-card-note { margin: 4px 0 0; color: var(--cf-muted); font-size: 12px; }
    .comi-cliente-form .cf-form { margin: 0; }
    .comi-cliente-form .cf-body { padding: 28px; }
    .comi-cliente-form .cf-label { display: block; color: #e2e8f0; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
    .comi-cliente-form .cf-required { color: #fda4af; margin-left: 3px; }
    .comi-cliente-form .cf-field { position: relative; }
    .comi-cliente-form .cf-field > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 17px; pointer-events: none; }
    .comi-cliente-form .cf-input { display: block; width: 100%; min-height: 50px; padding: 12px 15px 12px 44px; border: 1px solid #475569; border-radius: 9px; background: var(--cf-bg); color: var(--cf-text); font-size: 14px; transition: border-color .15s, box-shadow .15s; }
    .comi-cliente-form .cf-input::placeholder { color: #94a3b8; opacity: 1; }
    .comi-cliente-form .cf-input:focus { outline: 2px solid var(--cf-accent); outline-offset: 2px; border-color: var(--cf-accent); }
    .comi-cliente-form .cf-field:focus-within > i { color: var(--cf-accent); }
    .comi-cliente-form .cf-input[aria-invalid="true"] { border-color: #fb7185; }
    .comi-cliente-form .cf-select { padding-left: 14px; }
    .comi-cliente-form .cf-select option { background: var(--cf-bg); color: var(--cf-text); }
    .comi-cliente-form .cf-help { margin: 10px 0 0; color: var(--cf-muted); font-size: 12px; }
    .comi-cliente-form .cf-error { display: flex; align-items: flex-start; gap: 7px; margin: 10px 0 0; color: #fda4af; font-size: 12px; }
    .comi-cliente-form .cf-footer { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap; padding: 20px 28px; border-top: 1px solid var(--cf-border); background: #1a2639; }
    .comi-cliente-form .cf-required-note { margin: 0; color: var(--cf-muted); font-size: 11px; }
    .comi-cliente-form .cf-actions { display: flex; align-items: center; gap: 10px; }
    .comi-cliente-form .cf-btn { display: inline-flex; justify-content: center; align-items: center; gap: 8px; min-height: 44px; padding: 10px 17px; border: 1px solid transparent; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background-color .15s, border-color .15s; }
    .comi-cliente-form .cf-btn-primary { background: #0284c7; color: #fff; border-color: #0284c7; }
    .comi-cliente-form .cf-btn-primary:hover { background: #0369a1; border-color: var(--cf-accent); }
    .comi-cliente-form .cf-btn-secondary { background: #172337; color: #e2e8f0; border-color: #475569; }
    .comi-cliente-form .cf-btn-secondary:hover { background: #26364d; border-color: #94a3b8; }
    .comi-cliente-form .cf-alert { max-width: 940px; padding: 14px 16px; margin: 0 0 18px; border: 1px solid #9f4050; border-radius: 9px; color: #fecdd3; background: #422333; font-size: 13px; }
    .comi-cliente-form .cf-alert ul { margin: 8px 0 0; padding-left: 20px; }
    .comi-cliente-form .cf-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
    .comi-cliente-form .cf-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .comi-cliente-form .cf-section { margin: 0; padding: 0; border: 0; min-width: 0; }
    .comi-cliente-form .cf-section + .cf-section { margin-top: 30px; padding-top: 26px; border-top: 1px solid var(--cf-border); }
    .comi-cliente-form .cf-legend { display: flex; align-items: center; gap: 10px; padding: 0; margin-bottom: 18px; color: #f1f5f9; font-size: 15px; font-weight: 600; }
    .comi-cliente-form .cf-step { display: inline-grid; place-items: center; width: 27px; height: 27px; background: #17364a; color: #7dd3fc; border: 1px solid #31516a; border-radius: 8px; font-size: 12px; }

    @media (max-width: 840px) {
        .comi-cliente-form .cf-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .comi-cliente-form { padding: 18px 12px; border-radius: 10px; }
        .comi-cliente-form .cf-card-heading, .comi-cliente-form .cf-body { padding: 20px 18px; }
        .comi-cliente-form .cf-footer { padding: 18px; flex-direction: column; align-items: stretch; }
        .comi-cliente-form .cf-actions { flex-wrap: wrap; }
        .comi-cliente-form .cf-actions .cf-btn { flex: 1 1 150px; }
    }
</style>

<section class="comi-cliente-form" aria-labelledby="cliente-create-title">
    <a href="{{ route('clientes.index') }}" class="cf-back">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a clientes
    </a>

    <header class="cf-header">
        <p class="cf-eyebrow">COMICenter / Gestión Comercial</p>
        <h1 class="cf-title" id="cliente-create-title">Registrar nuevo cliente</h1>
        <p class="cf-subtitle">Ingresa la información del contacto para agregarlo al directorio de oportunidades.</p>
    </header>

    @if (session('error'))
        <div class="cf-alert" role="alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="cf-alert" role="alert">
            No se pudo completar el registro. Revisa los datos del formulario.
            <ul>
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cf-card">
        <div class="cf-card-heading">
            <span class="cf-heading-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
            <div>
                <h2 class="cf-card-title">Ficha de Cliente</h2>
                <p class="cf-card-note">Completa los datos requeridos para vincular a esta persona o empresa.</p>
            </div>
        </div>

        <form action="{{ route('clientes.store') }}" method="POST" class="cf-form">
            @csrf
            <div class="cf-body">

                <!-- Paso 1: Datos Personales -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">1</span> Datos personales</legend>
                    <div class="cf-grid">
                        <div>
                            <label for="nombre" class="cf-label">Nombre <span class="cf-required" aria-hidden="true">*</span></label>
                            <div class="cf-field">
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <input type="text" name="nombre" id="nombre" class="cf-input" value="{{ old('nombre') }}" maxlength="100" required placeholder="Ej. Juan">
                            </div>
                        </div>

                        <div>
                            <label for="apellido_paterno" class="cf-label">Apellido paterno <span class="cf-required" aria-hidden="true">*</span></label>
                            <div class="cf-field">
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <input type="text" name="apellido_paterno" id="apellido_paterno" class="cf-input" value="{{ old('apellido_paterno') }}" maxlength="100" required placeholder="Ej. Pérez">
                            </div>
                        </div>

                        <div>
                            <label for="apellido_materno" class="cf-label">Apellido materno</label>
                            <div class="cf-field">
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <input type="text" name="apellido_materno" id="apellido_materno" class="cf-input" value="{{ old('apellido_materno') }}" maxlength="100" placeholder="Ej. López">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Paso 2: Contacto y Empresa -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">2</span> Contacto y empresa</legend>
                    <div class="cf-grid cf-grid-2">
                        <div>
                            <label for="empresa" class="cf-label">Compañía/Empresa del Contacto</label>
                            <div class="cf-field">
                                <i class="bi bi-building" aria-hidden="true"></i>
                                <input type="text" name="empresa" id="empresa" class="cf-input" value="{{ old('empresa') }}" maxlength="150" placeholder="Ej. ACME Corp">
                            </div>
                        </div>

                        <div>
                            <label for="correo" class="cf-label">Correo electrónico</label>
                            <div class="cf-field">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <input type="email" name="correo" id="correo" class="cf-input" value="{{ old('correo') }}" maxlength="150" placeholder="cliente@empresa.com">
                            </div>
                        </div>

                        <div>
                            <label for="telefono_principal" class="cf-label">Teléfono principal <span class="cf-required" aria-hidden="true">*</span></label>
                            <div class="cf-field">
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                <input type="text" name="telefono_principal" id="telefono_principal" class="cf-input" value="{{ old('telefono_principal') }}" maxlength="20" required placeholder="5512345678">
                            </div>
                        </div>

                        <div>
                            <label for="telefono_secundario" class="cf-label">Teléfono secundario</label>
                            <div class="cf-field">
                                <i class="bi bi-telephone-plus" aria-hidden="true"></i>
                                <input type="text" name="telefono_secundario" id="telefono_secundario" class="cf-input" value="{{ old('telefono_secundario') }}" maxlength="20" placeholder="Opcional">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Paso 3: Ubicación y Clasificación -->
                <fieldset class="cf-section">
                    <legend class="cf-legend"><span class="cf-step" aria-hidden="true">3</span> Ubicación y clasificación</legend>
                    <div class="cf-grid">
                        <!-- País -->
                        <div>
                            <label for="select-pais" class="cf-label">País</label>
                            <select name="pais" id="select-pais" class="cf-input cf-select">
                                <option value="">Selecciona un país...</option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="select-estado" class="cf-label">Estado / Provincia</label>
                            <select name="estado" id="select-estado" disabled class="cf-input cf-select">
                                <option value="">Primero elige un país</option>
                            </select>
                        </div>

                        <!-- Ciudad -->
                        <div>
                            <label for="select-ciudad" class="cf-label">Ciudad</label>
                            <select name="ciudad" id="select-ciudad" disabled class="cf-input cf-select">
                                <option value="">Primero elige un estado</option>
                            </select>
                        </div>

                        <!-- Fuente -->
                        <div>
                            <label for="fuente" class="cf-label">Fuente (Origen)</label>
                            <div class="cf-field">
                                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                                <input type="text" name="fuente" id="fuente" class="cf-input" placeholder="Ej. Facebook, Referido" value="{{ old('fuente') }}" maxlength="100">
                            </div>
                        </div>

                        <!-- Tipo de Cliente -->
                        <div>
                            <label for="id_tipo_cliente" class="cf-label">Tipo de cliente <span class="cf-required" aria-hidden="true">*</span></label>
                            <select name="id_tipo_cliente" id="id_tipo_cliente" class="cf-input cf-select" required>
                                <option value="1" {{ old('id_tipo_cliente', '1') == '1' ? 'selected' : '' }}>B2B (Empresa)</option>
                                <option value="2" {{ old('id_tipo_cliente') == '2' ? 'selected' : '' }}>B2C (Consumidor final)</option>
                                <option value="3" {{ old('id_tipo_cliente') == '3' ? 'selected' : '' }}>Socio Comercial</option>
                            </select>
                        </div>

                        <!-- Estado Lead -->
                        <div>
                            <label for="id_estado_lead" class="cf-label">Estado Lead <span class="cf-required" aria-hidden="true">*</span></label>
                            <select name="id_estado_lead" id="id_estado_lead" class="cf-input cf-select" required>
                                <option value="1" {{ old('id_estado_lead', '1') == '1' ? 'selected' : '' }}>Nuevo</option>
                                <option value="2" {{ old('id_estado_lead') == '2' ? 'selected' : '' }}>Contactado</option>
                                <option value="3" {{ old('id_estado_lead') == '3' ? 'selected' : '' }}>Interesado</option>
                                <option value="4" {{ old('id_estado_lead') == '4' ? 'selected' : '' }}>Cliente</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

            </div>

            <div class="cf-footer">
                <p class="cf-required-note"><span class="cf-required" aria-hidden="true">*</span> Campos obligatorios</p>
                <div class="cf-actions">
                    <a href="{{ route('clientes.index') }}" class="cf-btn cf-btn-secondary">Cancelar</a>
                    <button type="submit" class="cf-btn cf-btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i> Guardar cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectPais = document.getElementById('select-pais');
    const selectEstado = document.getElementById('select-estado');
    const selectCiudad = document.getElementById('select-ciudad');

    if (!selectPais || !selectEstado || !selectCiudad) return;

    const oldPais = @json(old('pais'));
    const oldEstado = @json(old('estado'));
    const oldCiudad = @json(old('ciudad'));

    // 1. Cargar Paises
    fetch('https://countriesnow.space/api/v0.1/countries')
        .then(response => response.json())
        .then(data => {
            data.data.forEach(pais => {
                let option = document.createElement('option');
                option.value = pais.country;
                option.textContent = pais.country;
                if (oldPais && oldPais === pais.country) option.selected = true;
                selectPais.appendChild(option);
            });

            if (oldPais) {
                cargarEstados(oldPais, oldEstado, oldCiudad);
            }
        })
        .catch(() => {});

    // 2. Cargar Estados
    function cargarEstados(paisNombre, estadoSeleccionado = null, ciudadSeleccionada = null) {
        selectEstado.innerHTML = '<option value="">Cargando estados...</option>';
        selectCiudad.innerHTML = '<option value="">Primero elige un estado</option>';
        selectEstado.disabled = true;
        selectCiudad.disabled = true;

        if (paisNombre) {
            fetch('https://countriesnow.space/api/v0.1/countries/states', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: paisNombre })
            })
            .then(res => res.json())
            .then(data => {
                selectEstado.innerHTML = '<option value="">Selecciona un estado...</option>';
                data.data.states.forEach(estado => {
                    let option = document.createElement('option');
                    option.value = estado.name;
                    option.textContent = estado.name;
                    if (estadoSeleccionado && estadoSeleccionado === estado.name) option.selected = true;
                    selectEstado.appendChild(option);
                });
                selectEstado.disabled = false;

                if (estadoSeleccionado) {
                    cargarCiudades(paisNombre, estadoSeleccionado, ciudadSeleccionada);
                }
            })
            .catch(() => {
                selectEstado.innerHTML = '<option value="">Error al cargar estados</option>';
            });
        }
    }

    // 3. Cargar Ciudades
    function cargarCiudades(paisNombre, estadoNombre, ciudadSeleccionada = null) {
        selectCiudad.innerHTML = '<option value="">Cargando ciudades...</option>';
        selectCiudad.disabled = true;

        if (estadoNombre) {
            fetch('https://countriesnow.space/api/v0.1/countries/state/cities', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: paisNombre, state: estadoNombre })
            })
            .then(res => res.json())
            .then(data => {
                selectCiudad.innerHTML = '<option value="">Selecciona una ciudad...</option>';
                data.data.forEach(ciudad => {
                    let option = document.createElement('option');
                    option.value = ciudad;
                    option.textContent = ciudad;
                    if (ciudadSeleccionada && ciudadSeleccionada === ciudad) option.selected = true;
                    selectCiudad.appendChild(option);
                });
                selectCiudad.disabled = false;
            })
            .catch(() => {
                selectCiudad.innerHTML = '<option value="">Error al cargar ciudades</option>';
            });
        }
    }

    selectPais.addEventListener('change', function() {
        cargarEstados(this.value);
    });

    selectEstado.addEventListener('change', function() {
        cargarCiudades(selectPais.value, this.value);
    });
});
</script>
@endsection