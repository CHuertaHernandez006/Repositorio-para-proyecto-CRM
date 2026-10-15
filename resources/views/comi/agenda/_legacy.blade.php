@if($puedeVerLegacy)

    <div class="ca-card" style="margin-bottom: 24px;">

        <div
            style="
                padding: 20px;
                border-bottom: 1px solid #2b3a50;
            "
        >
            <p class="ca-eyebrow">
                COMI / Agenda operativa actual
            </p>

            <h2
                style="
                    margin: 0;
                    color: #f8fafc;
                    font-size: 20px;
                "
            >
                Citas actuales de COMI
            </h2>

            <p
                style="
                    margin: 7px 0 0;
                    color: #94a3b8;
                    font-size: 13px;
                "
            >
                Citas que actualmente está registrando COMI en producción.
            </p>
        </div>


        <div
            class="ca-summary"
            style="
                margin: 0;
                padding: 18px;
            "
        >

            <div class="ca-summary-card">
                <span class="ca-summary-label">
                    Total
                </span>

                <span class="ca-summary-value">
                    {{ $resumenLegacy['total'] }}
                </span>
            </div>


            <div class="ca-summary-card">
                <span class="ca-summary-label">
                    Pendientes
                </span>

                <span class="ca-summary-value">
                    {{ $resumenLegacy['pendientes'] }}
                </span>
            </div>


            <div class="ca-summary-card">
                <span class="ca-summary-label">
                    Confirmadas
                </span>

                <span class="ca-summary-value">
                    {{ $resumenLegacy['confirmadas'] }}
                </span>
            </div>


            <div class="ca-summary-card">
                <span class="ca-summary-label">
                    Rechazadas
                </span>

                <span class="ca-summary-value">
                    {{ $resumenLegacy['rechazadas'] }}
                </span>
            </div>

        </div>


        <div
            style="
                padding: 0 18px 18px;
            "
        >
            <form
                method="GET"
                action="{{ route('comi.agenda.index') }}"
                style="
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                    align-items: end;
                "
            >

                <div class="ca-field">

                    <label for="legacy_estado">
                        Estado
                    </label>

                    <select
                        name="legacy_estado"
                        id="legacy_estado"
                        class="ca-select"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option
                            value="pendiente_aprobacion"
                            @selected(
                                request('legacy_estado')
                                === 'pendiente_aprobacion'
                            )
                        >
                            Pendiente
                        </option>

                        <option
                            value="confirmada"
                            @selected(
                                request('legacy_estado')
                                === 'confirmada'
                            )
                        >
                            Confirmada
                        </option>

                        <option
                            value="rechazada"
                            @selected(
                                request('legacy_estado')
                                === 'rechazada'
                            )
                        >
                            Rechazada
                        </option>

                        <option
                            value="reprogramada"
                            @selected(
                                request('legacy_estado')
                                === 'reprogramada'
                            )
                        >
                            Reprogramada
                        </option>
                    </select>

                </div>


                <div class="ca-field">

                    <label for="legacy_fecha">
                        Fecha
                    </label>

                    <input
                        type="date"
                        name="legacy_fecha"
                        id="legacy_fecha"
                        value="{{ request('legacy_fecha') }}"
                        class="ca-input"
                    >

                </div>


                <button
                    type="submit"
                    class="ca-button ca-button-primary"
                >
                    Filtrar
                </button>


                <a
                    href="{{ route('comi.agenda.index') }}"
                    class="ca-button ca-button-secondary"
                >
                    Limpiar
                </a>

            </form>
        </div>


        @if(
            !$citasLegacy
            || $citasLegacy->isEmpty()
        )

            <div class="ca-empty">
                <h2>
                    No hay citas registradas
                </h2>

                <p>
                    No existen citas que coincidan con los filtros seleccionados.
                </p>
            </div>

        @else

            <div class="ca-table-wrapper">

                <table class="ca-table">

                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Contacto</th>
                            <th>Empresa</th>
                            <th>Objetivo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>


                    <tbody>

                        @foreach($citasLegacy as $cita)

                            @php

                                $estadoTexto = match(
                                    $cita->estado
                                ) {
                                    'pendiente_aprobacion'
                                        => 'Pendiente',

                                    'confirmada'
                                        => 'Confirmada',

                                    'rechazada'
                                        => 'Rechazada',

                                    'reprogramada'
                                        => 'Reprogramada',

                                    default
                                        => $cita->estado,
                                };


                                $estadoClase = match(
                                    $cita->estado
                                ) {
                                    'pendiente_aprobacion'
                                        => 'ca-status-pendiente',

                                    'confirmada'
                                        => 'ca-status-confirmada',

                                    'rechazada'
                                        => 'ca-status-rechazada',

                                    'reprogramada'
                                        => 'ca-status-reprogramada',

                                    default
                                        => '',
                                };

                            @endphp


                            <tr>

                                <td>

                                    <span class="ca-primary">
                                        {{
                                            \Carbon\Carbon::parse(
                                                $cita->fecha_hora_cita
                                            )
                                            ->timezone(
                                                'America/Mexico_City'
                                            )
                                            ->format('d/m/Y')
                                        }}
                                    </span>

                                    <span class="ca-secondary">
                                        {{
                                            \Carbon\Carbon::parse(
                                                $cita->fecha_hora_cita
                                            )
                                            ->timezone(
                                                'America/Mexico_City'
                                            )
                                            ->format('H:i')
                                        }}
                                    </span>

                                </td>


                                <td>

                                    <span class="ca-primary">
                                        {{ $cita->nombre_contacto }}
                                    </span>

                                    <span class="ca-secondary">
                                        {{ $cita->telefono }}
                                    </span>

                                    @if($cita->correo)

                                        <span class="ca-secondary">
                                            {{ $cita->correo }}
                                        </span>

                                    @endif

                                </td>


                                <td>
                                    {{
                                        $cita->nombre_empresa
                                        ?: 'Sin empresa'
                                    }}
                                </td>


                                <td>
                                    {{
                                        $cita->objetivo_reunion
                                        ?: 'Sin objetivo'
                                    }}
                                </td>


                                <td>

                                    <span
                                        class="
                                            ca-status
                                            {{ $estadoClase }}
                                        "
                                    >
                                        {{ $estadoTexto }}
                                    </span>

                                </td>


                                <td>

                                    @if(
                                        $cita->estado
                                        === 'pendiente_aprobacion'
                                    )

                                        <div class="ca-actions">

                                            <form
                                                method="POST"
                                                action="{{
                                                    route(
                                                        'comi.agenda.legacy.estado',
                                                        $cita->id
                                                    )
                                                }}"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <input
                                                    type="hidden"
                                                    name="estado"
                                                    value="confirmada"
                                                >

                                                <button
                                                    type="submit"
                                                    class="
                                                        ca-button
                                                        ca-button-confirm
                                                    "
                                                    onclick="
                                                        return confirm(
                                                            '¿Confirmar esta cita?'
                                                        );
                                                    "
                                                >
                                                    Confirmar
                                                </button>
                                            </form>


                                            <form
                                                method="POST"
                                                action="{{
                                                    route(
                                                        'comi.agenda.legacy.estado',
                                                        $cita->id
                                                    )
                                                }}"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <input
                                                    type="hidden"
                                                    name="estado"
                                                    value="rechazada"
                                                >

                                                <button
                                                    type="submit"
                                                    class="
                                                        ca-button
                                                        ca-button-reject
                                                    "
                                                    onclick="
                                                        return confirm(
                                                            '¿Rechazar esta cita?'
                                                        );
                                                    "
                                                >
                                                    Rechazar
                                                </button>
                                            </form>


                                            <a
                                                href="{{
                                                    route(
                                                        'comi.agenda.legacy.reprogramar.form',
                                                        $cita->id
                                                    )
                                                }}"
                                                class="
                                                    ca-button
                                                    ca-button-reprogram
                                                "
                                            >
                                                Reprogramar
                                            </a>

                                        </div>

                                    @else

                                        <span class="ca-secondary">
                                            Sin acciones pendientes
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div class="ca-pagination">

                <span>
                    Página
                    {{ $citasLegacy->currentPage() }}
                    de
                    {{ $citasLegacy->lastPage() }}
                    ·
                    {{ $citasLegacy->total() }}
                    citas
                </span>


                <div>

                    @if($citasLegacy->previousPageUrl())

                        <a
                            href="{{ $citasLegacy->previousPageUrl() }}"
                            class="ca-page-link"
                        >
                            Anterior
                        </a>

                    @endif


                    @if($citasLegacy->nextPageUrl())

                        <a
                            href="{{ $citasLegacy->nextPageUrl() }}"
                            class="ca-page-link"
                        >
                            Siguiente
                        </a>

                    @endif

                </div>

            </div>

        @endif

    </div>

@endif
