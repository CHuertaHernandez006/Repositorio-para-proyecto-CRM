@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-4">

    {{-- Encabezado --}}
    <div class="mb-4">

        <h1 class="text-white fw-bold mb-1">
            <i class="bi bi-calendar-check me-2"></i>
            Mis citas
        </h1>

        <p class="text-secondary mb-0">
            Consulta las citas que tienes asignadas.
        </p>

    </div>


    {{-- Mensaje de éxito --}}
    @if(session('exito'))

        <div class="alert alert-success border-0 shadow-sm">

            <i class="bi bi-check-circle me-2"></i>

            {{ session('exito') }}

        </div>

    @endif


    {{-- Filtro --}}
    <div class="card shadow-sm border-0 mb-4"
         style="background:#1e293b; border:1px solid #334155 !important;">

        <div class="card-body">

            <form method="GET" action="{{ route('citas.index') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-6">

                        <label class="form-label text-white">
                            Buscar cliente
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-dark border-secondary text-info">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                type="text"
                                name="buscar"
                                value="{{ $buscar }}"
                                class="form-control bg-dark text-white border-secondary"
                                placeholder="Nombre, empresa, correo o teléfono"
                            >

                        </div>

                    </div>


                    <div class="col-12 col-md-3">

                        <label class="form-label text-white">
                            Desde
                        </label>

                        <input
                            type="date"
                            name="fecha_desde"
                            value="{{ request('fecha_desde') }}"
                            class="form-control bg-dark text-white border-secondary"
                        >

                    </div>


                    <div class="col-12 col-md-3">

                        <label class="form-label text-white">
                            Hasta
                        </label>

                        <input
                            type="date"
                            name="fecha_hasta"
                            value="{{ request('fecha_hasta') }}"
                            class="form-control bg-dark text-white border-secondary"
                        >

                    </div>


                    <div class="col-12 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-info fw-semibold"
                        >
                            <i class="bi bi-search me-1"></i>
                            Buscar
                        </button>

                        <a
                            href="{{ route('citas.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Limpiar
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- Citas --}}
    <div class="card shadow-sm border-0"
         style="background:#1e293b; border:1px solid #334155 !important;">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-dark table-hover align-middle mb-0">

                    <thead style="background:#0f172a;">

                        <tr>

                            <th class="px-4 py-3">
                                Cliente
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Horario
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Motivo
                            </th>

                            <th class="text-end px-4">
                                Acción
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($citas as $cita)

                            <tr>

                                {{-- Cliente --}}
                                <td class="px-4">

                                    @if($cita->cliente)

                                        <div class="fw-semibold text-white">

                                            {{ $cita->cliente->nombre }}

                                            {{ $cita->cliente->apellido_paterno }}

                                            {{ $cita->cliente->apellido_materno }}

                                        </div>

                                        @if($cita->cliente->empresa)

                                            <small class="text-secondary">

                                                {{ $cita->cliente->empresa }}

                                            </small>

                                        @endif

                                    @else

                                        <span class="text-secondary">
                                            Cliente no disponible
                                        </span>

                                    @endif

                                </td>


                                {{-- Fecha --}}
                                <td>

                                    {{ $cita->fecha_hora_inicio->format('d/m/Y') }}

                                </td>


                                {{-- Horario --}}
                                <td>

                                    <span class="text-info">

                                        {{ $cita->fecha_hora_inicio->format('H:i') }}

                                        @if($cita->fecha_hora_fin)

                                            -
                                            {{ $cita->fecha_hora_fin->format('H:i') }}

                                        @endif

                                    </span>

                                </td>


                                {{-- Estado --}}
                                <td>

                                    @if($cita->estadoCita)

                                        <span class="badge bg-info text-dark">

                                            {{ $cita->estadoCita->nombre }}

                                        </span>

                                    @else

                                        <span class="text-secondary">
                                            Sin estado
                                        </span>

                                    @endif

                                </td>


                                {{-- Motivo --}}
                                <td>

                                    <span class="text-secondary">

                                        {{ $cita->motivo ?: 'Sin motivo' }}

                                    </span>

                                </td>


                                {{-- Acción --}}
                                <td class="text-end px-4">

                                    <a
                                        href="{{ route('citas.show', $cita->id_cita) }}"
                                        class="btn btn-sm btn-outline-info"
                                    >
                                        <i class="bi bi-eye me-1"></i>
                                        Ver
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="text-center py-5">

                                    <div class="text-secondary">

                                        <i
                                            class="bi bi-calendar-x"
                                            style="font-size:3rem;"
                                        ></i>

                                        <h5 class="text-white mt-3">
                                            No tienes citas registradas
                                        </h5>

                                        <p class="mb-0">
                                            Las citas que te sean asignadas aparecerán aquí.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        @if($citas->hasPages())

            <div class="card-footer border-secondary"
                 style="background:#1e293b;">

                {{ $citas->links() }}

            </div>

        @endif

    </div>

</div>

@endsection