@extends('layouts.app')

@section('title', 'Programar visita - COMICenter')

@section('content')

<style>

    .cp-page {
        padding: 28px;
        color: #e2e8f0;
    }

    .cp-card {
        max-width: 780px;
        padding: 24px;
        background: #131e30;
        border: 1px solid #2b3a50;
        border-radius: 12px;
    }

    .cp-title {
        margin: 0 0 20px;
        color: #f8fafc;
    }

    .cp-info {
        margin-bottom: 22px;
        padding: 16px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 9px;
    }

    .cp-info p {
        margin: 6px 0;
    }

    .cp-field {
        margin-bottom: 18px;
    }

    .cp-field label {
        display: block;
        margin-bottom: 7px;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
    }

    .cp-select {
        width: 100%;
        height: 42px;
        padding: 0 12px;
        background: #0f172a;
        color: #e2e8f0;
        border: 1px solid #334155;
        border-radius: 8px;
    }

    .cp-actions {
        display: flex;
        gap: 10px;
    }

    .cp-button {
        min-height: 41px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        border-radius: 8px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
    }

    .cp-primary {
        background: #0284c7;
        color: white;
        border: 1px solid #0284c7;
    }

    .cp-secondary {
        background: #1e293b;
        color: #cbd5e1;
        border: 1px solid #334155;
    }

    .cp-alert {
        margin-bottom: 18px;
        padding: 13px;
        background: rgba(239, 68, 68, .12);
        color: #fca5a5;
        border-radius: 8px;
    }

</style>


<section class="cp-page">

    <div class="cp-card">

        <h1 class="cp-title">
            Programar visita técnica
        </h1>


        @if(session('error'))

            <div class="cp-alert">
                {{ session('error') }}
            </div>

        @endif


        <div class="cp-info">

            <p>
                <strong>Contacto:</strong>
                {{ $solicitud->nombre_contacto }}
            </p>

            <p>
                <strong>Teléfono:</strong>
                {{ $solicitud->telefono }}
            </p>

            <p>
                <strong>Equipo:</strong>
                {{ $solicitud->equipo }}
            </p>

            <p>
                <strong>Problema:</strong>
                {{ $solicitud->descripcion_problema }}
            </p>

            <p>
                <strong>Preferencia:</strong>
                {{ $solicitud->horario_preferido }}
            </p>

        </div>


        <form
            method="GET"
            action="{{
                route(
                    'comi.servicio.programar.form',
                    $solicitud->id_solicitud
                )
            }}"
            class="cp-field"
        >

            <label>
                Agenda
            </label>

            <select
                name="agenda"
                class="cp-select"
                onchange="this.form.submit()"
            >

                @foreach($agendas as $agenda)

                    <option
                        value="{{ $agenda->id_agenda }}"
                        @selected(
                            $agendaSeleccionada
                                ->id_agenda
                            ==
                            $agenda->id_agenda
                        )
                    >
                        {{ $agenda->nombre }}
                    </option>

                @endforeach

            </select>

        </form>


        <form
            method="POST"
            action="{{
                route(
                    'comi.servicio.programar',
                    $solicitud->id_solicitud
                )
            }}"
        >

            @csrf


            <input
                type="hidden"
                name="id_agenda"
                value="{{
                    $agendaSeleccionada->id_agenda
                }}"
            >


            <div class="cp-field">

                <label>
                    Horario disponible
                </label>

                <select
                    name="fecha_hora"
                    class="cp-select"
                    required
                >

                    <option value="">
                        Selecciona un horario
                    </option>

                    @foreach($slots as $slot)

                        <option
                            value="{{
                                $slot->format(
                                    'Y-m-d\TH:i'
                                )
                            }}"
                        >
                            {{
                                $slot->format(
                                    'd/m/Y H:i'
                                )
                            }}
                        </option>

                    @endforeach

                </select>

            </div>


            @if($slots->isEmpty())

                <p style="color:#fbbf24;">
                    No hay horarios disponibles.
                </p>

            @endif


            <div class="cp-actions">

                <button
                    class="cp-button cp-primary"
                    type="submit"
                    @disabled($slots->isEmpty())
                >
                    Programar visita
                </button>


                <a
                    href="{{
                        route(
                            'comi.servicio.index'
                        )
                    }}"
                    class="cp-button cp-secondary"
                >
                    Cancelar
                </a>

            </div>

        </form>

    </div>

</section>

@endsection