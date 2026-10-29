<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Extensiones SIP | COMI Center</title>
    <style>
        :root { color-scheme: dark; --bg:#0f172a; --panel:#1e293b; --panel2:#142036; --line:#334155; --txt:#f1f5f9; --muted:#9aacc6; --cyan:#38bdf8; --green:#34d399; --yellow:#fbbf24; }
        * { box-sizing:border-box; }
        body { margin:0; background:radial-gradient(circle at 90% 0%,#133353 0%,transparent 35%),var(--bg); color:var(--txt); font:14px/1.5 system-ui,-apple-system,Segoe UI,sans-serif; min-height:100vh; }
        a { color:var(--cyan); text-decoration:none; } a:hover { text-decoration:underline; }
        .container { max-width:1240px; padding:28px 22px 55px; margin:auto; }
        header { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
        .brand { font-size:18px; font-weight:800; letter-spacing:-.7px; }
        .brand span { color:var(--cyan); }
        .back { padding:9px 14px; border:1px solid var(--line); border-radius:10px; color:var(--txt); }
        h1 { font-size:clamp(23px,3vw,30px); letter-spacing:-.8px; margin:0 0 6px; }
        h2 { font-size:17px; letter-spacing:-.35px; margin:0 0 5px; }
        p { margin:0; }
        .sub { color:var(--muted); margin-bottom:20px; }
        .panel { background:linear-gradient(155deg,var(--panel),#172438); border:1px solid var(--line); border-radius:17px; padding:21px; box-shadow:0 14px 32px rgba(0,0,0,.09); }
        .top { display:flex; justify-content:space-between; align-items:end; flex-wrap:wrap; gap:12px; }
        .field-label { display:block; color:var(--muted); font-size:12px; margin-bottom:7px; font-weight:600; }
        select, textarea, button { font:inherit; }
        select, textarea { background:var(--bg); border:1px solid #476078; color:var(--txt); padding:10px 11px; border-radius:9px; width:100%; min-width:0; }
        select:focus, textarea:focus { outline:2px solid var(--cyan); outline-offset:1px; }
        button { border:0; border-radius:9px; padding:10px 13px; cursor:pointer; font-weight:700; background:#087fb2; color:white; }
        button:hover { background:#0284c7; }
        button.secondary { background:#25364d; border:1px solid #42536c; }
        button.secondary:hover { background:#344761; }
        button:disabled { opacity:.5; cursor:not-allowed; }
        .stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:13px; margin:20px 0; }
        .stat { background:#18273d; border:1px solid var(--line); border-radius:13px; padding:14px 16px; }
        .stat small { display:block; color:var(--muted); font-size:12px; }
        .stat strong { display:block; font-size:27px; line-height:1.2; margin-top:4px; }
        .layout { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(290px,1fr); align-items:start; gap:16px; }
        .stack { display:grid; gap:16px; }
        .table-wrap { width:100%; overflow-x:auto; margin-top:14px; }
        table { border-collapse:collapse; min-width:600px; width:100%; }
        td,th { padding:13px 10px; text-align:left; border-bottom:1px solid #30415a; vertical-align:middle; }
        th { color:#a8b7c9; font-size:11px; text-transform:uppercase; letter-spacing:.06em; }
        tr:last-child td { border-bottom:none; }
        .muted { color:var(--muted); font-size:12px; }
        .pill { display:inline-block; padding:4px 9px; border-radius:999px; font-size:11px; font-weight:800; white-space:nowrap; }
        .ok { background:#10392e; color:var(--green); } .warn { background:#42351e; color:var(--yellow); }
        .off { background:#334155; color:#cbd5e1; }
        .assign { display:flex; gap:7px; align-items:center; }
        .assign select { min-width:155px; padding:8px 9px; }
        .assign button { white-space:nowrap; padding:9px 11px; }
        .mini { display:inline-block; margin-top:6px; }
        .message { padding:12px 14px; margin:13px 0; border-radius:9px; border:1px solid; }
        .message.success { background:#123c2c; border-color:#176b51; color:#b4f5d7; }
        .message.error { background:#472029; border-color:#a13f55; color:#ffd5db; }
        .message ul { margin:3px 0 0; padding-left:20px; }
        .inventory { display:grid; grid-template-columns:repeat(auto-fill,minmax(90px,1fr)); gap:7px; margin-top:15px; }
        .chip { background:#112139; border:1px solid #374e69; border-radius:10px; padding:9px; text-align:center; }
        .chip strong { display:block; font-size:15px; }
        .chip small { display:block; color:var(--muted); font-size:10px; margin-top:2px; }
        .chip.busy { border-color:#4b3b24; }
        .chip.disabled { opacity:.5; }
        textarea { min-height:84px; resize:vertical; margin-bottom:10px; }
        .note { font-size:12px; color:var(--muted); margin-top:10px; }
        @media(max-width:930px) { .layout { grid-template-columns:1fr; } }
        @media(max-width:620px) { .container { padding:18px 12px 35px; } .panel { padding:16px 12px; } .stats { gap:7px; } .stat { padding:12px 10px; } .stat strong { font-size:22px; } }
    </style>
</head>
<body>
<div class="container">
    <header>
        <div class="brand">COMI<span>Center</span> <span class="muted">/ Operarios</span></div>
        <a class="back" href="{{ url('/operarios') }}">← Volver a operarios</a>
    </header>

    <div class="top">
        <div>
            <h1>Asignación de extensiones SIP</h1>
            <p class="sub">Administra las extensiones disponibles y asígnalas a los operarios autorizados.</p>
        </div>
        @if((int) auth()->user()->id_rol === 1)
            <form method="GET" action="{{ route('extensiones-sip.index') }}" style="min-width:260px">
                <label class="field-label" for="empresa">Empresa</label>
                <select name="id_empresa" id="empresa" onchange="this.form.submit()">
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id_empresa }}" @selected((int) $idEmpresa === (int) $empresa->id_empresa)>{{ $empresa->nombre }}</option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    @if(session('exito'))
        <div class="message success" role="status">{{ session('exito') }}</div>
    @endif
    @if($errors->any())
        <div class="message error" role="alert"><strong>Revisa los datos:</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @php
        $disponibles = $inventario->filter(fn ($e) => $e->activa && !$e->asignado_a);
        $ocupadas = $inventario->filter(fn ($e) => (bool) $e->asignado_a);
    @endphp
    <div class="stats">
        <div class="stat"><small>Extensiones registradas</small><strong>{{ $inventario->count() }}</strong></div>
        <div class="stat"><small>Disponibles</small><strong style="color:var(--green)">{{ $disponibles->count() }}</strong></div>
        <div class="stat"><small>Asignadas</small><strong>{{ $ocupadas->count() }}</strong></div>
    </div>

    <div class="layout">
        <section class="panel">
            <h2>Operarios de la empresa</h2>
            <p class="muted">Selecciona una extensión libre. La anterior se libera si reasignas al operario.</p>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Operario</th><th>Extensión actual</th><th>Asignación</th></tr></thead>
                    <tbody>
                    @forelse($operarios as $operario)
                        @php
                            $propiasDisponibles = $inventario->filter(fn ($e) => $e->activa && (!$e->asignado_a || (int) $e->asignado_a === (int) $operario->id_usuario));
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $operario->nombre }} {{ $operario->apellido_paterno }}</strong>
                                <div class="muted">ID #{{ $operario->id_usuario }} {{ !$operario->activo ? '· Inactivo' : '' }}</div>
                            </td>
                            <td>
                                @if($operario->extension_asterisk)
                                    <span class="pill ok">{{ $operario->extension_asterisk }}</span>
                                    <form action="{{ route('extensiones-sip.liberar') }}" method="POST" class="mini">
                                        @csrf
                                        <input type="hidden" name="id_empresa" value="{{ $idEmpresa }}">
                                        <input type="hidden" name="id_usuario" value="{{ $operario->id_usuario }}">
                                        <button class="secondary" type="submit" style="font-size:11px;padding:5px 8px">Liberar</button>
                                    </form>
                                @else
                                    <span class="pill warn">Sin extensión</span>
                                @endif
                            </td>
                            <td>
                                <form class="assign" method="POST" action="{{ route('extensiones-sip.asignar') }}">
                                    @csrf
                                    <input type="hidden" name="id_empresa" value="{{ $idEmpresa }}">
                                    <input type="hidden" name="id_usuario" value="{{ $operario->id_usuario }}">
                                    <select name="extension" aria-label="Extensión para {{ $operario->nombre }}" required>
                                        <option value="">Seleccionar…</option>
                                        @foreach($propiasDisponibles as $extension)
                                            <option value="{{ $extension->extension }}" @selected($operario->extension_asterisk === $extension->extension)>
                                                {{ $extension->extension }}{{ $operario->extension_asterisk === $extension->extension ? ' (actual)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" @disabled($propiasDisponibles->isEmpty())>Asignar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">No hay operarios registrados para esta empresa.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="stack">
            <section class="panel">
                <h2>Catálogo de extensiones</h2>
                <p class="muted">Las extensiones registradas en el CRM, con su estado actual.</p>
                <div class="inventory">
                    @forelse($inventario as $extension)
                        <div class="chip {{ !$extension->activa ? 'disabled' : ($extension->asignado_a ? 'busy' : '') }}">
                            <strong>{{ $extension->extension }}</strong>
                            <small>{{ !$extension->activa ? 'Inactiva' : ($extension->asignado_a ? 'Asignada' : 'Libre') }}</small>
                        </div>
                    @empty
                        <p class="muted">No hay extensiones registradas.</p>
                    @endforelse
                </div>
            </section>
            <section class="panel">
                <h2>Registrar extensiones SIP</h2>
                <p class="muted">Pega los números creados previamente en Asterisk.</p>
                <form method="POST" action="{{ route('extensiones-sip.registrar') }}" style="margin-top:14px">
                    @csrf
                    <input type="hidden" name="id_empresa" value="{{ $idEmpresa }}">
                    <label class="field-label" for="extensiones">Extensiones (separadas por coma, espacio o línea)</label>
                    <textarea name="extensiones" id="extensiones" placeholder="1001, 1002, 1003, 1004" required>{{ old('extensiones') }}</textarea>
                    <button type="submit">Registrar en CRM</button>
                </form>
                <p class="note">Registrar un código aquí no crea el endpoint PJSIP en Asterisk ni verifica que esté conectado.</p>
            </section>
        </aside>
    </div>
</div>
</body>
</html>
