@extends('comercial.base')

@section('commercial-title', 'Agenda comercial')

@section(
    'commercial-subtitle',
    'Citas recibidas desde COMI y próximos seguimientos comerciales.'
)

@section('commercial-content')

<section class="card">

    <p class="muted">

        Horario:
        {{ config('app.timezone') }}

        ·

        {{ $prospectos->total() }}

        {{
            $prospectos->total() === 1
                ? 'evento programado'
                : 'eventos programados'
        }}

    </p>


    <div class="scroll space">

        <table>

            <thead>

                <tr>

                    <th>
                        Fecha y hora
                    </th>

                    <th>
                        Tipo
                    </th>

                    <th>
                        Prospecto
                    </th>

                    <th>
                        Asesor
                    </th>

                    <th>
                        Estado
                    </th>

                    <th>
                        Acción
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($prospectos as $p)

                    @php

                        $fecha = \Carbon\Carbon::parse(
                            $p->fecha_agenda
                        )->timezone(
                            config('app.timezone')
                        );

                    @endphp


                    <tr>


                        {{-- FECHA --}}

                        <td>

                            <strong>
                                {{ $fecha->format('d/m/Y') }}
                            </strong>

                            <small>
                                {{ $fecha->format('H:i') }}
                            </small>


                            @if($fecha->isPast())

                                <small class="danger">
                                    Vencido
                                </small>

                            @endif

                        </td>


                        {{-- TIPO --}}

                        <td>

                            @if($p->tipo_agenda === 'cita_comi')

                                <span class="badge asignado">
                                    Cita COMI
                                </span>

                            @else

                                <span class="badge en_seguimiento">
                                    Seguimiento
                                </span>

                            @endif

                        </td>


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

                            <small>

                                {{
                                    $p->telefono_principal
                                    ?: 'Sin teléfono'
                                }}

                            </small>

                        </td>


                        {{-- ASESOR --}}

                        <td>

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

                            No hay citas ni seguimientos
                            comerciales programados.

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