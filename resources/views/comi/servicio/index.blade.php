@extends('layouts.app')

@section('title', 'Servicio COMI - COMICenter')

@section('content')

<style>

    .cs-page {
        padding: 28px;
        color: #e2e8f0;
    }

    .cs-title {
        margin: 0;
        color: #f8fafc;
        font-size: 28px;
    }

    .cs-description {
        margin: 7px 0 24px;
        color: #94a3b8;
    }

    .cs-filter {
        margin-bottom: 20px;
        padding: 16px;
        background: #131e30;
        border: 1px solid #2b3a50;
        border-radius: 12px;
    }

    .cs-filter form {
        display: flex;
        gap: 10px;
        align-items: end;
        flex-wrap: wrap;
    }

    .cs-select {
        min-width: 230px;
        height: 40px;
        padding: 0 12px;
        background: #0f172a;
        color: #e2e8f0;
        border: 1px solid #334155;
        border-radius: 8px;
    }

    .cs-button {
        display: inline-flex;
        min-height: 38px;
        padding: 0 13px;
        align-items: center;
        justify-content: center;
        background: #0284c7;
        color: white;
        border: 1px solid #0284c7;
        border-radius: 8px;
        text-decoration: none;
        cursor: pointer;
        font-weight: 650;
    }

    .cs-card {
        overflow: hidden;
        background: #131e30;
        border: 1px solid #2b3a50;
        border-radius: 12px;
    }

    .cs-table-wrapper {
        overflow-x: auto;
    }

    .cs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cs-table th {
        padding: 13px 15px;
        background: #172235;
        color: #94a3b8;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
    }

    .cs-table td {
        padding: 15px;
        border-top: 1px solid #243247;
        font-size: 13px;
        vertical-align: top;
    }

    .cs-primary {
        color: #f8fafc;
        font-weight: 650;
    }

    .cs-secondary {
        display: block;
        margin-top: 4px;
        color: #94a3b8;
    }

    .cs-status {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 999px;
        background: #1e293b;
        font-size: 11px;
        font-weight: 700;
    }

    .cs-alert {
        margin-bottom: 18px;
        padding: 13px;
        border-radius: 8px;
    }

    .cs-success {
        color: #6ee7b7;
        background: rgba(16, 185, 129, .12);
    }

    .cs-error {
        color: #fca5a5;
        background: rgba(239, 68, 68, .12);
    }

    .cs-empty {
        padding: 45px;
        text-align: center;
        color: #94a3b8;
    }

</style>


<section class="cs-page">

    <h1 class="cs-title">
        Solicitudes de servicio COMI
    </h1>

    <p class="cs-description">
        Revisa las solicitudes técnicas generadas por COMI
        y programa las visitas correspondientes.
    </p>


    @if(session('success'))

        <div class="cs-alert cs-success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="cs-alert cs-error">
            {{ session('error') }}
        </div>

    @endif


    <div class="cs-filter">

        <form method="GET">

            <div>

                <label
                    style="
                        display:block;
                        margin-bottom:6px;
                        color:#94a3b8;
                    "
                >
                    Estado
                </label>

                <select
                    name="estado"
                    class="cs-select"
                >

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="pendiente_revision"
                        @selected(
                            request('estado')
                            === 'pendiente_revision'
                        )
                    >
                        Pendiente de revisión
                    </option>

                    <option
                        value="requiere_contacto"
                        @selected(
                            request('estado')
                            === 'requiere_contacto'
                        )
                    >
                        Requiere contacto
                    </option>

                    <option
                        value="cita_programada"
                        @selected(
                            request('estado')
                            === 'cita_programada'
                        )
                    >
                        Cita programada
                    </option>

                    <option
                        value="rechazada"
                        @selected(
                            request('estado')
                            === 'rechazada'
                        )
                    >
                        Rechazada
                    </option>

                    <option
                        value="cancelada"
                        @selected(
                            request('estado')
                            === 'cancelada'
                        )
                    >
                        Cancelada
                    </option>

                </select>

            </div>


            <button
                class="cs-button"
                type="submit"
            >
                Filtrar
            </button>

        </form>

    </div>


    <div class="cs-card">

        @if($solicitudes->isEmpty())

            <div class="cs-empty">
                No existen solicitudes de servicio para mostrar.
            </div>

        @else

            <div class="cs-table-wrapper">

                <table class="cs-table">

                    <thead>

                        <tr>
                            <th>Contacto</th>
                            <th>Equipo</th>
                            <th>Problema</th>
                            <th>Horario preferido</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach(
                            $solicitudes
                            as $solicitud
                        )

                            @php

                                $puedeProgramar =
                                    $agendasTecnicasGestionables
                                        ->contains(
                                            'id_proyecto',
                                            $solicitud->id_proyecto
                                        );

                            @endphp


                            <tr>

                                <td>

                                    <span class="cs-primary">
                                        {{
                                            $solicitud
                                                ->nombre_contacto
                                        }}
                                    </span>

                                    <span class="cs-secondary">
                                        {{ $solicitud->telefono }}
                                    </span>

                                </td>


                                <td>
                                    {{ $solicitud->equipo }}
                                </td>


                                <td>
                                    {{
                                        $solicitud
                                            ->descripcion_problema
                                    }}
                                </td>


                                <td>
                                    {{
                                        $solicitud
                                            ->horario_preferido
                                    }}
                                </td>


                                <td>

                                    <span class="cs-status">

                                        {{
                                            match(
                                                $solicitud->estado
                                            ) {
                                                'pendiente_revision'
                                                    => 'Pendiente',

                                                'requiere_contacto'
                                                    => 'Requiere contacto',

                                                'cita_programada'
                                                    => 'Cita programada',

                                                'rechazada'
                                                    => 'Rechazada',

                                                'cancelada'
                                                    => 'Cancelada',

                                                default
                                                    => $solicitud->estado,
                                            }
                                        }}

                                    </span>

                                </td>


                                <td>

                                    @if(
                                        $puedeProgramar
                                        &&
                                        in_array(
                                            $solicitud->estado,
                                            [
                                                'pendiente_revision',
                                                'requiere_contacto',
                                            ],
                                            true
                                        )
                                    )

                                        <a
                                            href="{{
                                                route(
                                                    'comi.servicio.programar.form',
                                                    $solicitud->id_solicitud
                                                )
                                            }}"
                                            class="cs-button"
                                        >
                                            Programar visita
                                        </a>

                                    @else

                                        <span class="cs-secondary">
                                            Sin acciones
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</section>

@endsection
