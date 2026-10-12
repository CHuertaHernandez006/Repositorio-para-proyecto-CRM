@extends('comercial.base')

@section(
    'commercial-title',
    'Agenda comercial'
)

@section(
    'commercial-subtitle',
    'Consulta las citas generadas por COMI y organiza la atención del equipo comercial.'
)


@push('styles')

<style>

.cm .agenda-summary{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:16px;
    margin-bottom:20px;
}

.cm .agenda-summary-card{
    padding:20px 22px;
    background:#1e293b;
    border:1px solid #334155;
    border-radius:14px;
}

.cm .agenda-summary-card strong{
    display:block;
    margin-bottom:10px;
    font-size:30px;
    line-height:1;
    color:#7dd3fc;
    font-variant-numeric:tabular-nums;
}

.cm .agenda-summary-card span{
    color:#a8bbd2;
    font-size:13px;
}


.cm .agenda-next{
    display:grid;
    grid-template-columns:minmax(0,1fr) auto;
    align-items:center;
    gap:22px;

    margin-bottom:20px;
    padding:22px 24px;

    border:1px solid #2f6b5e;
    border-left:4px solid #34d399;
    border-radius:14px;

    background:
        linear-gradient(
            120deg,
            rgba(52,211,153,.10),
            rgba(30,41,59,.98)
        );
}

.cm .agenda-next-label{
    margin:0 0 7px;
    color:#6ee7b7;
    font-size:10px;
    font-weight:700;
    letter-spacing:.12em;
    text-transform:uppercase;
}

.cm .agenda-next h2{
    margin:0;
    color:#f1f5f9;
    font-size:20px;
}

.cm .agenda-next p{
    margin:7px 0 0;
    color:#b6c8df;
    font-size:13px;
}

.cm .agenda-heading{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:20px;
}

.cm .agenda-heading-icon{
    width:42px;
    height:42px;
    display:grid;
    place-items:center;

    border:1px solid #31516a;
    border-radius:10px;

    background:#17364a;
    color:#7dd3fc;

    font-size:20px;
}

.cm .agenda-heading h2{
    margin:0;
    font-size:18px;
}

.cm .agenda-heading p{
    margin:4px 0 0;
}


.cm .agenda-status{
    display:inline-flex;
    align-items:center;
    padding:5px 9px;

    border-radius:999px;
    border:1px solid #36556c;

    font-size:11px;
    font-weight:600;
    white-space:nowrap;
}

.cm .agenda-status.pendiente_aprobacion{
    color:#fde68a;
    background:#3d3423;
    border-color:#756339;
}

.cm .agenda-status.confirmada{
    color:#6ee7b7;
    background:#123b34;
    border-color:#1f6555;
}

.cm .agenda-status.reprogramada{
    color:#7dd3fc;
    background:#173549;
    border-color:#36556c;
}

.cm .agenda-status.rechazada{
    color:#fda4af;
    background:#38293b;
    border-color:#664153;
}


.cm .agenda-date strong{
    display:block;
    color:#f1f5f9;
}

.cm .agenda-date small{
    display:block;
    margin-top:5px;
}

.cm .agenda-vencida{
    color:#fda4af !important;
}


.cm .agenda-advisor-form{
    min-width:175px;
}

.cm .agenda-advisor-form select{
    min-width:170px;
}


@media(max-width:850px){

    .cm .agenda-summary{
        grid-template-columns:1fr;
    }

    .cm .agenda-next{
        grid-template-columns:1fr;
    }

}

</style>

@endpush


@section('commercial-content')


{{-- ============================================================
    RESUMEN
============================================================ --}}

<div
    class="agenda-summary"
    aria-label="Resumen de la agenda comercial"
>

    <div class="agenda-summary-card">

        <strong>
            {{ $resumen['hoy'] }}
        </strong>

        <span>
            Citas programadas para hoy
        </span>

    </div>


    <div class="agenda-summary-card">

        <strong>
            {{ $resumen['pendientes'] }}
        </strong>

        <span>
            Pendientes de aprobación
        </span>

    </div>


    <div class="agenda-summary-card">

        <strong>
            {{ $resumen['proximas'] }}
        </strong>

        <span>
            Próximas citas
        </span>

    </div>

</div>



{{-- ============================================================
    PRÓXIMA CITA
============================================================ --}}

