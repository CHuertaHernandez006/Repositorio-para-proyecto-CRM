/*
 * COMICenter | Telefono flotante (v1)
 * No modifica SIP.js ni duplica credenciales. Usa la ruta Laravel existente
 * /telefono?flotante=1 en un iframe del MISMO origen.
 */
(() => {
    'use strict';

    // La pagina embebida no debe intentar abrir otra copia del telefono.
    if (window.self !== window.top) return;

    const phonePath = '/telefono';
    const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
    const isPhonePage = currentPath === phonePath;

    const isPhoneLink = (anchor) => {
        if (!anchor || anchor.tagName !== 'A') return false;
        try {
            const target = new URL(anchor.href, window.location.href);
            return target.origin === window.location.origin
                && (target.pathname.replace(/\/+$/, '') || '/') === phonePath;
        } catch (_) {
            return false;
        }
    };

    const styles = `
      .cfp-menu-link{display:flex!important;align-items:center;gap:9px;width:100%;max-width:100%;white-space:normal}
      .cfp-menu-link .cfp-menu-icon{width:19px;height:19px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
      .cfp-menu-link .cfp-menu-text{font:inherit;color:inherit;overflow-wrap:anywhere}
      #cfp-window{--cfp-ink:#eaf2ff;--cfp-line:#304765;position:fixed;z-index:2147483000;
        right:18px;bottom:18px;width:min(1100px,calc(100vw - 36px));height:min(790px,calc(100dvh - 36px));
        background:#101b2e;border:1px solid #395675;border-radius:17px;box-shadow:0 22px 95px #000a;
        overflow:hidden;color:var(--cfp-ink);font:14px/1.4 system-ui,-apple-system,'Segoe UI',sans-serif;
        display:flex;flex-direction:column;isolation:isolate}
      #cfp-window[hidden]{display:none!important}
      #cfp-window.cfp-maximized{inset:8px!important;width:calc(100vw - 16px)!important;height:calc(100dvh - 16px)!important;right:auto!important;bottom:auto!important;border-radius:12px}
      #cfp-bar{display:flex;align-items:center;gap:9px;min-height:54px;padding:8px 12px 8px 15px;
        background:linear-gradient(110deg,#132940,#15223b);border-bottom:1px solid var(--cfp-line);
        cursor:grab;touch-action:none;user-select:none}
      #cfp-bar:active{cursor:grabbing}
      .cfp-phone-dot{width:9px;height:9px;border-radius:50%;background:#6996bc;box-shadow:0 0 0 4px #64a6da1a;flex:none}
      .cfp-phone-dot.cfp-online{background:#2bd6a3;box-shadow:0 0 0 4px #2bd6a329}
      .cfp-phone-dot.cfp-busy{background:#64b4ff;box-shadow:0 0 0 4px #64b4ff30}
      .cfp-title-area{min-width:0;display:flex;flex-direction:column;gap:2px;flex:1}
      .cfp-title{font-size:13px;font-weight:800;letter-spacing:.01em;color:#f1f7ff}
      .cfp-subtitle{font-size:11px;color:#a9c4e1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
      .cfp-bar-buttons{display:flex;gap:5px;align-items:center}
      .cfp-bar-button{width:34px;height:32px;background:#203752;border:1px solid #405b7a;border-radius:8px;
        color:#e3f2ff;display:grid;place-items:center;cursor:pointer;font-size:19px;line-height:1;padding:0;font-family:inherit}
      .cfp-bar-button:hover{background:#305074}.cfp-bar-button:focus-visible{outline:2px solid #5bbbfa;outline-offset:2px}
      .cfp-bar-button.cfp-close:hover{background:#922c48;border-color:#ce4a64;color:white}
      #cfp-body{flex:1;min-height:0;overflow:hidden;background:#101827}
      #cfp-frame{width:100%;height:100%;border:0;display:block;background:#101827}
      #cfp-window.cfp-minimized{height:54px!important;max-height:54px!important;width:min(340px,calc(100vw - 36px))!important}
      #cfp-window.cfp-minimized #cfp-body{height:0;min-height:0;flex:0 1 0;overflow:hidden}
      #cfp-window.cfp-minimized #cfp-bar{border-bottom:0}
      @media(max-width:620px){#cfp-window{inset:8px!important;width:calc(100vw - 16px)!important;height:calc(100dvh - 16px)!important;right:auto!important;bottom:auto!important;border-radius:12px}
        #cfp-window.cfp-minimized{inset:auto 8px 8px auto!important;width:min(320px,calc(100vw - 16px))!important;height:54px!important}
        .cfp-bar-button{width:37px;height:35px}}
    `;

    const style = document.createElement('style');
    style.id = 'cfp-style';
    style.textContent = styles;
    document.head.appendChild(style);

    // Conserva las clases originales del sidebar, pero repara el texto mojibake
    // y quita la etiqueta "Pruebas". No modifica otros enlaces del CRM.
    function arreglarBotones() {
        document.querySelectorAll('a[href]').forEach((link) => {
            if (!isPhoneLink(link) || link.dataset.cfpPrepared === '1') return;
            link.dataset.cfpPrepared = '1';
            link.title = 'Abrir teléfono flotante';
            link.setAttribute('aria-label', 'Abrir teléfono WebRTC');
            link.classList.add('cfp-menu-link');
            const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('class', 'cfp-menu-icon');
            icon.setAttribute('viewBox', '0 0 24 24');
            icon.setAttribute('aria-hidden', 'true');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', 'M21 16.5v3a2 2 0 0 1-2.2 2A18 18 0 0 1 2.5 5.2 2 2 0 0 1 4.5 3h3a2 2 0 0 1 2 1.7l.5 2.5a2 2 0 0 1-.6 1.8l-1.5 1.5a14 14 0 0 0 5.6 5.6l1.5-1.5a2 2 0 0 1 1.8-.6l2.5.5a2 2 0 0 1 1.7 2Z');
            icon.appendChild(path);
            const text = document.createElement('span');
            text.className = 'cfp-menu-text';
            text.textContent = 'Teléfono WebRTC';
            link.replaceChildren(icon, text);
        });
    }

    arreglarBotones();
    const navObserver = new MutationObserver(arreglarBotones);
    navObserver.observe(document.body, { childList: true, subtree: true });

    // En la pagina completa /telefono no tiene sentido insertar un iframe igual.
    if (isPhonePage) return;

    let windowEl = null;
    let frame = null;
    let stateLabel = null;
    let statusDot = null;
    let maximizeBtn = null;
    let phoneObserver = null;

    const phoneRoot = () => {
        try { return frame?.contentDocument?.getElementById('webtel-root') || null; }
        catch (_) { return null; }
    };

    const callActive = () => {
        const root = phoneRoot();
        if (!root) return false;
        return root.dataset.llamadaEstado === 'en-curso' ||
            ['dialing', 'incoming', 'connected'].includes(root.dataset.callPhase);
    };

    const registered = () => phoneRoot()?.dataset.sipEstado === 'registrada';

    function updateStatus() {
        if (!windowEl || !stateLabel) return;
        const root = phoneRoot();
        const busy = callActive();
        const online = root?.dataset.sipEstado === 'registrada';
        stateLabel.textContent = busy ? 'Llamada activa · Mantén la ventana abierta'
            : online ? 'Extensión conectada' : root ? 'Conectando extensión…' : 'Cargando teléfono…';
        statusDot.classList.toggle('cfp-busy', busy);
        statusDot.classList.toggle('cfp-online', !busy && online);
    }

    function createShell(url) {
        windowEl = document.createElement('section');
        windowEl.id = 'cfp-window';
        windowEl.setAttribute('role', 'dialog');
        windowEl.setAttribute('aria-label', 'Teléfono WebRTC flotante');
        windowEl.innerHTML = `
          <div id="cfp-bar">
            <span class="cfp-phone-dot" aria-hidden="true"></span>
            <div class="cfp-title-area"><span class="cfp-title">Teléfono COMICenter</span><span class="cfp-subtitle">Abriendo teléfono…</span></div>
            <div class="cfp-bar-buttons">
              <button type="button" class="cfp-bar-button" data-cfp="minimize" aria-label="Minimizar teléfono" title="Minimizar (mantiene la conexión)">−</button>
              <button type="button" class="cfp-bar-button" data-cfp="maximize" aria-label="Ampliar teléfono" title="Ampliar o restaurar">□</button>
              <button type="button" class="cfp-bar-button cfp-close" data-cfp="close" aria-label="Cerrar teléfono" title="Cerrar (desconecta)">×</button>
            </div>
          </div>
          <div id="cfp-body"><iframe id="cfp-frame" title="Softphone COMICenter" allow="microphone; autoplay"></iframe></div>
        `;
        document.body.appendChild(windowEl);
        frame = windowEl.querySelector('#cfp-frame');
        stateLabel = windowEl.querySelector('.cfp-subtitle');
        statusDot = windowEl.querySelector('.cfp-phone-dot');
        maximizeBtn = windowEl.querySelector('[data-cfp="maximize"]');

        frame.addEventListener('load', () => {
            phoneObserver?.disconnect();
            const root = phoneRoot();
            if (root) {
                phoneObserver = new MutationObserver(updateStatus);
                phoneObserver.observe(root, { attributes: true, attributeFilter: ['data-sip-estado', 'data-llamada-estado', 'data-call-phase'] });
            }
            updateStatus();
        });
        frame.src = url;

        windowEl.addEventListener('click', (event) => {
            const button = event.target.closest('[data-cfp]');
            if (!button) return;
            const action = button.dataset.cfp;
            if (action === 'minimize') {
                windowEl.classList.toggle('cfp-minimized');
                button.textContent = windowEl.classList.contains('cfp-minimized') ? '+' : '−';
                button.setAttribute('aria-label', windowEl.classList.contains('cfp-minimized') ? 'Restaurar teléfono' : 'Minimizar teléfono');
            } else if (action === 'maximize') {
                windowEl.classList.remove('cfp-minimized');
                windowEl.classList.toggle('cfp-maximized');
                maximizeBtn.textContent = windowEl.classList.contains('cfp-maximized') ? '❐' : '□';
                // Restaurar anclaje inicial al salir de modo maximizado.
                ['left', 'top', 'right', 'bottom'].forEach(prop => windowEl.style.removeProperty(prop));
            } else if (action === 'close') {
                const message = callActive() ? 'Hay una llamada en curso. Cerrar cortará la comunicación. ¿Cerrar de todas formas?'
                    : registered() ? 'Cerrar desconectará tu extensión SIP. ¿Cerrar de todas formas?' : '';
                if (message && !window.confirm(message)) return;
                phoneObserver?.disconnect();
                windowEl.remove();
                windowEl = frame = stateLabel = statusDot = maximizeBtn = null;
                phoneObserver = null;
            }
        });

        // Ventana arrastrable desde la barra superior, excepto en los botones.
        const bar = windowEl.querySelector('#cfp-bar');
        let drag = null;
        bar.addEventListener('pointerdown', (event) => {
            if (event.button !== 0 || event.target.closest('button') || windowEl.classList.contains('cfp-maximized')) return;
            const rect = windowEl.getBoundingClientRect();
            drag = { x: event.clientX, y: event.clientY, left: rect.left, top: rect.top };
            bar.setPointerCapture(event.pointerId);
        });
        bar.addEventListener('pointermove', (event) => {
            if (!drag || windowEl.classList.contains('cfp-maximized')) return;
            const width = windowEl.getBoundingClientRect().width;
            const height = windowEl.getBoundingClientRect().height;
            const x = Math.max(0, Math.min(window.innerWidth - width, drag.left + event.clientX - drag.x));
            const y = Math.max(0, Math.min(window.innerHeight - height, drag.top + event.clientY - drag.y));
            windowEl.style.left = `${x}px`;
            windowEl.style.top = `${y}px`;
            windowEl.style.right = 'auto';
            windowEl.style.bottom = 'auto';
        });
        bar.addEventListener('pointerup', () => { drag = null; });
        bar.addEventListener('pointercancel', () => { drag = null; });
        updateStatus();
    }

    function openPhone(anchor) {
        if (!windowEl) {
            const url = new URL(anchor.href, window.location.origin);
            url.searchParams.set('flotante', '1');
            createShell(url.toString());
        } else {
            windowEl.classList.remove('cfp-minimized');
            const minBtn = windowEl.querySelector('[data-cfp="minimize"]');
            minBtn.textContent = '−';
            minBtn.setAttribute('aria-label', 'Minimizar teléfono');
        }
        updateStatus();
    }

    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a[href]');
        if (!isPhoneLink(anchor) || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        event.stopPropagation();
        openPhone(anchor);
    }, true);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && windowEl && !windowEl.classList.contains('cfp-minimized')) {
            windowEl.querySelector('[data-cfp="minimize"]').click();
        }
    });

    // IMPORTANTE: una navegación completa de Blade destruye el iframe.
    // Proteger llamadas en curso; minimizar NO destruye el iframe ni la sesión.
    window.addEventListener('beforeunload', (event) => {
        if (windowEl && callActive()) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
})();
