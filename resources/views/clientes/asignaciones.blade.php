@extends('layouts.app')
@section('title', 'Asignaciones de clientes - CRM')
@section('header-title', 'Asignaciones de clientes')
@section('content')
<style>
.op-panel{--op-accent:#38bdf8;color:#e2e8f0;max-width:1400px;margin:auto;font-size:14px}.op-panel *{box-sizing:border-box}.op-panel .op-eyebrow{color:var(--op-accent);font-size:11px;letter-spacing:.14em;text-transform:uppercase;font-weight:700;margin:0 0 12px}.op-panel h1{color:#f8fafc;font-size:clamp(25px,3vw,34px);margin:0 0 12px;line-height:1.2}.op-panel p{line-height:1.7}.op-panel .op-muted{color:#a6b5cb}.op-panel .op-card{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:24px;margin-top:24px}.op-panel .op-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.op-panel .op-between{justify-content:space-between}.op-panel .op-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:10px 16px;border:1px solid #475569;border-radius:8px;background:#172337;color:#e2e8f0;text-decoration:none;font:inherit;cursor:pointer}.op-panel .op-primary{background:#0369a1;border-color:#0284c7;color:white}.op-panel :is(a,button,input,select):focus-visible{outline:2px solid #38bdf8;outline-offset:3px}.op-panel input,.op-panel select{background:#0f172a;border:1px solid #475569;color:#f8fafc;border-radius:8px;padding:11px;min-height:42px;font:inherit;max-width:100%}.op-panel .op-search{flex:1;min-width:180px}.op-panel .op-table-scroll{overflow-x:auto;margin-top:20px}.op-panel table{width:100%;border-collapse:collapse;text-align:left;min-width:720px}.op-panel th{background:#152033;color:#a6b5cb;font-size:11px;text-transform:uppercase;letter-spacing:.06em;padding:14px}.op-panel td{padding:16px 14px;border-bottom:1px solid #334155;vertical-align:middle}.op-panel td small{display:block;margin-top:6px;color:#a6b5cb}.op-panel .op-notice{border:1px solid #475569;border-radius:8px;padding:14px;margin-top:16px;background:#142337}.op-panel .op-error{color:#fecaca;border-color:#9f4655}.op-panel .op-success{color:#a7f3d0;border-color:#286556}.op-panel .op-metric{font-size:44px;font-weight:700;line-height:1.2;color:var(--op-accent);margin:12px 0}.op-panel .op-grid{display:grid;grid-template-columns:1fr 2fr;gap:20px}.op-panel .op-grid .op-card{margin-top:0}.op-panel .op-empty{text-align:center;padding:36px}.op-panel label{display:block;margin-bottom:8px;color:#cbd5e1}.op-panel .op-pill{display:inline-block;padding:6px 10px;background:#0f172a;border:1px solid #475569;border-radius:6px;color:var(--op-accent);font-size:12px;margin-bottom:18px}@media(max-width:700px){.op-panel .op-grid{grid-template-columns:1fr}.op-panel .op-card{padding:18px}.op-panel .op-row form{width:100%}}
</style>
<section class="op-panel" aria-labelledby="op-titulo">
    <div class="op-row op-between">
        <div><p class="op-eyebrow">COMICenter / Administración</p><h1 id="op-titulo">Asignaciones de clientes</h1>
        <p class="op-muted">Designa un responsable para cada cliente. Cada operario puede atender varios clientes.</p></div>
        <a href="{{ route('clientes.index') }}" class="op-btn">Volver a clientes</a>
    </div>
    @if(session('success'))<div role="status" class="op-notice op-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="op-notice op-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="op-notice">Solo aparecen operarios de Asignación activos y aprobados de tu empresa. Reasignar cambia el acceso al cliente; sus citas y llamadas anteriores conservan su autor y no se transfieren automáticamente.</div>
    @if($operarios->isEmpty())<div class="op-notice">No hay operarios disponibles. Activa y aprueba un operario de Asignación desde Operarios.</div>@endif
    <div class="op-card">
        <form method="GET" action="{{ route('clientes.asignaciones.index') }}">
            <label for="op-buscar">Buscar cliente</label>
            <div class="op-row"><input class="op-search" type="search" id="op-buscar" name="buscar" maxlength="100" value="{{ $buscar }}" placeholder="Nombre, organización o teléfono"><button class="op-btn op-primary">Buscar</button>
            @if($buscar !== '')<a class="op-btn" href="{{ route('clientes.asignaciones.index') }}">Limpiar</a>@endif</div>
        </form>
        <p class="op-muted">{{ $clientes->total() }} clientes{{ $buscar !== '' ? ' encontrados para “'.$buscar.'”' : '' }}</p>
        <div class="op-table-scroll" tabindex="0" role="region" aria-label="Asignaciones de clientes">
        <table><thead><tr><th scope="col">Cliente</th><th scope="col">Responsable actual</th><th scope="col">Asignar o reasignar</th></tr></thead><tbody>
        @forelse($clientes as $cliente)
            <tr><td><strong>{{ $cliente->nombre_completo }}</strong><small>#{{ $cliente->id_cliente }} · {{ $cliente->organizacion ?: 'Sin organización' }}</small></td>
            <td>{{ $cliente->responsable?->name ?? 'Sin asignar' }}
                @if($cliente->responsable && !$operarios->contains('id_usuario', $cliente->id_usuario_responsable))<small>Responsable no disponible. Reasigna o retira la asignación.</small>@endif
            </td>
            <td><form method="POST" action="{{ route('clientes.asignaciones.update', $cliente->id_cliente) }}">
                @csrf @method('PUT')
                <input type="hidden" name="responsable_anterior" value="{{ $cliente->id_usuario_responsable }}">
                <label for="responsable-{{ $cliente->id_cliente }}">Responsable de {{ $cliente->nombre_completo }}</label>
                <div class="op-row"><select name="id_usuario_responsable" id="responsable-{{ $cliente->id_cliente }}">
                    @if($cliente->id_usuario_responsable && !$operarios->contains('id_usuario', $cliente->id_usuario_responsable))
                        <option value="{{ $cliente->id_usuario_responsable }}" selected disabled>Selecciona otro responsable</option>
                    @endif
                    <option value="" @selected(!$cliente->id_usuario_responsable)>Sin asignar</option>
                    @foreach($operarios as $operario)<option value="{{ $operario->id_usuario }}" @selected($cliente->id_usuario_responsable == $operario->id_usuario)>{{ $operario->name }} · #{{ $operario->id_usuario }}</option>@endforeach
                </select><button class="op-btn op-primary" aria-label="Guardar responsable de {{ $cliente->nombre_completo }}">Guardar</button></div>
            </form></td></tr>
        @empty
            <tr><td colspan="3" class="op-empty">{{ $buscar !== '' ? 'No hay clientes que coincidan con tu búsqueda.' : 'Todavía no hay clientes registrados.' }}</td></tr>
        @endforelse
        </tbody></table></div>
        <div style="margin-top:20px">{{ $clientes->links() }}</div>
    </div>
</section>
@endsection