@if($proximaCita)

    @php

        $fechaProxima =
            \Carbon\Carbon::parse(
                $proximaCita->fecha_hora_cita
            )
            ->timezone(
                $zonaAgenda
            );

        $nombreProxima =
            $proximaCita->organizacion
            ?: trim(
                $proximaCita->nombre
                . ' '
                . $proximaCita->apellido_paterno
            );

    @endphp


    <div class="agenda-next">

        <div>

            <p class="agenda-next-label">
                Próxima cita
            </p>

            <h2>
                {{ $nombreProxima ?: $proximaCita->nombre_contacto }}
            </h2>

            <p>

                <strong>
                    {{ $fechaProxima->format('d/m/Y H:i') }}
                </strong>

                @if($proximaCita->objetivo_reunion)

                    ·
                    {{ $proximaCita->objetivo_reunion }}

                @endif

            </p>


            <p>

                Asesor:

                <strong>

                    @if($proximaCita->asesor_nombre)

                        {{
                            trim(
                                $proximaCita->asesor_nombre
                                . ' '
                                . $proximaCita->asesor_apellido
                            )
                        }}

                    @else

                        Sin asignar

                    @endif

                </strong>

            </p>

        </div>


        <a
            class="btn primary"
            href="{{
                route(
                    'comercial.prospectos.show',
                    $proximaCita->prospecto_id
                )
            }}"
        >
            Ver prospecto
        </a>

    </div>

@endif



{{-- ============================================================
    AGENDA
============================================================ --}}

