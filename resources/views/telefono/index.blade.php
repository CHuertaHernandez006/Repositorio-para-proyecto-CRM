@extends('layouts.app')

@section('title', 'Teléfono WebRTC - COMICenter')
@section('header-title', 'Teléfono WebRTC')

@section('content')
<style>
    .webtel { max-width:1320px; margin:0 auto; color:#e2e8f0; }
    .webtel * { box-sizing:border-box; }
    .webtel-header { display:flex; gap:16px; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
    .webtel h1 { margin:0; font-size:25px; color:#f8fafc; }
    .webtel p { color:#94a3b8; line-height:1.55; }
    .webtel-tag { border:1px solid #334155; background:#172338; padding:8px 12px; border-radius:999px; font-size:12px; white-space:nowrap; }
    .webtel-grid { display:grid; grid-template-columns:minmax(315px,1.1fr) minmax(330px,1fr); gap:18px; align-items:start; }
    .webtel-left { display:grid; gap:18px; min-width:0; }
    .webtel-card { background:#131e30; border:1px solid #2b3a50; border-radius:17px; padding:22px; box-shadow:0 14px 32px rgba(0,0,0,.12); min-width:0; }
    .webtel-card h2 { font-size:15px; margin:0 0 15px; color:#f8fafc; }
    .webtel-label { display:block; color:#93a4bc; font-size:12px; margin:0 0 7px; }
    .webtel-input { width:100%; padding:12px 13px; background:#0b1728; border:1px solid #3a4b63; color:#f8fafc; border-radius:10px; font-size:14px; }
    .webtel-input:focus { outline:2px solid #38bdf8; outline-offset:1px; }
    .webtel-input[readonly] { opacity:.85; }
    .webtel-button { border:1px solid #42516a; border-radius:11px; padding:10px 13px; color:#e2e8f0; background:#1e2b42; font-weight:650; cursor:pointer; min-height:41px; }
    .webtel-button:hover:not(:disabled) { filter:brightness(1.15); }
    .webtel-button:focus-visible, .webtel-client-call:focus-visible { outline:2px solid #38bdf8; outline-offset:2px; }
    .webtel-button:disabled { opacity:.38; cursor:not-allowed; }
    .webtel-primary { background:#0f8c72; border-color:#19a688; color:#fff; }
    .webtel-danger { background:#a93650; border-color:#c44762; color:white; }
    .webtel-actions { display:flex; flex-wrap:wrap; gap:9px; margin-top:15px; }
    .webtel-number { font-size:21px; font-weight:750; letter-spacing:.03em; text-align:center; margin:10px 0 0; }
    .webtel-timer { text-align:center; font-variant-numeric:tabular-nums; color:#7dd3fc; font-size:15px; margin-top:6px; }
    .webtel-keypad { display:grid; grid-template-columns:repeat(3,1fr); gap:9px; margin:17px auto; max-width:315px; }
    .webtel-keypad button { padding:14px; min-height:50px; font-size:19px; border:1px solid #31435c; border-radius:12px; background:#1b2c43; color:#e8f2ff; cursor:pointer; }
    .webtel-keypad button:hover { background:#254462; }
    .webtel-status { padding:12px 14px; border-radius:11px; margin:0 0 18px; background:#122a38; border:1px solid #265265; color:#7dd3fc; font-size:13px; }
    .webtel-error { border-color:#a93f54; background:#351d2a; color:#fda4af; }
    .webtel-small { margin:10px 0; font-size:12px; }
    .webtel-mono { font-family:ui-monospace, SFMono-Regular, Consolas, monospace; font-size:12px; color:#c4b5fd; overflow-wrap:anywhere; }
    .webtel-remote { width:100%; margin-top:15px; }
    .webtel-credential-info { display:flex; align-items:center; gap:9px; margin:13px 0; padding:11px 13px; background:#0e2b32; border:1px solid #1c5053; border-radius:10px; color:#80e1ce; font-size:12px; }
    .webtel-directory-header { display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .webtel-directory-count { color:#91a4ba; font-size:12px; }
    .webtel-search { margin:10px 0 13px; }
    .webtel-client-list { display:grid; gap:8px; max-height:420px; overflow-y:auto; padding-right:4px; }
    .webtel-client { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:11px 12px; border:1px solid #2a3b52; background:#101c30; border-radius:11px; }
    .webtel-client-info { min-width:0; }
    .webtel-client-name { color:#f1f5f9; font-weight:650; font-size:13px; overflow-wrap:anywhere; }
    .webtel-client-number { color:#8ba2bc; font-size:12px; margin-top:4px; overflow-wrap:anywhere; }
    .webtel-client-buttons { display:flex; flex-wrap:wrap; gap:6px; align-items:center; justify-content:flex-end; }
    .webtel-client-call { cursor:pointer; padding:8px 10px; border-radius:9px; border:1px solid #226d65; background:#114839; color:#9bf2d4; font-size:11px; font-weight:700; white-space:nowrap; }
    .webtel-client-call:hover:not(:disabled) { background:#14614c; }
    .webtel-client-call:disabled { opacity:.42; cursor:not-allowed; }
    .webtel-client-empty { text-align:center; padding:16px 8px; color:#8ca1b9; font-size:12px; }
    .webtel-more { display:block; margin:12px auto 0; }
    @media(max-width:950px) { .webtel-grid { grid-template-columns:1fr; } .webtel-header { flex-direction:column; } }
    @media(max-width:500px) { .webtel-card { padding:15px; } .webtel-client { flex-direction:column; align-items:stretch; } .webtel-client-buttons { justify-content:flex-start; } }
</style>

<div class="webtel" id="webtel-root"
     data-enabled="{{ $habilitadoWebRtc ? '1' : '0' }}"
     data-extension="{{ $extensionWebRtc }}"
     data-wss="{{ $wssWebRtc }}"
     data-domain="{{ $dominioWebRtc }}"
     data-credentials-url="{{ route('telefono.credenciales') }}"
     data-clients-url="{{ route('telefono.clientes-disponibles') }}"
     data-validate-url="{{ route('telefono.validar-destino') }}"
     data-incoming-url="{{ route('telefono.buscar-entrante') }}">
    <div class="webtel-header">
        <div>
            <h1>Teléfono integrado COMICenter</h1>
            <p>Llama a tus clientes desde el navegador. Conserva la pestaña abierta durante las llamadas.</p>
        </div>
        <span class="webtel-tag">Extensión: <strong>{{ $extensionWebRtc ?: 'Sin asignar' }}</strong></span>
    </div>

    <div class="webtel-status" id="webtel-status" role="status" aria-live="polite">Inicializando teléfono…</div>

    <div class="webtel-grid">
        <div class="webtel-left">
            <section class="webtel-card" aria-labelledby="webtel-connect-title">
                <h2 id="webtel-connect-title">1. Conexión telefónica</h2>
                <label class="webtel-label" for="webtel-extension">Extensión SIP del operario</label>
                <input class="webtel-input" id="webtel-extension" type="text" value="{{ $extensionWebRtc }}" readonly>
                <p class="webtel-small">Servidor WSS: <span class="webtel-mono">{{ $wssWebRtc ?: 'Pendiente de infraestructura' }}</span></p>
                <div class="webtel-credential-info">Conexión automática con credencial cifrada, vinculada a tu usuario del CRM.</div>
                <div class="webtel-actions">
                    <button type="button" class="webtel-button webtel-primary" id="webtel-connect">Reintentar conexión</button>
                    <button type="button" class="webtel-button" id="webtel-disconnect" disabled>Desconectar</button>
                </div>
                <p class="webtel-small">Si la conexión no inicia, redes debe provisionar tu extensión de forma segura. No escribas contraseñas SIP en el CRM.</p>
            </section>

            <section class="webtel-card" aria-labelledby="webtel-directory-title">
                <div class="webtel-directory-header">
                    <h2 id="webtel-directory-title" style="margin:0;">2. Mis clientes asignados</h2>
                    <span class="webtel-directory-count" id="webtel-clients-count"></span>
                </div>
                <label class="webtel-label" for="webtel-clients-search" style="margin-top:15px;">Busca por nombre, organización o número</label>
                <input class="webtel-input webtel-search" id="webtel-clients-search" type="search" maxlength="100" placeholder="Buscar cliente…" autocomplete="off">
                <div class="webtel-client-list" id="webtel-clients-list" aria-live="polite">
                    <div class="webtel-client-empty">Cargando clientes autorizados…</div>
                </div>
                <button type="button" class="webtel-button webtel-more" id="webtel-clients-more" hidden>Cargar más clientes</button>
            </section>
        </div>

        <section class="webtel-card" aria-labelledby="webtel-call-title">
            <h2 id="webtel-call-title">3. Marcación y llamada</h2>
            <label class="webtel-label" for="webtel-destination">Teléfono del cliente registrado</label>
            <input class="webtel-input webtel-number" id="webtel-destination" inputmode="tel" autocomplete="off" placeholder="10 dígitos" value="{{ request('telefono', '') }}">
            <div class="webtel-keypad" aria-label="Teclado telefónico">
                @foreach(['1','2','3','4','5','6','7','8','9','*','0','#'] as $digito)
                    <button type="button" data-webtel-digit="{{ $digito }}">{{ $digito }}</button>
                @endforeach
            </div>
            <div class="webtel-timer" id="webtel-timer" aria-live="off">00:00</div>
            <p class="webtel-small" id="webtel-call-label" aria-live="polite" style="text-align:center;">Sin llamada activa</p>
            <div class="webtel-actions" style="justify-content:center;">
                <button type="button" class="webtel-button webtel-primary" id="webtel-call" disabled>Llamar</button>
                <button type="button" class="webtel-button webtel-primary" id="webtel-answer" disabled>Contestar</button>
                <button type="button" class="webtel-button" id="webtel-reject" disabled>Rechazar</button>
                <button type="button" class="webtel-button webtel-danger" id="webtel-hangup" disabled>Colgar</button>
                <button type="button" class="webtel-button" id="webtel-mute" disabled>Silenciar</button>
                <button type="button" class="webtel-button" id="webtel-hold" disabled>Espera</button>
            </div>
            <audio class="webtel-remote" id="webtel-remote-audio" autoplay controls aria-label="Audio de la llamada"></audio>
            <p class="webtel-small">Todos los destinos pasan por la validación de clientes del CRM. Redes debe mantener las restricciones de marcado en Asterisk.</p>
        </section>
    </div>
</div>

@vite('resources/js/softphone-webrtc.js')
@endsection
