/** COMICenter: piloto SALIENTE Twilio; no importa, inicializa ni modifica SIP.js. */
import { Device } from '@twilio/voice-sdk';

const root = document.getElementById('twilio-piloto');
if (root) {
    const el = (id) => document.getElementById(`twilio-${id}`);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const form = el('form');
    let device = null;
    let call = null;
    let currentAttempt = null;
    let poller = null;
    let pollingVersion = 0;
    let mute = false;
    let connecting = false;
    let active = false;

    const notify = (message, error = false) => {
        el('status').textContent = message;
        el('status').dataset.error = error ? '1' : '0';
    };

    function controls() {
        el('call').disabled = connecting || active || root.dataset.enabled !== '1';
        el('hangup').disabled = !call || !active;
        el('mute').disabled = !call || !active;
        el('digits').disabled = !call || !active;
        el('mute').textContent = mute ? 'Activar micrófono' : 'Silenciar micrófono';
    }

    async function api(url, payload = undefined) {
        const response = await fetch(url, {
            method: payload === undefined ? 'GET' : 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(payload === undefined ? {} : {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                }),
            },
            ...(payload === undefined ? {} : { body: JSON.stringify(payload) }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || `Error HTTP ${response.status}`);
        return data;
    }

    function cleanupCall() {
        active = false;
        connecting = false;
        mute = false;
        call = null;
        controls();
        // La desconexión del navegador NO es prueba del estado PSTN; el webhook confirma.
        if (currentAttempt) {
            const version = ++pollingVersion;
            window.setTimeout(() => {
                if (version === pollingVersion) stopPolling();
            }, 20000);
        } else {
            stopPolling();
        }
    }

    function stopPolling() {
        if (poller) clearInterval(poller);
        poller = null;
        currentAttempt = null;
        ++pollingVersion;
    }

    async function consultAttempt(version) {
        if (!currentAttempt || version !== pollingVersion) return;
        try {
            const url = `${root.dataset.checkBaseUrl}/${encodeURIComponent(currentAttempt)}`;
            const state = await api(url);
            if (version !== pollingVersion) return;
            el('provider-status').textContent = `Estado confirmado por Twilio: ${state.estado}`;
            if (state.finalizada) {
                if (!active) stopPolling();
            }
        } catch (_) { /* Fallas transitorias de consulta no terminan una llamada. */ }
    }

    function beginPolling(intent) {
        stopPolling();
        currentAttempt = intent;
        const version = ++pollingVersion;
        void consultAttempt(version);
        poller = window.setInterval(() => void consultAttempt(version), 3000);
    }

    async function prepareDevice() {
        const data = await api(root.dataset.tokenUrl, {});
        if (!data.token) throw new Error('Laravel no devolvió el Access Token.');
        if (!device) {
            device = new Device(data.token, { logLevel: 'warn' });
            device.on('error', (error) => {
                notify(`Twilio: ${error.message || 'error desconocido'}`, true);
            });
            device.on('tokenWillExpire', async () => {
                try {
                    const renewed = await api(root.dataset.tokenUrl, {});
                    await device?.updateToken(renewed.token);
                } catch (_) {
                    notify('No se pudo renovar el token. Inicia una nueva llamada para reintentar.', true);
                }
            });
            // No hacemos device.register(): se reservan las entrantes para Asterisk.
        } else {
            await device.updateToken(data.token);
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (connecting || active || root.dataset.enabled !== '1') return;
        const numero = el('destination').value.trim();
        connecting = true;
        controls();
        notify('Validando cliente y preparando Twilio...');
        el('provider-status').textContent = 'Esperando solicitud...';
        try {
            await prepareDevice();
            // Laravel verifica cliente + allowlist y crea intento temporal de un solo uso.
            const prepared = await api(root.dataset.prepareUrl, { telefono: numero });
            notify(`Marcando a ${prepared.cliente || prepared.destino} a través de Twilio...`);
            beginPolling(prepared.intento);
            // No enviar To. El destino REAL se consulta de la base de datos en el webhook.
            const connected = await device.connect({ params: { Intento: prepared.intento } });
            call = connected;
            active = true;
            connecting = false;
            controls();
            notify('Conexión del navegador establecida. Esperando respuesta del destinatario...');
            connected.on('accept', () => {
                notify('Navegador conectado a Twilio; consulta el estado del destino abajo.');
            });
            connected.on('disconnect', () => {
                notify('Conexión del navegador finalizada. Esperando estado final de Twilio.');
                cleanupCall();
            });
            connected.on('reject', () => {
                notify('Twilio rechazó la conexión.', true);
                cleanupCall();
            });
            connected.on('cancel', () => {
                notify('La solicitud fue cancelada.');
                cleanupCall();
            });
            connected.on('error', (err) => {
                notify(`Error de llamada: ${err.message || 'sin detalle'}`, true);
                cleanupCall();
            });
        } catch (error) {
            notify(`No se pudo iniciar la llamada: ${error.message || 'error desconocido'}`, true);
            // Consultar un rato si Twilio alcanzó a recibir la solicitud; no dejar un polling infinito.
            if (currentAttempt) {
                const version = pollingVersion;
                window.setTimeout(() => {
                    if (version === pollingVersion) stopPolling();
                }, 20000);
            }
            connecting = false;
            active = false;
            call = null;
            controls();
        }
    });

    el('hangup').addEventListener('click', () => {
        call?.disconnect();
        notify('Se solicitó colgar. Esperando confirmación final de Twilio.');
    });
    el('mute').addEventListener('click', () => {
        if (!call || !active) return;
        mute = !mute;
        call.mute(mute);
        controls();
    });
    el('digits').addEventListener('click', () => {
        const digits = el('dtmf').value.trim();
        if (!call || !active || !/^[0-9*#]{1,16}$/.test(digits)) {
            notify('DTMF: introduce entre 1 y 16 caracteres (0-9, * o #).', true);
            return;
        }
        try { call.sendDigits(digits); }
        catch (error) { notify(`No se pudo enviar DTMF: ${error.message}`, true); }
    });

    window.addEventListener('beforeunload', (event) => {
        if (active) { event.preventDefault(); event.returnValue = ''; }
    });
    if (root.dataset.enabled !== '1') {
        notify('Piloto inactivo. Redes debe configurar y habilitar Twilio en el VPS.', true);
    }
    controls();
}
