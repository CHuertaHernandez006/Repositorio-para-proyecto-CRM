@extends('comercial.base')

@section(
    'commercial-title',
    (int) Auth::user()->id_rol === 1
        ? 'Prospectos COMI'
        : 'Mis prospectos'
)

@section(
    'commercial-subtitle',
    (int) Auth::user()->id_rol === 1
        ? 'Consulta, asigna y da seguimiento a los prospectos detectados por COMI.'
        : 'Consulta y da seguimiento a tus prospectos asignados.'
)

@section('commercial-content')


{{-- ============================================================
    MÉTRICAS
============================================================ --}}

<div class="metrics">

    <div class="card metric">

        <span class="muted">
            Total de prospectos
        </span>

        <strong>
            {{ $resumen['total'] }}
        </strong>

    </div>


    <div class="card metric">

        <span class="muted">
            Pendientes de atención
        </span>

        <strong>
            {{ $resumen['pendientes'] }}
        </strong>

    </div>


    <div class="card metric">

        <span class="muted">
            Cotizaciones enviadas
        </span>

        <strong>
            {{ $resumen['cotizaciones'] }}
        </strong>

    </div>

</div>



{{-- ============================================================
    LISTADO
============================================================ --}}

<section class="card">


    {{-- ========================================================
        FILTROS
    ========================================================= --}}

    <form
        method="GET"
        action="{{ route('comercial.prospectos.index') }}"
        class="toolbar"
    >


        <div class="field">

            <label for="buscar">
                Buscar prospecto
            </label>

            <input
                id="buscar"
                name="buscar"
                type="search"
                maxlength="100"
                value="{{ $buscar }}"
                placeholder="Nombre, empresa, correo o teléfono"
            >

        </div>



        <div class="field">

            <label for="estado">
                Estado
            </label>

            <select
                id="estado"
                name="estado"
            >

                <option value="">
                    Todos los estados
                </option>

                @foreach(
                    \App\Support\Comercial::ESTADOS
                    as $valor => $etiqueta
                )

                    <option
                        value="{{ $valor }}"
                        @selected(
                            $estado === $valor
                        )
                    >
                        {{ $etiqueta }}
                    </option>

                @endforeach

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
                        Todos los asesores
                    </option>

                    <option
                        value="sin_asignar"
                        @selected(
                            $asesor === 'sin_asignar'
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



        <div class="field">

            <label for="cita">
                Cita
            </label>

            <select id="cita" name="cita">
                @foreach(['' => 'Todas', 'programada' => 'Confirmada futura', 'solicitada' => 'Pendiente / solicitada', 'con_registro' => 'Con cita registrada', 'historica' => 'Con horario pasado', 'sin_cita' => 'Sin cita ni solicitud'] as $valor => $texto)
                    <option value="{{ $valor }}" @selected($cita === $valor)>{{ $texto }}</option>
                @endforeach
            </select>

        </div>



        <div class="field">

            <label for="desde">
                Desde
            </label>

            <input
                id="desde"
                name="desde"
                type="date"
                value="{{ $desde }}"
            >

        </div>



        <div class="field">

            <label for="hasta">
                Hasta
            </label>

            <input
                id="hasta"
                name="hasta"
                type="date"
                value="{{ $hasta }}"
            >

        </div>



        <button
            class="btn primary"
            type="submit"
        >
            Filtrar
        </button>


        @if(
            $buscar !== ''
            ||
            $estado !== ''
            ||
            $asesor !== ''
            ||
            $cita !== ''
            ||
            $desde !== ''
            ||
            $hasta !== ''
        )

            <a
                class="btn"
                href="{{ route('comercial.prospectos.index') }}"
            >
                Limpiar
            </a>

        @endif

    </form>



    {{-- ========================================================
        RESULTADOS
    ========================================================= --}}

    <p
        class="muted space"
        role="status"
    >

        <strong>
            {{ $prospectos->total() }}
        </strong>

        {{
            $prospectos->total() === 1
                ? 'prospecto encontrado'
                : 'prospectos encontrados'
        }}

    </p>



    {{-- ========================================================
        TABLA
    ========================================================= --}}

    <div class="scroll space">

        <table>

            <thead>

                <tr>

                    <th>
                        Prospecto
                    </th>

                    <th>
                        Contacto
                    </th>

                    <th>
                        Llegó
                    </th>

                    <th>
                        Cita
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

                @forelse($prospectos as $p)

                    <tr>


                        {{-- PROSPECTO --}}

                        <td>

                            <strong>

                                {{
                                    $p->organizacion
                                    ?: trim(
                                        $p->nombre
                                        . ' '
                                        . $p->apellido_paterno
                                    )
                                }}

                            </strong>


                            @if($p->organizacion)

                                <small>

                                    {{
                                        trim(
                                            $p->nombre
                                            . ' '
                                            . $p->apellido_paterno
                                        )
                                    }}

                                </small>

                            @endif

                        </td>



                        {{-- CONTACTO --}}

                        <td>

                            {{
                                $p->telefono_principal
                                ?: 'Sin teléfono'
                            }}

                            <small>

                                {{
                                    $p->correo
                                    ?: 'Sin correo'
                                }}

                            </small>

                        </td>



                        {{-- FECHA DE LLEGADA --}}

                        <td>

                            @if($p->created_at)

                                <strong>

                                    {{
                                        \Carbon\Carbon::parse(
                                            $p->created_at
                                        )
                                        ->timezone(
                                            config('app.timezone')
                                        )
                                        ->format('d/m/Y')
                                    }}

                                </strong>

                                <small>

                                    {{
                                        \Carbon\Carbon::parse(
                                            $p->created_at
                                        )
                                        ->timezone(
                                            config('app.timezone')
                                        )
                                        ->format('H:i')
                                    }}

                                </small>

                            @else

                                <span class="muted">
                                    —
                                </span>

                            @endif

                        </td>



                        {{-- CITA --}}

                        <td>

                            @include('comercial.cita-prospecto', ['p' => $p])

                        </td>



                        {{-- ESTADO --}}

                        <td>

                            <span
                                class="badge {{ $p->estado }}"
                            >

                                {{
                                    \App\Support\Comercial::ESTADOS[
                                        $p->estado
                                    ]
                                }}

                            </span>

                        </td>



                        {{-- ASESOR --}}

                        <td>

                            @if(
                                (int) Auth::user()->id_rol === 1
                                &&
                                !in_array(
                                    $p->estado,
                                    [
                                        'contratado',
                                        'no_interesado'
                                    ],
                                    true
                                )
                            )

                                @php

                                    $asesorActualEnLista =
                                        $p->id_asesor
                                        &&
                                        $asesores->contains(
                                            'id_usuario',
                                            $p->id_asesor
                                        );

                                @endphp


                                <form
                                    method="POST"
                                    action="{{
                                        route(
                                            'comercial.asignar',
                                            $p->id
                                        )
                                    }}"
                                >

                                    @csrf

                                    @method('PUT')


                                    <select
                                        name="id_asesor"
                                        onchange="this.form.submit()"
                                    >

                                        <option
                                            value=""
                                            disabled
                                            @selected(
                                                !$p->id_asesor
                                            )
                                        >
                                            Sin asignar
                                        </option>


                                        @if(
                                            $p->id_asesor
                                            &&
                                            !$asesorActualEnLista
                                        )

                                            <option
                                                value="{{ $p->id_asesor }}"
                                                selected
                                            >

                                                {{
                                                    trim(
                                                        $p->asesor_nombre
                                                        . ' '
                                                        . $p->asesor_apellido
                                                    )
                                                }}
                                                (inactivo)

                                            </option>

                                        @endif


                                        @foreach($asesores as $a)

                                            <option
                                                value="{{ $a->id_usuario }}"
                                                @selected(
                                                    (int) $p->id_asesor
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

                                @if($p->asesor_nombre)

                                    {{
                                        trim(
                                            $p->asesor_nombre
                                            . ' '
                                            . $p->asesor_apellido
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
                                        $p->id
                                    )
                                }}"
                            >
                                Ver seguimiento →
                            </a>

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="empty"
                        >

                            No hay prospectos que coincidan
                            con los filtros seleccionados.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    @include(
        'comercial.paginacion',
        [
            'pagina' => $prospectos
        ]
    )

</section>

@endsection