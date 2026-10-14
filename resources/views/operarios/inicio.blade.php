@extends('layouts.app')
@section('title', 'Mi espacio de trabajo - CRM')
@section('header-title', 'Mi espacio de trabajo')
@section('content')
<style>
.op-panel{--op-accent:#38bdf8;color:#e2e8f0;max-width:1400px;margin:auto;font-size:14px}.op-panel *{box-sizing:border-box}.op-panel .op-eyebrow{color:var(--op-accent);font-size:11px;letter-spacing:.14em;text-transform:uppercase;font-weight:700;margin:0 0 12px}.op-panel h1{color:#f8fafc;font-size:clamp(25px,3vw,34px);margin:0 0 12px;line-height:1.2}.op-panel p{line-height:1.7}.op-panel .op-muted{color:#a6b5cb}.op-panel .op-card{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:24px;margin-top:24px}.op-panel .op-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.op-panel .op-between{justify-content:space-between}.op-panel .op-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:10px 16px;border:1px solid #475569;border-radius:8px;background:#172337;color:#e2e8f0;text-decoration:none;font:inherit;cursor:pointer}.op-panel .op-primary{background:#0369a1;border-color:#0284c7;color:white}.op-panel :is(a,button,input,select):focus-visible{outline:2px solid #38bdf8;outline-offset:3px}.op-panel input,.op-panel select{background:#0f172a;border:1px solid #475569;color:#f8fafc;border-radius:8px;padding:11px;min-height:42px;font:inherit;max-width:100%}.op-panel .op-search{flex:1;min-width:180px}.op-panel .op-table-scroll{overflow-x:auto;margin-top:20px}.op-panel table{width:100%;border-collapse:collapse;text-align:left;min-width:720px}.op-panel th{background:#152033;color:#a6b5cb;font-size:11px;text-transform:uppercase;letter-spacing:.06em;padding:14px}.op-panel td{padding:16px 14px;border-bottom:1px solid #334155;vertical-align:middle}.op-panel td small{display:block;margin-top:6px;color:#a6b5cb}.op-panel .op-notice{border:1px solid #475569;border-radius:8px;padding:14px;margin-top:16px;background:#142337}.op-panel .op-error{color:#fecaca;border-color:#9f4655}.op-panel .op-success{color:#a7f3d0;border-color:#286556}.op-panel .op-metric{font-size:44px;font-weight:700;line-height:1.2;color:var(--op-accent);margin:12px 0}.op-panel .op-grid{display:grid;grid-template-columns:1fr 2fr;gap:20px}.op-panel .op-grid .op-card{margin-top:0}.op-panel .op-empty{text-align:center;padding:36px}.op-panel label{display:block;margin-bottom:8px;color:#cbd5e1}.op-panel .op-pill{display:inline-block;padding:6px 10px;background:#0f172a;border:1px solid #475569;border-radius:6px;color:var(--op-accent);font-size:12px;margin-bottom:18px}@media(max-width:700px){.op-panel .op-grid{grid-template-columns:1fr}.op-panel .op-card{padding:18px}.op-panel .op-row form{width:100%}}
</style>
<section class="op-panel" style="--op-accent:{{ $esAsignacion ? '#d8b4fe' : '#38bdf8' }}">
    <p class="op-eyebrow">COMICenter / Tu espacio de trabajo</p>
    <span class="op-pill">Operario · {{ $esAsignacion ? 'Asignación' : 'Prospección' }}</span>
    <h1>Bienvenido, {{ auth()->user()->name }}</h1>
    <p class="op-muted">{{ $esAsignacion ? 'Organiza la atención de los clientes que tu administrador puso a tu cargo.' : 'Encuentra oportunidades entre los clientes de tu empresa y registra nuevos prospectos.' }}</p>
    <div class="op-grid" style="margin-top:28px">
        <div class="op-card"><p class="op-eyebrow">{{ $esAsignacion ? 'Clientes a tu cargo' : 'Clientes de tu empresa' }}</p>
            <div class="op-metric">{{ number_format($totalClientes) }}</div>
            <p class="op-muted">{{ $esAsignacion ? 'Clientes disponibles para tu atención.' : 'Contactos disponibles para consultar y dar seguimiento.' }}</p>
            <a href="{{ route('clientes.index') }}" class="op-btn op-primary">{{ $esAsignacion ? 'Ver mis clientes' : 'Buscar clientes' }}</a>
        </div>
        <div class="op-card"><h2 style="color:#f8fafc;font-size:20px;margin:0 0 12px">¿Qué deseas hacer?</h2>
            <p class="op-muted">Consulta tus contactos programados o continúa el seguimiento de tus llamadas.</p>
            <div class="op-row">
                @if(!$esAsignacion)<a href="{{ route('clientes.create') }}" class="op-btn op-primary">Nuevo cliente</a>@endif
                <a href="{{ route('calendario.index') }}" class="op-btn">Mi agenda</a>
                <a href="{{ route('llamadas.index') }}" class="op-btn">Mis llamadas</a>
            </div>
            @if($esAsignacion && $totalClientes === 0)<div class="op-notice">Aún no tienes clientes asignados. Tu administrador puede asignártelos desde Clientes → Asignaciones.</div>@endif
        </div>
    </div>
</section>
@endsection
