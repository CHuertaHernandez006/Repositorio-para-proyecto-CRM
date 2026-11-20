@extends('layouts.app')

@section('title', 'Prueba Twilio Voice — COMICenter')
@section('header-title', 'Piloto Twilio · Llamadas externas')

@section('content')
<style>
    .tw-pilot{--edge:#30445f;--panel:#17243b;--fg:#edf5ff;--muted:#acbfda;color:var(--fg);max-width:940px;margin:auto;font-family:inherit}
    .tw-pilot *{box-sizing:border-box}
    .tw-pilot .box{background:linear-gradient(160deg,#18283f,#101b2e);border:1px solid var(--edge);border-radius:18px;padding:24px;margin-bottom:18px}
    .tw-pilot h1{font-size:25px;font-weight:800;margin:0 0 7px}
    .tw-pilot h2{font-size:16px;margin:0 0 12px}
    .tw-pilot p{color:var(--muted);font-size:13px;line-height:1.6;margin:8px 0}
    .tw-pilot a{color:#74caff}
    .tw-pilot label{display:block;color:#c5d8ed;font-weight:700;font-size:12px;margin:12px 0 6px}
    .tw-pilot input{width:100%;padding:13px 14px;background:#0e192b;border:1px solid #405b7e;color:white;border-radius:11px;font:inherit}
    .tw-pilot button{padding:12px 16px;color:white;background:#116d98;border:1px solid #319bc7;border-radius:11px;font-size:13px;font-weight:700;cursor:pointer}
    .tw-pilot button[disabled]{opacity:.44;cursor:not-allowed}
    .tw-pilot .actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
    .tw-pilot .hangup{background:#b32348;border-color:#ee5173}
    .tw-pilot .muted{color:#b8cfe8;font-size:12px}
    .tw-pilot .status{padding:13px 15px;border-radius:11px;border:1px solid #306d7e;background:#103346;color:#9ce8ef;margin-top:15px;font-size:13px}
    .tw-pilot .status[data-error='1']{background:#382030;border-color:#9e3a5a;color:#ffc3d0}
    .tw-pilot .notes{display:grid;gap:8px;margin-top:18px}
    .tw-pilot .notes p{margin:0}
    @media(max-width:600px){.tw-pilot .box{padding:16px}}
</style>
<div class="tw-pilot" id="twilio-piloto"
    data-enabled="{{ $twilioPilotoActivo ? '1' : '0' }}"
    data-token-url="{{ route('telefono.twilio.token') }}"
    data-prepare-url="{{ route('telefono.twilio.preparar') }}"
    data-check-base-url="{{ url('/telefono/twilio/intentos') }}">
    <section class="box">
        <h1>Twilio Voice · Prueba controlada</h1>
        <p>Llamadas externas por Twilio Programmable Voice. El teléfono SIP.js/Asterisk actual se mantiene sin cambios.</p>
        <p><a href="{{ route('telefono.index') }}">Regresar al teléfono SIP.js</a></p>
        <div id="twilio-status" class="status" role="status" aria-live="polite">Preparado para iniciar.</div>
    </section>
    <section class="box">
        <h2>Llamada externa</h2>
        <form id="twilio-form">
            <label for="twilio-destination">Número mexicano de un cliente autorizado y habilitado para la prueba</label>
            <input type="tel" id="twilio-destination" name="telefono" maxlength="30"
                placeholder="10 dígitos o +52…" autocomplete="off" required>
            <div class="actions">
                <button id="twilio-call" type="submit">Llamar con Twilio</button>
                <button id="twilio-hangup" type="button" class="hangup" disabled>Colgar</button>
                <button id="twilio-mute" type="button" disabled>Silenciar micrófono</button>
            </div>
        </form>
        <div class="status" id="twilio-provider-status" role="status" aria-live="polite">Sin llamada activa.</div>
        <label for="twilio-dtmf">Tonos DTMF durante una llamada</label>
        <div class="actions" style="margin-top:4px">
            <input type="text" id="twilio-dtmf" style="max-width:200px" maxlength="16" placeholder="123#" autocomplete="off">
            <button type="button" id="twilio-digits" disabled>Enviar DTMF</button>
        </div>
    </section>
    <section class="box">
        <h2>Alcance del piloto</h2>
        <div class="notes">
            <p>Este modo es solamente para llamadas salientes. Las entrantes y extensiones internas permanecen en Asterisk.</p>
            <p>El CRM valida cada número y guarda el historial; los callbacks de Twilio confirman los estados finales. No se activan grabaciones en esta fase.</p>
            <p>Los resultados pueden variar entre navegadores. Probar audio bidireccional, llamada consecutiva y desconexión normal antes de habilitar el modo a operarios.</p>
        </div>
    </section>
</div>
@vite('resources/js/twilio-softphone-piloto.js')
@endsection
