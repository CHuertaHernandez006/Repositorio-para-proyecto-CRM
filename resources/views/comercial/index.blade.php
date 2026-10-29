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

{{-- Actualización silenciosa: métricas, filtros y listado --}}
<div id="panel-comercial">
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
</div>

{{-- Polling silencioso de prospectos COMI (cada 5 segundos) --}}
<script>
(() => {
    'use strict';

    const INTERVALO_MS = 5000;
    const PANEL_ID = 'panel-comercial';
    let solicitudActiva = false;
    let avisoRespuestaInvalida = false;

    // Evitar que el refresco interrumpa filtros, selección o edición.
    function usuarioInteractuando(panel) {
        const activo = document.activeElement;
        if (activo && panel.contains(activo)) {
            if (activo.matches('input, select, textarea, button, [contenteditable=""], [contenteditable="true"]')) {
                return true;
            }
        }

        // No tocar la tabla si el usuario está seleccionando contenido.
        const seleccion = window.getSelection();
        if (seleccion && !seleccion.isCollapsed) {
            return true;
        }

        return false;
    }

    async function actualizarPanel() {
        const panel = document.getElementById(PANEL_ID);
        if (!panel || solicitudActiva || document.hidden || usuarioInteractuando(panel)) {
            return;
        }

        solicitudActiva = true;
        try {
            // Respetar los filtros y la página actual leyendo la URL completa.
            // No mandar X-Requested-With: algunas rutas Laravel devuelven JSON a AJAX.
            const respuesta = await fetch(window.location.href, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'text/html' }
            });

            if (!respuesta.ok || !respuesta.headers.get('content-type')?.includes('text/html')) {
                throw new Error('El servidor no devolvió la vista HTML (HTTP ' + respuesta.status + ')');
            }

            const html = await respuesta.text();
            const documentoNuevo = new DOMParser().parseFromString(html, 'text/html');
            const panelNuevo = documentoNuevo.getElementById(PANEL_ID);
            const panelActual = document.getElementById(PANEL_ID);

            // Si la respuesta fue redirigida al login o es distinta, no tocar la UI.
            if (!panelNuevo || !panelActual) {
                if (!avisoRespuestaInvalida) {
                    console.warn('Auto-refresh comercial: no se encontró el panel en la respuesta. Comprueba la ruta y la autenticación.');
                    avisoRespuestaInvalida = true;
                }
                return;
            }
            avisoRespuestaInvalida = false;

            // Mientras llegó la respuesta pudo empezar a escribir o elegir un asesor.
            if (usuarioInteractuando(panelActual)) {
                return;
            }

            // No recrear la tabla ni los controles cuando nada ha cambiado.
            if (panelActual.innerHTML === panelNuevo.innerHTML) {
                return;
            }

            // Mantener el desplazamiento horizontal de la tabla al renovar filas.
            const scrollAnterior = panelActual.querySelector('.scroll');
            const scrollX = scrollAnterior ? scrollAnterior.scrollLeft : 0;
            const scrollY = scrollAnterior ? scrollAnterior.scrollTop : 0;

            panelActual.innerHTML = panelNuevo.innerHTML;

            const scrollNuevo = panelActual.querySelector('.scroll');
            if (scrollNuevo) {
                scrollNuevo.scrollLeft = scrollX;
                scrollNuevo.scrollTop = scrollY;
            }
        } catch (error) {
            console.warn('Auto-refresh del panel comercial:', error);
        } finally {
            solicitudActiva = false;
        }
    }

    function iniciarPolling() {
        // La vista es tradicional (Blade); no requiere modificar rutas ni controladores.
        actualizarPanel();
        window.setInterval(actualizarPanel, INTERVALO_MS);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarPolling, { once: true });
    } else {
        iniciarPolling();
    }
})();
</script>

@endsection