import { Web } from 'sip.js';

// Fase 1: navegador <-> Asterisk via WSS. No reemplaza AMI ni escribe el
// historial: los eventos definitivos deben venir de Asterisk (no del navegador).
const root = document.getElementById('webtel-root');

if (root) {
    const el = (name) => document.getElementById(`webtel-${name}`);
    const extension = root.dataset.extension?.trim() || '';
    const wss = root.dataset.wss?.trim() || '';
    const domain = root.dataset.domain?.trim() || '';
    const enabled = root.dataset.enabled === '1';
    const configured = enabled && /^wss:\/\//i.test(wss) && domain && /^\d{3,6}$/.test(extension);

    let phone = null;
    let registered = false;
    let connecting = false;
    let active = false;
    let incoming = false;
    let answeredAt = null;
    let timer = null;
    let muted = false;
    let held = false;

    const formatClock = (secs) => `${String(Math.floor(secs / 60)).padStart(2, '0')}:${String(secs % 60).padStart(2, '0')}`;
    const notify = (message, error = false) => {
        el('status').textContent = message;
        el('status').classList.toggle('webtel-error', error);
    };
    const label = (message) => { el('call-label').textContent = message; };

    const updateButtons = () => {
        el('connect').disabled = !configured || registered || connecting || !!phone;
        el('disconnect').disabled = !phone || active || connecting;
        el('call').disabled = !registered || active;
        el('answer').disabled = !registered || !incoming || !active;
        el('reject').disabled = !registered || !incoming || !active;
        el('hangup').disabled = !registered || !active;
        el('mute').disabled = !registered || !active || incoming;
        el('hold').disabled = !registered || !active || incoming;
        el('mute').textContent = muted ? 'Activar micrófono' : 'Silenciar';
        el('hold').textContent = held ? 'Recuperar' : 'Espera';
    };

    const clearClock = () => {
        clearInterval(timer);
        timer = null;
        answeredAt = null;
        el('timer').textContent = '00:00';
    };
    const startClock = () => {
        clearClock();
        answeredAt = Date.now();
        timer = setInterval(() => {
            if (answeredAt) el('timer').textContent = formatClock(Math.floor((Date.now() - answeredAt) / 1000));
        }, 1000);
    };

    const onCallEnd = () => {
        active = false;
        incoming = false;
        muted = false;
        held = false;
        clearClock();
        label('Llamada finalizada');
        notify(registered ? 'Extensión registrada. Lista para otra llamada.' : 'No conectado.');
        updateButtons();
    };

    if (!configured) {
        notify('Módulo pendiente: redes debe habilitar WebRTC y proporcionar WEBRTC_WSS_URL / WEBRTC_SIP_DOMAIN; el operario necesita extensión asignada.', true);
    } else if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
        notify('El navegador necesita HTTPS y permiso de micrófono para usar WebRTC.', true);
    } else {
        notify('Listo para conectar. Ingresa la contraseña SIP de la extensión de pruebas.');
    }

    el('connect').addEventListener('click', async () => {
        if (!configured || connecting || phone || !window.isSecureContext) return;
        const password = el('secret').value;
        if (!password) { notify('Ingresa la contraseña de la extensión WebRTC de pruebas.', true); return; }
        connecting = true;
        updateButtons();
        try {
            // Solicitar permiso sin conservar una pista de audio sin utilizar.
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
            stream.getTracks().forEach(track => track.stop());

            phone = new Web.SimpleUser(wss, {
                aor: `sip:${extension}@${domain}`,
                media: { remote: { audio: el('remote-audio') } },
                reconnectionAttempts: 3,
                reconnectionDelay: 4,
                userAgentOptions: {
                    authorizationUsername: extension,
                    authorizationPassword: password,
                    displayName: `COMICenter ${extension}`,
                    logBuiltinEnabled: false,
                    logConfiguration: false,
                },
                delegate: {
                    onServerConnect: () => notify('WebSocket conectado. Registrando extensión…'),
                    onServerDisconnect: () => {
                        registered = false;
                        notify('Conexión WSS perdida. Comprueba el servidor.', true);
                        updateButtons();
                    },
                    onRegistered: () => {
                        registered = true;
                        notify(`Extensión ${extension} registrada en Asterisk.`);
                        updateButtons();
                    },
                    onUnregistered: () => {
                        registered = false;
                        updateButtons();
                    },
                    onCallCreated: () => {
                        active = true;
                        updateButtons();
                    },
                    onCallReceived: () => {
                        active = true;
                        incoming = true;
                        label('Llamada entrante · número pendiente de identificación');
                        notify('Llamada entrante. Contesta o rechaza desde el CRM.');
                        updateButtons();
                    },
                    onCallAnswered: () => {
                        active = true;
                        incoming = false;
                        label('En curso');
                        notify('Llamada conectada. Audio mediante WebRTC.');
                        startClock();
                        updateButtons();
                    },
                    onCallHold: (value) => {
                        held = !!value;
                        label(held ? 'En espera' : 'En curso');
                        updateButtons();
                    },
                    onCallHangup: onCallEnd,
                },
            });
            // Nunca guardamos la contraseña en storage; eliminarla del input.
            el('secret').value = '';
            notify('Conectando al servidor WSS…');
            await phone.connect();
            await phone.register();
            registered = true;
            notify(`Extensión ${extension} registrada. Puedes llamar y recibir llamadas.`);
        } catch (error) {
            notify(`No se pudo registrar la extensión. Revisa WSS, certificado, credenciales y PJSIP. ${error?.message || ''}`, true);
            if (phone) { try { await phone.disconnect(); } catch (_) { /* solo limpieza */ } }
            phone = null;
            registered = false;
        } finally {
            connecting = false;
            updateButtons();
        }
    });

    el('disconnect').addEventListener('click', async () => {
        if (!phone || active) return;
        connecting = true;
        updateButtons();
        try {
            await phone.unregister();
            await phone.disconnect();
            notify('Extensión desconectada del navegador.');
        } catch (error) {
            notify(`No fue posible desconectar limpiamente: ${error?.message || ''}`, true);
        } finally {
            phone = null;
            registered = false;
            connecting = false;
            updateButtons();
        }
    });

    el('call').addEventListener('click', async () => {
        if (!phone || !registered || active) return;
        const rawPhone = el('destination').value.trim();
        if (!rawPhone) { notify('Escribe el teléfono del cliente.', true); return; }
        active = true; // Bloquear llamadas duplicadas durante la validación
        updateButtons();
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(root.dataset.validateUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ telefono: rawPhone }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || `Validación HTTP ${response.status}`);
            const destination = data.telefono_marcacion;
            if (!/^\d{10}$/.test(destination)) throw new Error('El backend no devolvió un teléfono válido.');
            label(`Marcando ${destination} · ${data.cliente || 'Cliente registrado'}`);
            notify('Solicitando llamada SIP desde el navegador…');
            await phone.call(`sip:${destination}@${domain}`);
        } catch (error) {
            active = false;
            label('Sin llamada activa');
            notify(`No se inició la llamada: ${error?.message || 'Error desconocido'}`, true);
        } finally {
            updateButtons();
        }
    });

    el('answer').addEventListener('click', async () => {
        if (!phone || !incoming) return;
        try { await phone.answer(); }
        catch (error) { notify(`Error al contestar: ${error?.message || ''}`, true); }
    });
    el('reject').addEventListener('click', async () => {
        if (!phone || !incoming) return;
        try { await phone.decline(); onCallEnd(); }
        catch (error) { notify(`Error al rechazar: ${error?.message || ''}`, true); }
    });
    el('hangup').addEventListener('click', async () => {
        if (!phone || !active) return;
        try { await phone.hangup(); onCallEnd(); }
        catch (error) { notify(`Error al colgar: ${error?.message || ''}`, true); }
    });
    el('mute').addEventListener('click', async () => {
        if (!phone || !active || incoming) return;
        try {
            if (muted) await phone.unmute(); else await phone.mute();
            muted = !muted;
            updateButtons();
        } catch (error) { notify(`No se pudo cambiar el micrófono: ${error?.message || ''}`, true); }
    });
    el('hold').addEventListener('click', async () => {
        if (!phone || !active || incoming) return;
        try {
            if (held) await phone.unhold(); else await phone.hold();
            held = !held;
            label(held ? 'En espera' : 'En curso');
            updateButtons();
        } catch (error) { notify(`No se pudo cambiar la espera: ${error?.message || ''}`, true); }
    });
    root.querySelectorAll('[data-webtel-digit]').forEach(button => button.addEventListener('click', async () => {
        const digit = button.dataset.webtelDigit;
        if (active && !incoming && phone) {
            try { await phone.sendDTMF(digit); }
            catch (error) { notify(`DTMF no disponible: ${error?.message || ''}`, true); }
        } else if (!active) {
            el('destination').value += digit;
        }
    }));
    window.addEventListener('beforeunload', (event) => {
        // Avisar que una navegación destruye el WebSocket y corta la llamada.
        if (active) { event.preventDefault(); event.returnValue = ''; }
    });
    updateButtons();
}
