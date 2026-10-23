@php
    $avance = $misObjetivos['actual'];
    $periodos = ['semanal' => 'Semanal', 'mensual' => 'Mensual', 'personalizado' => 'Personalizado'];
@endphp
<style>
.op-panel .goal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
.op-panel .goal-title{font-size:22px;color:#f8fafc;margin:4px 0 10px}
.op-panel .goal-tag{display:inline-block;border:1px solid #475569;background:#142337;color:#bae6fd;border-radius:99px;padding:7px 12px;font-size:12px;font-weight:600}
.op-panel .goal-tag.cumplido{border-color:#287560;background:#12372f;color:#6ee7b7}
.op-panel .goal-tag.proximo_vencer,.op-panel .goal-tag.vencido{border-color:#80632e;background:#342b1d;color:#fde68a}
.op-panel .goal-numbers{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:24px 0}
.op-panel .goal-number{background:#142033;border:1px solid #334155;border-radius:12px;padding:18px}
.op-panel .goal-number small{color:#a6b5cb;display:block;margin-bottom:10px}
.op-panel .goal-number strong{color:#f8fafc;font-size:30px;line-height:1.2}
.op-panel .goal-number:first-child strong{color:var(--op-accent)}
.op-panel .goal-progress{width:100%;height:12px;background:#0f172a;border-radius:12px;overflow:hidden}
.op-panel .goal-progress span{display:block;height:100%;background:linear-gradient(90deg,#0284c7,#38bdf8);border-radius:12px}
.op-panel .goal-period{color:#cbd5e1;margin:0}
.op-panel .goal-explanation{font-size:12px;color:#a6b5cb;margin:16px 0 0}
.op-panel .goal-history th,.op-panel .goal-history td{padding:14px}
@media(max-width:550px){.op-panel .goal-numbers{grid-template-columns:1fr;gap:10px}.op-panel .goal-number{display:flex;align-items:center;justify-content:space-between}.op-panel .goal-number small{margin:0}}
</style>
<section class="op-card" aria-labelledby="my-goal-title">
    <div class="goal-head">
        <div>
            <p class="op-eyebrow">Mi objetivo de llamadas</p>
            <h2 class="goal-title" id="my-goal-title">Tu avance, a la vista</h2>
            <p class="op-muted" style="margin:0">La meta que tu administrador asignó a tu cuenta.</p>
        </div>
        @if($avance)
            <span class="goal-tag {{ $avance['situacion'] }}">{{ $avance['etiqueta'] }}</span>
        @else
            <span class="goal-tag">Sin objetivo vigente</span>
        @endif
    </div>

    @if($avance)
        <p class="goal-period" style="margin-top:22px">
            <strong>{{ $periodos[$avance['objetivo']->periodo] ?? 'Personalizado' }}</strong>
            · Del {{ $avance['objetivo']->fecha_inicio->format('d/m/Y') }}
            al {{ $avance['objetivo']->fecha_fin->format('d/m/Y') }}
        </p>
        <div class="goal-numbers">
            <div class="goal-number"><small>Meta de llamadas</small><strong>{{ number_format($avance['meta']) }}</strong></div>
            <div class="goal-number"><small>Salientes finalizadas</small><strong>{{ number_format($avance['realizadas']) }}</strong></div>
            <div class="goal-number"><small>Para completar tu meta</small><strong>{{ number_format($avance['restantes']) }}</strong></div>
        </div>
        <div class="op-row op-between" style="margin-bottom:10px">
            <strong>{{ $avance['porcentaje'] }}% de avance</strong>
            <span class="op-muted">
                @if($avance['estado'] === 'pendiente')
                    Inicia el {{ $avance['objetivo']->fecha_inicio->format('d/m/Y') }}
                @elseif($avance['estado'] === 'cumplido')
                    ¡Objetivo alcanzado!
                @elseif($avance['dias_restantes'] === 0)
                    Tu periodo termina hoy
                @else
                    {{ $avance['dias_restantes'] }} días para finalizar el periodo
                @endif
            </span>
        </div>
        <div class="goal-progress" role="progressbar" aria-label="Avance de tu objetivo de llamadas"
             aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $avance['barra'] }}"
             aria-valuetext="{{ $avance['realizadas'] }} de {{ $avance['meta'] }} llamadas">
            <span style="width:{{ $avance['barra'] }}%"></span>
        </div>
        <p class="goal-explanation">
            Se cuentan tus llamadas salientes iniciadas en el periodo y con cierre registrado.
            Los intentos que siguen abiertos no avanzan la meta. Fechas en horario de Ciudad de México.
        </p>
        <div class="op-row" style="margin-top:20px">
            <a class="op-btn op-primary" href="{{ route('llamadas.index') }}">Ver mis llamadas</a>
            <span class="op-muted">{{ number_format($avance['intentadas']) }} intentos · {{ number_format($avance['clasificadas']) }} con resultado registrado</span>
        </div>
    @else
        <div class="op-notice">
            No tienes una meta vigente. Cuando tu administrador te asigne un objetivo, aparecerá aquí con su periodo y avance.
        </div>
    @endif
</section>

@if($misObjetivos['historial']->isNotEmpty())
    <section class="op-card" aria-labelledby="my-goal-history-title">
        <div class="goal-head">
            <div><p class="op-eyebrow">Seguimiento personal</p><h2 class="goal-title" id="my-goal-history-title">Mis últimos objetivos</h2></div>
            <span class="op-muted">Últimas 5 asignaciones</span>
        </div>
        <div class="op-table-scroll goal-history">
            <table>
                <caption style="text-align:left;color:#a6b5cb;padding:0 0 14px">Incluye la meta vigente y tus asignaciones anteriores.</caption>
                <thead><tr><th scope="col">Periodo</th><th scope="col">Fechas</th><th scope="col">Meta</th><th scope="col">Finalizadas</th><th scope="col">Estado</th></tr></thead>
                <tbody>
                    @foreach($misObjetivos['historial'] as $registro)
                        <tr>
                            <td>{{ $periodos[$registro['objetivo']->periodo] ?? 'Personalizado' }}</td>
                            <td>{{ $registro['objetivo']->fecha_inicio->format('d/m/Y') }} — {{ $registro['objetivo']->fecha_fin->format('d/m/Y') }}</td>
                            <td>{{ number_format($registro['meta']) }}</td>
                            <td>{{ number_format($registro['realizadas']) }}<small>{{ $registro['porcentaje'] }}% de avance</small></td>
                            <td><span class="goal-tag {{ $registro['situacion'] }}">{{ $registro['etiqueta'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="goal-explanation">Al asignar una nueva meta, el sistema puede cerrar la anterior. Sus llamadas no se trasladan automáticamente a otro periodo.</p>
    </section>
@endif
