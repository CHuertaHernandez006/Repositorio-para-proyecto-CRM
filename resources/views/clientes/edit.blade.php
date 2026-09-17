@extends('layouts.app')
@section('title', 'Editar Cliente')
@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100 mt-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-6 border-b pb-4">Editar Cliente: {{ $cliente->nombre }}</h2>

    <form action="{{ route('clientes.update', $cliente->id_cliente) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Datos Personales -->
        <div>
            <h3 class="text-lg font-semibold text-slate-700 mb-3">Datos Personales</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nombre *</label>
                    <input type="text" name="nombre" value="{{ $cliente->nombre }}" required class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Apellido Paterno *</label>
                    <input type="text" name="apellido_paterno" value="{{ $cliente->apellido_paterno }}" required class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Apellido Materno</label>
                    <input type="text" name="apellido_materno" value="{{ $cliente->apellido_materno }}" class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>
        </div>

        <!-- Contacto y Empresa -->
        <div>
            <h3 class="text-lg font-semibold text-slate-700 mb-3">Contacto y Empresa</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Empresa</label>
                    <input type="text" name="empresa" value="{{ $cliente->empresa }}" class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Correo Electrónico</label>
                    <input type="email" name="correo" value="{{ $cliente->correo }}" class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Teléfono Principal *</label>
                    <input type="text" name="telefono_principal" value="{{ $cliente->telefono_principal }}" required class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Teléfono Secundario</label>
                    <input type="text" name="telefono_secundario" value="{{ $cliente->telefono_secundario }}" class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>
        </div>

        <!-- Ubicación y Clasificación -->
        <div>
            <h3 class="text-lg font-semibold text-slate-700 mb-3">Ubicación y Clasificación</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- País -->
                <div>
                    <label for="select-pais" class="block text-sm font-medium text-slate-600 mb-1">País</label>           
                    <select name="pais" id="select-pais" class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Selecciona un país...</option>
                    </select>
                </div>

                <!-- Estado / Provincia -->
                <div>
                    <label for="select-estado" class="block text-sm font-medium text-slate-600 mb-1">Estado / Provincia</label>
                    <select name="estado" id="select-estado" disabled class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border bg-slate-50 disabled:opacity-50">                
                        <option value="">Primero elige un país</option>
                    </select>
                </div>

                <!-- Ciudad -->
                <div>
                    <label for="select-ciudad" class="block text-sm font-medium text-slate-600 mb-1">Ciudad</label>
                    <select name="ciudad" id="select-ciudad" disabled class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border bg-slate-50 disabled:opacity-50">
                        <option value="">Primero elige un estado</option>
                    </select>
                </div>

                <!-- Fuente -->
                <div>
                    <label for="fuente" class="block text-sm font-medium text-slate-600 mb-1">Fuente (Origen)</label>
                    <input
                    type="text"
                    name="fuente"
                    id="fuente"
                    value="{{ old('fuente') }}"
                    placeholder="Ej. Facebook, Referido"
                    class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500"
                    >
                </div>

                <!-- Tipo de Cliente -->
                <div>
                    <label for="id_tipo_cliente" class="block text-sm font-medium text-slate-600 mb-1">Tipo de Cliente *</label>
                    <select name="id_tipo_cliente" id="id_tipo_cliente" required class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500 bg-white">
                        <option value="1" {{ old('id_tipo_cliente', 1) == 1 ? 'selected' : '' }}>B2B (Empresa)</option>
                        <option value="2" {{ old('id_tipo_cliente', 1) == 2 ? 'selected' : '' }}>B2C (Consumidor final)</option>
                        <option value="3" {{ old('id_tipo_cliente', 1) == 3 ? 'selected' : '' }}>Socio Comercial</option>
                    </select>
                </div>

                <!-- Estado Lead -->
                <div>
                    <label for="id_estado_lead" class="block text-sm font-medium text-slate-600 mb-1">Estado Lead *</label>
                    <select name="id_estado_lead" id="id_estado_lead" required class="w-full rounded-lg border-slate-200 shadow-sm p-2.5 border focus:ring-purple-500 focus:border-purple-500 bg-white">
                        <option value="1" {{ old('id_estado_lead', 1) == 1 ? 'selected' : '' }}>Nuevo</option>
                        <option value="2" {{ old('id_estado_lead', 1) == 2 ? 'selected' : '' }}>Contactado</option>
                        <option value="3" {{ old('id_estado_lead', 1) == 3 ? 'selected' : '' }}>Interesado</option>
                        <option value="4" {{ old('id_estado_lead', 1) == 4 ? 'selected' : '' }}>Cliente</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('clientes.index') }}" class="bg-slate-100 text-slate-600 px-5 py-2.5 rounded-lg font-medium hover:bg-slate-200 transition-colors">Cancelar</a>
            <button type="submit" class="bg-[#7c3aed] text-white px-5 py-2.5 rounded-lg font-medium hover:bg-[#6d28d9] transition-colors shadow-sm">Actualizar Cliente</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectPais = document.getElementById('select-pais');
    const selectEstado = document.getElementById('select-estado');
    const selectCiudad = document.getElementById('select-ciudad');

    // 1. Cargar Paises
    fetch('https://countriesnow.space/api/v0.1/countries')
        .then(response => response.json())
        .then(data => {
            data.data.forEach(pais => {
                let option = document.createElement('option');
                option.value = pais.country;
                option.textContent = pais.country;
                selectPais.appendChild(option);
            });
        });

    // 2. Cargar Estados cuando se elige un País
    selectPais.addEventListener('change', function() {
        selectEstado.innerHTML = '<option value="">Cargando estados...</option>';
        selectCiudad.innerHTML = '<option value="">Primero elige un estado</option>';
        selectEstado.disabled = true;
        selectCiudad.disabled = true;

        if(this.value) {
            fetch('https://countriesnow.space/api/v0.1/countries/states', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: this.value })
            })
            .then(res => res.json())
            .then(data => {
                selectEstado.innerHTML = '<option value="">Selecciona un estado...</option>';
                data.data.states.forEach(estado => {
                    let option = document.createElement('option');
                    option.value = estado.name;
                    option.textContent = estado.name;
                    selectEstado.appendChild(option);
                });
                selectEstado.disabled = false;
            });
        }
    });

    // 3. Cargar Ciudades cuando se elige un Estado
    selectEstado.addEventListener('change', function() {
        selectCiudad.innerHTML = '<option value="">Cargando ciudades...</option>';
        selectCiudad.disabled = true;

        if(this.value) {
            fetch('https://countriesnow.space/api/v0.1/countries/state/cities', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: selectPais.value, state: this.value })
            })
            .then(res => res.json())
            .then(data => {
                selectCiudad.innerHTML = '<option value="">Selecciona una ciudad...</option>';
                data.data.forEach(ciudad => {
                    let option = document.createElement('option');
                    option.value = ciudad;
                    option.textContent = ciudad;
                    selectCiudad.appendChild(option);
                });
                selectCiudad.disabled = false;
            });
        }
    });
});
</script>
@endsection