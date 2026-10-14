@extends('layouts.app')

@section('title', 'Reprogramar cita - COMICenter')

@section('content')

<style>

    .cr-page {
        padding: 28px;
        color: #e2e8f0;
    }

    .cr-card {
        max-width: 760px;
        padding: 24px;
        background: #131e30;
        border: 1px solid #2b3a50;
        border-radius: 12px;
    }

    .cr-title {
        margin: 0 0 6px;
        color: #f8fafc;
        font-size: 26px;
    }

    .cr-subtitle {
        margin: 0 0 24px;
        color: #94a3b8;
    }

    .cr-info {
        margin-bottom: 22px;
        padding: 16px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 9px;
    }

    .cr-info p {
        margin: 5px 0;
    }

    .cr-label {
        display: block;
        margin-bottom: 7px;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
    }

    .cr-select {
        width: 100%;
        height: 43px;
        padding: 0 12px;
        background: #0f172a;
        color: #e2e8f0;
        border: 1px solid #334155;
        border-radius: 8px;
    }

    .cr-actions {
        display: flex;
        gap: 10px;
        margin-top: 22px;
    }

    .cr-button {
        min-height: 41px;
        padding: 0 16px;
        border-radius: 8px;
        font-weight: 650;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .cr-primary {
        background: #0284c7;
        color: white;
        border: 1px solid #0284c7;
    }

    .cr-secondary {
        background: #1e293b;
        color: #cbd5e1;
        border: 1px solid #334155;
    }

    .cr-alert {
        margin-bottom: 18px;
        padding: 13px;
        background: rgba(239, 68, 68, .12);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, .30);
        border-radius: 8px;
    }

</style>


<section class="cr-page">

    <div class="cr-card">

        <h1 class="cr-title">
            Reprogramar cita
        </h1>

        <p class="cr-subtitle">
            Selecciona uno de los próximos horarios disponibles.
        </p>


        @if(session('error'))

            <div class="cr-alert">
                {{ session('error') }}
            </div>

        @endif


        <div class="cr-info">

            <p>
                <strong>Proyecto:</strong>
                {{ $cita->proyecto?->nombre }}
            </p>

            <p>
                <strong>Agenda:</strong>
                {{ $cita->agenda?->nombre }}
            </p>

            <p>
                <strong>Motivo:</strong>
                {{ $cita->motivo ?: 'Sin motivo' }}
            </p>

            <p>
                <strong>Fecha actual:</strong>

                {{
                    $cita
                        ->fecha_hora_inicio
                        ->timezone($zonaHoraria)
                        ->format('d/m/Y H:i')
                }}
            </p>

        </div>


        <form
            method="POST"
            action="{{
                route(
                    'comi.agenda.reprogramar',
                    $cita->id_cita
                )
            }}"
        >

            @csrf
            @method('PATCH')


            <label
                class="cr-label"
                for="fecha_hora"
            >
                Nuevo horario
            </label>


            <select
                name="fecha_hora"
                id="fecha_hora"
                class="cr-select"
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


            @if($slots->isEmpty())

                <p
                    style="
                        color:#fbbf24;
                        font-size:13px;
                    "
                >
                    No se encontraron horarios disponibles.
                </p>

            @endif


            <div class="cr-actions">

                <button
                    type="submit"
                    class="cr-button cr-primary"
                    @disabled($slots->isEmpty())
                >
                    Guardar reprogramación
                </button>


                <a
                    href="{{
                        route(
                            'comi.agenda.index'
                        )
                    }}"
                    class="cr-button cr-secondary"
                >
                    Cancelar
                </a>

            </div>

        </form>

    </div>

</section>

@endsection