<section class="card">


    <div class="agenda-heading">

        <div class="agenda-heading-icon">
            ◫
        </div>

        <div>

            <h2>
                Agenda de citas COMI
            </h2>

            <p class="muted">
                Reuniones agendadas por COMI, ordenadas por fecha y hora.
            </p>

        </div>

    </div>



    {{-- ========================================================
        FILTROS
    ========================================================= --}}

    <form
        method="GET"
        action="{{ route('comercial.agenda') }}"
        class="toolbar"
    >


        <div class="field">

            <label for="buscar">
                Buscar
            </label>

            <input
                id="buscar"
                type="search"
                name="buscar"
                value="{{ $buscar }}"
                maxlength="100"
                placeholder="Prospecto, empresa, correo, teléfono u objetivo"
            >

        </div>



        <div class="field">

            <label for="estado_cita">
                Estado de cita
            </label>

            <select
                id="estado_cita"
                name="estado_cita"
            >

                <option value="">
                    Todos los estados
                </option>

                <option
                    value="pendiente_aprobacion"
                    @selected(
                        $estadoCita ===
                        'pendiente_aprobacion'
                    )
                >
                    Pendiente de aprobación
                </option>

                <option
                    value="confirmada"
                    @selected(
                        $estadoCita ===
                        'confirmada'
                    )
                >
                    Confirmada
                </option>

                <option
                    value="reprogramada"
                    @selected(
                        $estadoCita ===
                        'reprogramada'
                    )
                >
                    Reprogramada
                </option>

                <option
                    value="rechazada"
                    @selected(
                        $estadoCita ===
                        'rechazada'
                    )
                >
                    Rechazada
                </option>

            </select>

        </div>



        @if((int) Auth::user()->id_rol === 1)

            <div class="field">

                <label for="asesor">
                    Asesor
                </label>

                <select
                    id="asesor"
                    name="asesor"
                >

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="sin_asignar"
                        @selected(
                            $asesor ===
                            'sin_asignar'
                        )
                    >
                        Sin asignar
                    </option>


                    @foreach($asesores as $a)

                        <option
                            value="{{ $a->id_usuario }}"
                            @selected(
                                $asesor ===
                                (string) $a->id_usuario
                            )
                        >

                            {{
                                trim(
                                    $a->nombre
                                    . ' '
                                    . $a->apellido_paterno
                                )
                            }}

                        </option>

                    @endforeach

                </select>

            </div>

        @endif



        <button
            type="submit"
            class="btn primary"
        >
            Buscar
        </button>


        @if(
            $buscar !== ''
            ||
            $estadoCita !== ''
            ||
            $asesor !== ''
        )

            <a
                href="{{ route('comercial.agenda') }}"
                class="btn"
            >
                Limpiar
            </a>

        @endif

    </form>



    <p
        class="muted space"
        role="status"
    >

        <strong>
            {{ $citas->total() }}
        </strong>

        {{
            $citas->total() === 1
                ? 'cita encontrada'
                : 'citas encontradas'
        }}

        · Horario {{ $zonaAgenda }}

    </p>



    {{-- ========================================================
        TABLA
    ========================================================= --}}

    <div class="scroll space">

        <table>

            <thead>

                <tr>

                    <th>
                        Fecha y hora
                    </th>

                    <th>
                        Prospecto
                    </th>

                    <th>
                        Objetivo
                    </th>

                    <th>
                        Estado
                    </th>

                    <th>
                        Asesor
                    </th>

                    <th>
                        Acción
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($citas as $cita)

                    @php

                        $fecha =
                            \Carbon\Carbon::parse(
                                $cita->fecha_hora_cita
                            )
                            ->timezone(
                                $zonaAgenda
                            );

                        $nombreProspecto =
                            $cita->organizacion
                            ?: trim(
                                $cita->nombre
                                . ' '
                                . $cita->apellido_paterno
                            );

                        $estadoEtiqueta = match(
                            $cita->estado_cita
                        ) {
                            'pendiente_aprobacion'
                                => 'Pendiente',

                            'confirmada'
                                => 'Confirmada',

                            'reprogramada'
                                => 'Reprogramada',

                            'rechazada'
                                => 'Rechazada',

                            default
                                => ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $cita->estado_cita
                                    )
                                ),
                        };

                    @endphp


                    <tr>


                        {{-- FECHA --}}

                        <td class="agenda-date">

                            <strong>
                                {{ $fecha->format('d/m/Y') }}
                            </strong>

                            <small>
                                {{ $fecha->format('H:i') }}
                            </small>


                            @if(
                                $fecha->isPast()
                                &&
                                !in_array(
                                    $cita->estado_cita,
                                    [
                                        'rechazada'
                                    ],
                                    true
                                )
                            )

                                <small class="agenda-vencida">
                                    Horario vencido
                                </small>

                            @endif

                        </td>



                        {{-- PROSPECTO --}}

                        <td>

                            <strong>

                                {{
                                    $nombreProspecto
                                    ?: $cita->nombre_contacto
                                }}

                            </strong>

                            <small>

                                {{
                                    $cita->telefono_principal
                                    ?: $cita->telefono
                                    ?: 'Sin teléfono'
                                }}

                            </small>

                        </td>



                        {{-- OBJETIVO --}}

                        <td>

                            {{
                                $cita->objetivo_reunion
                                ?: 'Sin objetivo especificado'
                            }}

                            @if($cita->notas)

                                <small>
                                    {{ $cita->notas }}
                                </small>

                            @endif

                        </td>



                        {{-- ESTADO --}}

                        <td>

                            <span
                                class="agenda-status {{ $cita->estado_cita }}"
                            >
                                {{ $estadoEtiqueta }}
                            </span>

                        </td>



                        {{-- ASESOR --}}

                        <td>

                            @if(
                                (int) Auth::user()->id_rol === 1
                                &&
                                !in_array(
                                    $cita->estado_comercial,
                                    [
                                        'contratado',
                                        'no_interesado'
                                    ],
                                    true
                                )
                            )

                                <form
                                    class="agenda-advisor-form"
                                    method="POST"
                                    action="{{
                                        route(
                                            'comercial.asignar',
                                            $cita->prospecto_id
                                        )
                                    }}"
                                >

                                    @csrf

                                    @method('PUT')


                                    <select
                                        name="id_asesor"
                                        onchange="
                                            if(this.value){
                                                this.form.submit();
                                            }
                                        "
                                    >

                                        <option
                                            value=""
                                            disabled
                                            @selected(
                                                !$cita->id_asesor
                                            )
                                        >
                                            Sin asignar
                                        </option>


                                        @foreach($asesores as $a)

                                            <option
                                                value="{{ $a->id_usuario }}"
                                                @selected(
                                                    (int) $cita->id_asesor
                                                    ===
                                                    (int) $a->id_usuario
                                                )
                                            >

                                                {{
                                                    trim(
                                                        $a->nombre
                                                        . ' '
                                                        . $a->apellido_paterno
                                                    )
                                                }}

                                            </option>

                                        @endforeach

                                    </select>

                                </form>

                            @else

                                @if($cita->asesor_nombre)

                                    {{
                                        trim(
                                            $cita->asesor_nombre
                                            . ' '
                                            . $cita->asesor_apellido
                                        )
                                    }}

                                @else

                                    <span class="muted">
                                        Sin asignar
                                    </span>

                                @endif

                            @endif

                        </td>



                        {{-- ACCIÓN --}}

                        <td>

                            <a
                                href="{{
                                    route(
                                        'comercial.prospectos.show',
                                        $cita->prospecto_id
                                    )
                                }}"
                            >
                                Abrir prospecto →
                            </a>

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="empty"
                        >

                            @if(
                                $buscar !== ''
                                ||
                                $estadoCita !== ''
                                ||
                                $asesor !== ''
                            )

                                No hay citas que coincidan
                                con los filtros seleccionados.

                            @else

                                COMI todavía no tiene citas
                                registradas para estos prospectos.

                            @endif

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    @include(
        'comercial.paginacion',
        [
            'pagina' => $citas
        ]
    )

</section>

@endsection