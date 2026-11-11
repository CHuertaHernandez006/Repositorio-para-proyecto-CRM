@extends('layouts.app')

@section('title', 'Teléfono WebRTC - COMICenter')
@section('header-title', 'Teléfono WebRTC')

@section('content')
<style>
  /* COMICenter Softphone 2.0: todos los estilos confinados al componente */
  .webtel{--wt-bg:#101b2e;--wt-panel:#152239;--wt-line:#30425f;--wt-muted:#9bb1cf;--wt-text:#f4f8ff;--wt-blue:#399aff;--wt-cyan:#55d0ff;--wt-green:#22cc91;--wt-red:#f14f6f;max-width:1440px;margin:0 auto;color:var(--wt-text);font-family:inherit}
  .webtel *{box-sizing:border-box}.webtel [hidden]{display:none!important}
  .webtel button,.webtel input{font:inherit}.webtel button{cursor:pointer}.webtel button:disabled{cursor:not-allowed;opacity:.38;filter:saturate(.65)}
  .webtel button:focus-visible,.webtel input:focus-visible,.webtel summary:focus-visible{outline:2px solid var(--wt-cyan);outline-offset:3px}
  .webtel svg.wt-icon{width:20px;height:20px;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;fill:none;flex-shrink:0}
  .webtel .wt-hero{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:0 0 18px;padding:5px 0 7px}
  .webtel .wt-brand{display:flex;align-items:center;gap:15px;min-width:0}
  .webtel .wt-brand-mark{display:grid;place-items:center;width:55px;height:55px;border:1px solid #286ca1;border-radius:16px;background:linear-gradient(140deg,#164f8b,#152f58);box-shadow:0 12px 36px #0a5fba30;color:#8bdcff}
  .webtel .wt-brand-mark svg{width:28px;height:28px}.webtel h1{font-weight:800;font-size:clamp(21px,2.1vw,30px);letter-spacing:-.045em;line-height:1.12;margin:0}
  .webtel .wt-subtitle{margin:7px 0 0;color:var(--wt-muted);font-size:13px}
  .webtel .wt-registration{display:inline-flex;align-items:center;gap:9px;padding:11px 15px;border:1px solid #3b546f;border-radius:999px;background:#1a2a44;color:#d5e3f7;font-size:12px;font-weight:750;white-space:nowrap}
  .webtel .wt-pulse-dot{width:9px;height:9px;border-radius:50%;background:#6f829d;box-shadow:0 0 0 4px #6f829d20}
  .webtel[data-sip-estado='registrada'] .wt-pulse-dot{background:#24d89b;box-shadow:0 0 0 4px #24d89b20}
  .webtel[data-sip-estado='conectando'] .wt-pulse-dot{background:#f5c86b;box-shadow:0 0 0 4px #f5c86b20}
  .webtel .webtel-status{display:flex;align-items:center;min-height:47px;margin:0 0 17px;padding:12px 15px;border-radius:12px;border:1px solid #2d5a79;background:#102c40;color:#8edaff;font-size:13px;line-height:1.45}
  .webtel .webtel-status.webtel-error{border-color:#9a3f5b;background:#392133;color:#ffc0ce}
  .webtel .wt-layout{display:grid;grid-template-columns:minmax(335px,.93fr) minmax(380px,1.08fr);gap:18px;align-items:start}
  .webtel .wt-left{display:grid;gap:16px;min-width:0}
  .webtel .wt-card{background:linear-gradient(160deg,#19263f 0%,#111d32 100%);border:1px solid var(--wt-line);border-radius:20px;box-shadow:0 14px 42px #00000024;min-width:0;overflow:hidden}
  .webtel .wt-pad{padding:20px 21px}.webtel .wt-card-title{display:flex;align-items:center;gap:9px;margin:0;font-size:15px;font-weight:800;color:#f6faff;letter-spacing:-.012em}
  .webtel .wt-card-title .wt-small-icon{display:grid;place-items:center;width:31px;height:31px;background:#163f71;border-radius:10px;color:#75bdff}
  .webtel .wt-card-title svg{width:17px;height:17px}
  .webtel .wt-connect{position:relative;background:radial-gradient(circle at 5% 0%,#174c454c,transparent 55%),linear-gradient(145deg,#192c40,#152139)}
  .webtel .wt-connect-main{display:flex;align-items:center;gap:14px}
  .webtel .wt-connect-icon{display:grid;place-items:center;flex-shrink:0;width:53px;height:53px;border-radius:17px;background:linear-gradient(145deg,#176f62,#124644);color:#7ef3cd;box-shadow:0 0 0 1px #28b89240}
  .webtel .wt-connect-icon svg{width:27px;height:27px}
  .webtel .wt-connect-title{margin:0;font-size:18px;font-weight:800;line-height:1.3}.webtel .wt-connect-caption{margin:3px 0 0;font-size:12px;color:#a9c5d5}
  .webtel .wt-connect-pill{margin-left:auto;display:inline-flex;align-items:center;gap:6px;border-radius:999px;border:1px solid #267c64;background:#0e4038;padding:6px 10px;color:#9cf2cf;font-weight:700;font-size:11px;white-space:nowrap}
  .webtel .wt-connect-details{display:grid;gap:8px;margin-top:18px;padding-top:15px;border-top:1px solid #344a5a}
  .webtel .wt-detail{display:flex;align-items:center;gap:8px;min-width:0;font-size:12px;color:#adbed4}
  .webtel .wt-detail strong{color:#e9f1fd;font-weight:750}.webtel .wt-detail code{font:inherit;color:#91d3ff;overflow-wrap:anywhere}
  .webtel .wt-connect-bottom{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-top:14px}
  .webtel .wt-inline-btn{border:1px solid #435771;border-radius:10px;background:#223551;color:#e4f1ff;padding:9px 12px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:7px}
  .webtel .wt-inline-btn svg{width:15px;height:15px}.webtel .wt-inline-btn:hover:not(:disabled){background:#2e4463}
  .webtel .wt-inline-btn.wt-success-btn{border-color:#1b8971;background:#0c7865;color:white}
  .webtel .wt-inline-btn.wt-sound-active{border-color:#3291c9;color:#90deff;background:#133954}
  .webtel .wt-directory-head{display:flex;align-items:center;justify-content:space-between;gap:9px}
  .webtel .wt-count{font-size:12px;color:#91aac8;white-space:nowrap}
  .webtel .wt-search-wrap{position:relative;margin-top:17px}
  .webtel .wt-search-wrap svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#8ba4c4}
  .webtel .wt-input{width:100%;min-height:44px;border:1px solid #355170;border-radius:12px;background:#0e1a2d;color:#eff6ff;padding:12px 13px;font-size:14px;outline:none}
  .webtel .wt-input::placeholder{color:#7187a5}.webtel .wt-search-wrap .wt-input{padding-left:43px}
  .webtel .webtel-client-list{display:grid;gap:9px;margin-top:11px;max-height:346px;overflow:auto;padding-right:4px;scrollbar-color:#365070 transparent;scrollbar-width:thin}
  .webtel .webtel-client{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px;border:1px solid #2c4160;border-radius:13px;background:#102039}
  .webtel .webtel-client-info{flex:1;min-width:0;display:grid;gap:3px}.webtel .webtel-client-name{font-weight:750;color:#eff5ff;font-size:12px;overflow-wrap:anywhere}
  .webtel .webtel-client-number{color:#8faed0;font-size:11px;overflow-wrap:anywhere}
  .webtel .webtel-client-buttons{display:flex;flex-direction:column;gap:5px;align-items:stretch}
  .webtel .webtel-client-call{display:inline-flex;align-items:center;gap:5px;justify-content:center;border:1px solid #158565;background:#107b5b;color:#fff;border-radius:9px;padding:9px 11px;font-size:11px;font-weight:800;white-space:nowrap}
  .webtel .webtel-client-call:hover:not(:disabled){background:#0d946c}.webtel .webtel-client-call svg{width:13px;height:13px}
  .webtel .webtel-client-empty{text-align:center;padding:22px 8px;color:#a0b1cc;font-size:12px}
  .webtel .wt-more{display:block;margin:13px auto 0}
  .webtel .wt-call-panel{position:relative;background:radial-gradient(circle at 50% 23%,#1a46804b,transparent 53%),linear-gradient(150deg,#182741 0%,#101b2e 90%)}
  .webtel .wt-call-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 0 15px}
  .webtel .wt-call-state{display:inline-flex;align-items:center;gap:8px;border-radius:999px;background:#1b4071;border:1px solid #2e70bc;padding:9px 13px;color:#b9ddff;font-size:12px;font-weight:800}
  .webtel .wt-call-state[data-tone='connected']{background:#104d41;border-color:#238c72;color:#8cf0c9}
  .webtel .wt-call-state[data-tone='incoming']{background:#214a76;border-color:#409ff0;color:#a5e3ff;animation:wtGlow 1.8s ease-in-out infinite}
  .webtel .wt-call-state[data-tone='error']{background:#53283a;border-color:#b04462;color:#ffd2d9}
  .webtel .wt-call-clock{font-variant-numeric:tabular-nums;color:#cfe3fc;font-size:15px;font-weight:800;letter-spacing:.02em}
  .webtel .wt-call-stage{display:flex;align-items:center;flex-direction:column;justify-content:center;min-height:330px;text-align:center;padding:13px 8px 18px}
  .webtel .wt-avatar-shell{position:relative;display:grid;place-items:center;width:150px;height:150px;margin:0 auto 23px;border-radius:50%;background:radial-gradient(circle,#1a66c2 0%,#133a75 62%,#102e59 100%);border:1px solid #3676c5;box-shadow:0 0 0 13px #2475cf17,0 0 0 29px #2475cf0d,0 14px 50px #0c54c431}
  .webtel .wt-avatar-shell svg{width:69px;height:69px;stroke-width:1.3;color:#d6eaff}
  .webtel[data-call-phase='dialing'] .wt-avatar-shell,.webtel[data-call-phase='incoming'] .wt-avatar-shell{animation:wtRingPulse 2s infinite}
  .webtel .wt-call-title{font-size:clamp(20px,2.25vw,29px);font-weight:850;letter-spacing:-.045em;line-height:1.18;margin:0;max-width:100%;overflow-wrap:anywhere}
  .webtel .wt-call-number{font-size:15px;color:#a2bddc;margin:10px 0 0;overflow-wrap:anywhere}
  .webtel .wt-call-description{margin:9px 0 0;max-width:335px;color:#a9bad2;font-size:12px;line-height:1.5}
  .webtel .wt-wave{height:38px;display:flex;align-items:center;justify-content:center;gap:3px;margin:19px 0 4px;opacity:.4}
  .webtel .wt-wave i{display:block;width:4px;height:var(--bar);background:linear-gradient(#60c3ff,#277bff);border-radius:10px;animation:wtWave 1.25s ease-in-out infinite;animation-delay:var(--delay)}
  .webtel[data-call-phase='connected'] .wt-wave,.webtel[data-call-phase='dialing'] .wt-wave,.webtel[data-call-phase='incoming'] .wt-wave{opacity:1}
  .webtel[data-call-phase='idle'] .wt-wave i,.webtel[data-call-phase='ended'] .wt-wave i{animation:none;height:4px}
  .webtel .wt-call-controls{display:flex;justify-content:center;gap:clamp(12px,2vw,24px);flex-wrap:wrap;margin:2px auto 22px}
  .webtel .wt-control{display:grid;justify-items:center;gap:8px;min-width:70px;color:#d5e7ff;font-size:11px;font-weight:650}
  .webtel .wt-control button{width:62px;height:62px;border-radius:50%;border:1px solid #3c5070;background:#223550;color:#ecf5ff;display:grid;place-items:center;box-shadow:0 10px 24px #00000025}
  .webtel .wt-control button svg{width:26px;height:26px}.webtel .wt-control button:hover:not(:disabled){background:#30527a;transform:translateY(-1px)}
  .webtel .wt-control button.wt-hangup{background:#cf294f;border-color:#e65070;color:white;box-shadow:0 10px 28px #d3275555}
  .webtel .wt-control button.wt-answer{background:#0eac7d;border-color:#34deab;color:white;box-shadow:0 10px 26px #0c9c7550}
  .webtel .wt-control button[aria-pressed='true']{background:#175686;border-color:#55b4f2;color:white}
  .webtel .wt-dialer{border-top:1px solid #2d4160;background:#101d33a3;padding:15px 20px 18px}
  .webtel .wt-dialer summary{list-style:none;display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:800;color:#b6d9ff;cursor:pointer;user-select:none}
  .webtel .wt-dialer summary::-webkit-details-marker{display:none}.webtel .wt-dialer summary svg{width:16px;height:16px;transition:transform .2s}
  .webtel .wt-dialer[open] summary svg{transform:rotate(180deg)}
  .webtel .wt-dialer-content{margin-top:14px}.webtel .wt-dialer-head{display:flex;gap:9px}
  .webtel .wt-dialer-head .wt-input{flex:1;text-align:center;font-size:17px;font-weight:750;font-variant-numeric:tabular-nums;letter-spacing:.05em}
  .webtel .wt-dialer-call{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:0 16px;border:1px solid #20a983;background:#0d936c;color:#fff;border-radius:11px;font-size:12px;font-weight:800;white-space:nowrap}
  .webtel .wt-dialer-call svg{width:17px;height:17px}.webtel .wt-keypad{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;max-width:306px;margin:14px auto 0}
  .webtel .wt-keypad button{min-height:43px;border:1px solid #35516f;background:#1c304d;color:#e5f0ff;border-radius:11px;font-size:17px;font-weight:700}
  .webtel .wt-keypad button:hover{background:#2b5078}
  .webtel .wt-bottom{display:flex;align-items:center;gap:10px;justify-content:space-between;flex-wrap:wrap;padding:16px 21px 18px;border-top:1px solid #293c56}
  .webtel .wt-volume{display:flex;align-items:center;gap:9px;color:#a7c7e3;font-size:11px}.webtel .wt-volume svg{width:18px;height:18px}
  .webtel .wt-volume input{accent-color:#359cfb;width:min(155px,19vw)}
  .webtel .wt-bottom-note{font-size:11px;color:#8ba3c3;line-height:1.45}
  .webtel .wt-audio-settings{padding:0 21px 12px;font-size:11px;color:#93a9c8}
  .webtel .wt-audio-settings summary{cursor:pointer}.webtel .wt-audio-settings audio{width:100%;margin-top:10px;height:37px}
  @keyframes wtRingPulse{0%,100%{box-shadow:0 0 0 13px #2475cf17,0 0 0 29px #2475cf0d,0 14px 50px #0c54c431}50%{box-shadow:0 0 0 17px #2475cf26,0 0 0 36px #2475cf0c,0 14px 55px #1671ee35}}
  @keyframes wtWave{0%,100%{height:4px}50%{height:var(--bar)}}@keyframes wtGlow{50%{box-shadow:0 0 0 7px #3695fd1e}}
  @media (max-width:1060px){.webtel .wt-layout{grid-template-columns:1fr}.webtel .wt-call-panel{order:-1}.webtel .wt-call-stage{min-height:265px}.webtel .wt-left{grid-template-columns:1fr 1.15fr;align-items:start}.webtel .wt-connect{height:100%}}
  @media (max-width:710px){.webtel .wt-hero{align-items:flex-start;flex-wrap:wrap}.webtel .wt-registration{font-size:11px}.webtel .wt-left{grid-template-columns:1fr}.webtel .wt-pad{padding:16px}.webtel .wt-call-stage{min-height:258px}.webtel .wt-avatar-shell{width:120px;height:120px;margin-bottom:17px}.webtel .wt-avatar-shell svg{width:55px;height:55px}.webtel .wt-control button{width:54px;height:54px}.webtel .wt-control{min-width:62px}.webtel .wt-call-controls{gap:12px}.webtel .wt-bottom{padding:13px 16px}.webtel .wt-volume input{width:125px}}
  @media (prefers-reduced-motion: reduce){.webtel *{animation:none!important;transition:none!important}}
</style>

<div class="webtel" id="webtel-root"
     data-enabled="{{ $habilitadoWebRtc ? '1' : '0' }}"
     data-extension="{{ $extensionWebRtc }}"
     data-wss="{{ $wssWebRtc }}"
     data-domain="{{ $dominioWebRtc }}"
     data-internos="{{ implode(',', config('webrtc.internal_extensions', [])) }}"
     data-credentials-url="{{ route('telefono.credenciales') }}"
     data-clients-url="{{ route('telefono.clientes-disponibles') }}"
     data-validate-url="{{ route('telefono.validar-destino') }}"
     data-incoming-url="{{ route('telefono.buscar-entrante') }}"
     data-sip-estado="sin-registro" data-call-phase="idle">
  {{-- Iconos originales SVG; sin bibliotecas ni recursos externos. --}}
  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
    <symbol id="wt-ico-phone" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.7 2.8a2 2 0 0 1-.45 2.11L8.1 9.91a16 16 0 0 0 6 6l1.27-1.26a2 2 0 0 1 2.11-.45c.9.33 1.84.57 2.8.7A2 2 0 0 1 22 16.92Z"/></symbol>
    <symbol id="wt-ico-hangup" viewBox="0 0 24 24"><path d="M3.5 15.5c4.9-5.1 12.1-5.1 17 0"/><path d="M3.5 15.5 2 19l4.5 2 2-4.5-2-2M20.5 15.5 22 19l-4.5 2-2-4.5 2-2"/></symbol>
    <symbol id="wt-ico-user" viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="4"/><path d="M4.5 21v-2.2c0-4.2 3.5-6.1 7.5-6.1s7.5 1.9 7.5 6.1V21Z"/></symbol>
    <symbol id="wt-ico-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M2 20v-2c0-3.1 2.8-4.8 7-4.8s7 1.7 7 4.8v2M17.5 4.7a3 3 0 0 1 0 5.8M18 14c2.5.6 4 2.1 4 4v2"/></symbol>
    <symbol id="wt-ico-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7.2"/><path d="m16 16 5 5"/></symbol>
    <symbol id="wt-ico-check" viewBox="0 0 24 24"><path d="m4 12 5 5L20 6"/></symbol>
    <symbol id="wt-ico-refresh" viewBox="0 0 24 24"><path d="M20 7V3l-4 4M4 17v4l4-4M20 7a8 8 0 0 0-14-2M4 17a8 8 0 0 0 14 2"/></symbol>
    <symbol id="wt-ico-headset" viewBox="0 0 24 24"><path d="M4 13v-2a8 8 0 0 1 16 0v2M4 13H2v6h5v-6H4Zm16 0h2v6h-5v-6h3ZM20 19a5 5 0 0 1-5 3h-3"/></symbol>
    <symbol id="wt-ico-sound" viewBox="0 0 24 24"><path d="M3 9v6h4l5 4V5L7 9H3ZM16 9a5 5 0 0 1 0 6M19 6a9 9 0 0 1 0 12"/></symbol>
    <symbol id="wt-ico-mute" viewBox="0 0 24 24"><path d="M3 3l18 18M10 10v3a2 2 0 0 0 3.2 1.6M14 10V6a2 2 0 0 0-4 0v1M5 11a7 7 0 0 0 12 5M19 11a7 7 0 0 1-.5 2.6M12 18v4M8 22h8"/></symbol>
    <symbol id="wt-ico-mic" viewBox="0 0 24 24"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v4M8 22h8"/></symbol>
    <symbol id="wt-ico-pause" viewBox="0 0 24 24"><path d="M8 5v14M16 5v14"/></symbol>
    <symbol id="wt-ico-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 3v18M15 3v18M3 9h18M3 15h18"/></symbol>
    <symbol id="wt-ico-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
  </svg>

  <div class="wt-hero">
    <div class="wt-brand">
      <span class="wt-brand-mark"><svg class="wt-icon"><use href="#wt-ico-headset"/></svg></span>
      <div><h1>Teléfono COMICenter</h1><p class="wt-subtitle">Habla con tus clientes sin salir del CRM.</p></div>
    </div>
    <div class="wt-registration" id="webtel-register-badge"><span class="wt-pulse-dot"></span><span id="webtel-register-label">Extensión {{ $extensionWebRtc ?: 'sin asignar' }} · Conectando</span></div>
  </div>
  <div id="webtel-status" class="webtel-status" role="status" aria-live="polite">Inicializando teléfono…</div>

  <div class="wt-layout">
    <div class="wt-left">
      <section class="wt-card wt-pad wt-connect" aria-labelledby="webtel-connect-title">
        <div class="wt-connect-main">
          <span class="wt-connect-icon"><svg class="wt-icon"><use href="#wt-ico-headset"/></svg></span>
          <div><h2 class="wt-connect-title" id="webtel-connect-title">Conexión telefónica</h2><p class="wt-connect-caption" id="webtel-connect-state">Preparando extensión...</p></div>
          <span class="wt-connect-pill"><span class="wt-pulse-dot"></span> <span id="webtel-connect-pill">Sin registro</span></span>
        </div>
        <div class="wt-connect-details">
          <div class="wt-detail"><svg class="wt-icon"><use href="#wt-ico-phone"/></svg>Extensión <strong id="webtel-extension">{{ $extensionWebRtc ?: 'Sin asignar' }}</strong></div>
          <div class="wt-detail"><svg class="wt-icon"><use href="#wt-ico-check"/></svg>Servidor WSS: <code>{{ $wssWebRtc ?: 'No configurado' }}</code></div>
        </div>
        <div class="wt-connect-bottom">
          <button type="button" id="webtel-connect" class="wt-inline-btn wt-success-btn"><svg class="wt-icon"><use href="#wt-ico-refresh"/></svg>Reintentar conexión</button>
          <button type="button" id="webtel-disconnect" class="wt-inline-btn" disabled>Desconectar</button>
          <button type="button" id="webtel-sound-toggle" class="wt-inline-btn wt-sound-active" aria-pressed="true" title="Activar o silenciar los sonidos del teléfono"><svg class="wt-icon"><use href="#wt-ico-sound"/></svg><span id="webtel-sound-label">Sonidos activados</span></button>
        </div>
      </section>

      <section class="wt-card wt-pad" aria-labelledby="webtel-directory-title">
        <div class="wt-directory-head"><h2 class="wt-card-title" id="webtel-directory-title"><span class="wt-small-icon"><svg class="wt-icon"><use href="#wt-ico-users"/></svg></span>Mis clientes asignados</h2><span class="wt-count" id="webtel-clients-count">Cargando…</span></div>
        <div class="wt-search-wrap"><svg class="wt-icon"><use href="#wt-ico-search"/></svg><input class="wt-input" id="webtel-clients-search" type="search" maxlength="100" placeholder="Buscar por nombre o teléfono…" aria-label="Buscar clientes" autocomplete="off"></div>
        <div class="webtel-client-list" id="webtel-clients-list" aria-live="polite"><div class="webtel-client-empty">Cargando clientes autorizados…</div></div>
        <button type="button" class="wt-inline-btn wt-more" id="webtel-clients-more" hidden>Cargar más clientes</button>
      </section>
    </div>

    <section class="wt-card wt-call-panel" aria-labelledby="webtel-call-title">
      <div class="wt-pad">
        <div class="wt-call-top">
          <span class="wt-call-state" id="webtel-call-state" data-tone="idle"><svg class="wt-icon"><use href="#wt-ico-phone"/></svg><span id="webtel-call-state-label">Listo para llamar</span></span>
          <span class="wt-call-clock" id="webtel-timer" aria-label="Duración de la llamada">00:00</span>
        </div>
        <div class="wt-call-stage" id="webtel-call-stage" aria-live="off">
          <div class="wt-avatar-shell"><svg class="wt-icon"><use href="#wt-ico-user"/></svg></div>
          <h2 class="wt-call-title" id="webtel-call-title">Tu teléfono está listo</h2>
          <p class="wt-call-number" id="webtel-call-number">Extensión {{ $extensionWebRtc ?: 'sin asignar' }}</p>
          <p class="wt-call-description" id="webtel-call-label" aria-live="polite">Elige un cliente o marca un número para iniciar una llamada.</p>
          <div class="wt-wave" aria-hidden="true">
            @foreach([6,10,14,24,12,29,17,35,24,14,31,18,26,11,32,19,13,26,33,15,22,8,16,24,12,7] as $i => $alto)
              <i style="--bar:{{ $alto }}px;--delay:{{ ($i % 7) * -0.15 }}s"></i>
            @endforeach
          </div>
        </div>
        <div class="wt-call-controls" aria-label="Controles de llamada">
          <div class="wt-control" id="webtel-answer-wrap" hidden><button type="button" id="webtel-answer" class="wt-answer" disabled aria-label="Contestar llamada"><svg class="wt-icon"><use href="#wt-ico-phone"/></svg></button><span>Contestar</span></div>
          <div class="wt-control" id="webtel-reject-wrap" hidden><button type="button" id="webtel-reject" class="wt-hangup" disabled aria-label="Rechazar llamada"><svg class="wt-icon"><use href="#wt-ico-hangup"/></svg></button><span>Rechazar</span></div>
          <div class="wt-control" id="webtel-mute-wrap" hidden><button type="button" id="webtel-mute" disabled aria-label="Silenciar micrófono" aria-pressed="false"><svg class="wt-icon"><use href="#wt-ico-mic"/></svg></button><span id="webtel-mute-label">Silenciar</span></div>
          <div class="wt-control" id="webtel-hold-wrap" hidden><button type="button" id="webtel-hold" disabled aria-label="Poner llamada en espera" aria-pressed="false"><svg class="wt-icon"><use href="#wt-ico-pause"/></svg></button><span id="webtel-hold-label">Espera</span></div>
          <div class="wt-control" id="webtel-hangup-wrap" hidden><button type="button" id="webtel-hangup" class="wt-hangup" disabled aria-label="Colgar llamada"><svg class="wt-icon"><use href="#wt-ico-hangup"/></svg></button><span>Colgar</span></div>
          <div class="wt-control" id="webtel-keypad-wrap"><button type="button" id="webtel-keypad-toggle" aria-label="Mostrar u ocultar teclado"><svg class="wt-icon"><use href="#wt-ico-grid"/></svg></button><span>Teclado</span></div>
        </div>
      </div>
      <details class="wt-dialer" id="webtel-dialer" open>
        <summary>Marcación manual / teclado DTMF<svg class="wt-icon"><use href="#wt-ico-chevron"/></svg></summary>
        <div class="wt-dialer-content">
          <div class="wt-dialer-head"><input class="wt-input" id="webtel-destination" inputmode="tel" autocomplete="off" placeholder="Teléfono o extensión habilitada" aria-label="Teléfono o extensión para llamar" value="{{ request('telefono', '') }}"><button type="button" class="wt-dialer-call" id="webtel-call" disabled><svg class="wt-icon"><use href="#wt-ico-phone"/></svg>Llamar</button></div>
          <div class="wt-keypad" aria-label="Teclado telefónico">
            @foreach(['1','2','3','4','5','6','7','8','9','*','0','#'] as $digito)
              <button type="button" data-webtel-digit="{{ $digito }}" aria-label="Tecla {{ $digito }}">{{ $digito }}</button>
            @endforeach
          </div>
        </div>
      </details>
      <div class="wt-bottom">
        <div class="wt-volume"><svg class="wt-icon"><use href="#wt-ico-sound"/></svg><label for="webtel-volume">Volumen llamada</label><input id="webtel-volume" type="range" min="0" max="100" value="80" step="5"><output id="webtel-volume-value" for="webtel-volume">80%</output></div>
        <span class="wt-bottom-note">Conexión segura WSS · SIP.js</span>
      </div>
      <details class="wt-audio-settings"><summary>Opciones de audio</summary><audio id="webtel-remote-audio" autoplay playsinline controls aria-label="Audio remoto de la llamada"></audio></details>
    </section>
  </div>
</div>

@vite('resources/js/softphone-webrtc.js')
@endsection
