@extends('layouts.app')

@section('title', 'Teléfono WebRTC - COMICenter')
@section('header-title', 'Teléfono WebRTC · Pruebas')

@section('content')
<style>
    .webtel { max-width:1100px; margin:0 auto; color:#e2e8f0; }
    .webtel * { box-sizing:border-box; }
    .webtel-header { display:flex; gap:16px; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
    .webtel h1 { margin:0; font-size:25px; color:#f8fafc; }
    .webtel p { color:#94a3b8; line-height:1.55; }
    .webtel-tag { border:1px solid #334155; background:#172338; padding:8px 12px; border-radius:999px; font-size:12px; }
    .webtel-grid { display:grid; grid-template-columns:minmax(300px,1fr) minmax(300px,1.1fr); gap:18px; }
    .webtel-card { background:#131e30; border:1px solid #2b3a50; border-radius:17px; padding:23px; box-shadow:0 14px 32px rgba(0,0,0,.12); }
    .webtel-card h2 { font-size:15px; margin:0 0 18px; color:#f8fafc; }
    .webtel-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:12px 0; }
    .webtel-label { display:block; color:#93a4bc; font-size:12px; margin:0 0 7px; }
    .webtel-input { width:100%; padding:13px 14px; background:#0b1728; border:1px solid #3a4b63; color:#f8fafc; border-radius:10px; font-size:15px; }
    .webtel-input:focus { outline:2px solid #38bdf8; outline-offset:1px; }
    .webtel-input[readonly] { opacity:.82; }
    .webtel-button { border:1px solid #42516a; border-radius:11px; padding:11px 13px; color:#e2e8f0; background:#1e2b42; font-weight:650; cursor:pointer; min-height:42px; }
    .webtel-button:hover:not(:disabled) { filter:brightness(1.15); }
    .webtel-button:disabled { opacity:.38; cursor:not-allowed; }
    .webtel-primary { background:#0f8c72; border-color:#19a688; color:#fff; }
    .webtel-danger { background:#a93650; border-color:#c44762; color:white; }
    .webtel-actions { display:flex; flex-wrap:wrap; gap:9px; margin-top:15px; }
    .webtel-number { font-size:21px; font-weight:750; letter-spacing:.04em; text-align:center; margin:10px 0 0; }
    .webtel-timer { text-align:center; font-variant-numeric:tabular-nums; color:#7dd3fc; font-size:15px; margin-top:4px; }
    .webtel-keypad { display:grid; grid-template-columns:repeat(3,1fr); gap:9px; margin:17px auto; max-width:315px; }
    .webtel-keypad button { padding:15px; min-height:51px; font-size:19px; border:1px solid #31435c; border-radius:12px; background:#1b2c43; color:#e8f2ff; cursor:pointer; }
    .webtel-keypad button:hover { background:#254462; }
    .webtel-status { padding:12px 14px; border-radius:11px; margin:0 0 18px; background:#122a38; border:1px solid #265265; color:#7dd3fc; font-size:13px; }
    .webtel-error { border-color:#a93f54; background:#351d2a; color:#fda4af; }
    .webtel-note { padding:13px; margin-top:17px; border-left:3px solid #d6a649; background:#292638; color:#e3d0ac; border-radius:6px; font-size:12px; line-height:1.55; }
    .webtel-small { margin:10px 0; font-size:12px; }
    .webtel-mono { font-family:ui-monospace, SFMono-Regular, Consolas, monospace; font-size:12px; color:#c4b5fd; overflow-wrap:anywhere; }
    .webtel-remote { width:100%; margin-top:15px; }
    @media(max-width:850px) { .webtel-grid { grid-template-columns:1fr; } .webtel-header {flex-direction:column;} }
</style>

<div class="webtel" id="webtel-root"
     data-enabled="{{ $habilitadoWebRtc ? '1' : '0' }}"
     data-extension="{{ $extensionWebRtc }}"
     data-wss="{{ $wssWebRtc }}"
     data-domain="{{ $dominioWebRtc }}"
     data-validate-url="{{ route('telefono.validar-destino') }}"
     data-incoming-url="{{ route('telefono.buscar-entrante') }}">
    <div class="webtel-header">
        <div>
            <h1>Teléfono integrado COMICenter</h1>
            <p>Pruebas WebRTC con Asterisk y SIP.js. No reemplaza todavía a Zoiper.</p>
        </div>
        <span class="webtel-tag">Extensión: <strong>{{ $extensionWebRtc ?: 'Sin asignar' }}</strong></span>
    </div>

    <div class="webtel-status" id="webtel-status" role="status" aria-live="polite">
        Inicializando módulo de pruebas…
    </div>

    <div class="webtel-grid">
        <section class="webtel-card" aria-labelledby="webtel-connect-title">
            <h2 id="webtel-connect-title">1. Conexión telefónica</h2>
            <label class="webtel-label" for="webtel-extension">Extensión SIP asignada en PostgreSQL</label>
            <input class="webtel-input" id="webtel-extension" type="text" value="{{ $extensionWebRtc }}" readonly>
            <p class="webtel-small">Servidor WSS: <span class="webtel-mono" id="webtel-wss-label">{{ $wssWebRtc ?: 'Pendiente de Infraestructura' }}</span></p>
            <label class="webtel-label" for="webtel-secret">Contraseña SIP temporal de la extensión de PRUEBA</label>
            <input class="webtel-input" id="webtel-secret" type="password" autocomplete="off" spellcheck="false" placeholder="Ingresar solo para esta sesión">
            <div class="webtel-actions">
                <button type="button" class="webtel-button webtel-primary" id="webtel-connect">Conectar y registrar</button>
                <button type="button" class="webtel-button" id="webtel-disconnect" disabled>Desconectar</button>
            </div>
            <div class="webtel-note">
                La contraseña NO se guarda en Laravel ni en localStorage. Para producción, Infraestructura debe definir credenciales WebRTC individuales y un mecanismo seguro de aprovisionamiento. La autenticación SIP del navegador necesita usar credenciales durante la sesión.
            </div>
        </section>

        <section class="webtel-card" aria-labelledby="webtel-call-title">
            <h2 id="webtel-call-title">2. Marcación y llamada</h2>
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
            <p class="webtel-small">La marcación manual valida el cliente y su asignación antes de llamar. Asterisk también debe controlar quién puede marcar.</p>
        </section>
    </div>
</div>

@vite('resources/js/softphone-webrtc.js')
@endsection
