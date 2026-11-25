import { Web } from 'sip.js';
import { TwilioVoice } from './twilio-voice.js';

// Softphone SIP.js. Registra eventos provisionales, no sustituye CDR/CEL de Asterisk.
// Cualquier evento definitivo de llamadas debe provenir de Asterisk.
// La clave SIP se solicita SOLO al backend autenticado, en memoria, y no se persiste.
const root = document.getElementById('webtel-root');

if (root) {
    const el = (name) => document.getElementById(`webtel-${name}`);
    const extension = root.dataset.extension?.trim() || '';
    const wss = root.dataset.wss?.trim() || '';
    const domain = root.dataset.domain?.trim() || '';
    // Lista de marcacion interna; no implica permiso para aprovisionar claves SIP.
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
    // Impide que eventos tardios de una conexion anterior cambien el estado actual.
    let versionConexion = 0;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const twilioEnabled = root.dataset.twilioEnabled === '1';
    let twilioVoice = null;
    let twilioRemoteAudio = null;
    let activeProvider = null; // null | 'sip' | 'twilio'

    // COMICenter: historial provisional WebRTC (sin atribuir eventos al CDR de Asterisk).
    let historialSesion = null;
    let historialCreacion = null;
    let historialCola = Promise.resolve();
    let historialLatido = null;
    let motivoCierreHistorial = 'terminada';

    async function solicitarHistorial(url, datos, keepalive = false) {
        if (!url) throw new Error('Falta configurar la ruta de historial WebRTC.');
        const respuesta = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            keepalive,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(datos),
        });
        const json = await respuesta.json().catch(() => ({}));
        if (!respuesta.ok) {
            throw new Error(json.message || `Historial HTTP ${respuesta.status}`);
        }
        return json;
    }

    async function iniciarRegistroHistorial(datos) {
        if (historialSesion && historialCreacion) return historialCreacion;
        const sesion = window.crypto?.randomUUID?.();
        if (!sesion) throw new Error('Este navegador no admite identificadores seguros para el historial.');
        historialSesion = sesion;
        motivoCierreHistorial = 'terminada';
        historialCola = Promise.resolve();
        historialCreacion = solicitarHistorial(root.dataset.historyStartUrl, { sesion, ...datos });
        try {
            await historialCreacion;
            if (historialSesion === sesion && !historialLatido) {
                historialLatido = window.setInterval(() => {
                    void enviarEventoHistorial('latido').catch(() => {
                        // Se conserva la llamada; el historial podrá marcarse Sin confirmar.
                    });
                }, 30000);
            }
            return historialCreacion;
        } catch (error) {
            if (historialSesion === sesion) {
                historialSesion = null;
                historialCreacion = null;
            }
            throw error;
        }
    }

    function enviarEventoHistorial(evento, keepalive = false) {
        const sesion = historialSesion;
        const pendiente = historialCreacion;
        if (!sesion || !pendiente) return Promise.resolve();
        // Preserva el orden contestada -> terminada incluso con latencia HTTP.
        const tarea = historialCola.catch(() => {}).then(async () => {
            await pendiente;
            return solicitarHistorial(root.dataset.historyEventUrl, { sesion, evento }, keepalive);
        });
        historialCola = tarea;
        return tarea;
    }

    function cerrarRegistroHistorial(motivo = 'terminada') {
        if (!historialSesion) return;
        const tarea = enviarEventoHistorial(motivo);
        historialSesion = null;
        historialCreacion = null;
        if (historialLatido) window.clearInterval(historialLatido);
        historialLatido = null;
        void tarea.catch(error => console.warn('[COMICenter] El cierre WebRTC quedó pendiente:', error.message));
    }

    window.addEventListener('pagehide', () => {
        if (!historialSesion || !active) return;
        // sendBeacon es best-effort; la CSRF viaja como campo POST, nunca en URL.
        // No declarar una llamada como finalizada cuando solo se cerró la pestaña.
        try {
            const body = new URLSearchParams({
                _token: csrf, sesion: historialSesion, evento: 'interrumpida',
            });
            const blob = new Blob([body.toString()], {
                type: 'application/x-www-form-urlencoded;charset=UTF-8',
            });
            if (!navigator.sendBeacon?.(root.dataset.historyEventUrl, blob)) {
                void enviarEventoHistorial('interrumpida', true).catch(() => {});
            }
        } catch (_) {
            void enviarEventoHistorial('interrumpida', true).catch(() => {});
        }
    });

    let clientesAbort = null;
    let clientesPagina = 1;
    let clientesUltimaPagina = 1;
    let clientesBusqueda = '';
    let clientesCargando = false;
    let clientesSolicitud = 0;

    // UX de llamada: UI y sonidos locales. Las validaciones SIP permanecen en el servidor.
    let phase = 'idle'; // idle | validating | dialing | incoming | connected | held | ended | error
    let currentContact = '';
    let currentNumber = '';
    let soundEnabled = true;
    let audioContext = null;
    let ringingInterval = null;
    let ringingKind = null;
    const activeOscillators = new Set();
    let finishUiTimer = null;
    let incomingLookupVersion = 0;
    const remoteAudio = el('remote-audio');

    // Un sonido local de intento no equivale a que el destino haya recibido 180 Ringing.
    // No reproducimos archivos externos, ni guardamos preferencias ni secretos en storage.
    function unlockAudio() {
        if (!soundEnabled) return null;
        try {
            const AudioCtor = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtor) return null;
            audioContext ||= new AudioCtor();
            if (audioContext.state === 'suspended') audioContext.resume().catch(() => {});
            return audioContext;
        } catch (_) { return null; }
    }

    function playTone(freqs, milliseconds = 140, volume = 0.038, delaySeconds = 0) {
        const ctx = unlockAudio();
        if (!ctx || !soundEnabled) return;
        const start = ctx.currentTime + delaySeconds;
        const stop = start + milliseconds / 1000;
        const values = Array.isArray(freqs) ? freqs : [freqs];
        values.forEach((frequency) => {
            const oscillator = ctx.createOscillator();
            const gain = ctx.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(frequency, start);
            gain.gain.setValueAtTime(0.0001, start);
            gain.gain.linearRampToValueAtTime(volume / values.length, start + 0.015);
            gain.gain.setValueAtTime(volume / values.length, Math.max(start + 0.015, stop - 0.06));
            gain.gain.exponentialRampToValueAtTime(0.0001, stop);
            oscillator.connect(gain).connect(ctx.destination);
            activeOscillators.add(oscillator);
            oscillator.onended = () => {
                activeOscillators.delete(oscillator);
                try { oscillator.disconnect(); gain.disconnect(); } catch (_) { /* limpieza */ }
            };
            oscillator.start(start);
            oscillator.stop(stop + 0.012);
        });
    }

    function silenceTones() {
        if (ringingInterval) clearInterval(ringingInterval);
        ringingInterval = null;
        ringingKind = null;
        for (const oscillator of activeOscillators) {
            try { oscillator.stop(); } catch (_) { /* ya terminó */ }
        }
        activeOscillators.clear();
    }

    function startRingtone(kind) {
        silenceTones();
        if (!soundEnabled) return;
        ringingKind = kind;
        const emit = () => {
            if (!soundEnabled || ringingKind !== kind) return;
            if (kind === 'incoming') {
                // Dos pulsos suaves: timbre local, independiente de la señalización de Asterisk.
                playTone([660, 880], 290, 0.033);
                playTone([660, 880], 290, 0.033, 0.42);
            } else {
                // Tono local de progreso: no confirma timbrado del destinatario.
                playTone([440, 480], 900, 0.022);
            }
        };
        emit();
        ringingInterval = window.setInterval(emit, kind === 'incoming' ? 2400 : 3500);
    }

    const dtmfFreqs = {
        '1':[697,1209],'2':[697,1336],'3':[697,1477],
        '4':[770,1209],'5':[770,1336],'6':[770,1477],
        '7':[852,1209],'8':[852,1336],'9':[852,1477],
        '*':[941,1209],'0':[941,1336],'#':[941,1477],
    };
    function keyClick(digit) { if (dtmfFreqs[digit]) playTone(dtmfFreqs[digit], 110, 0.029); }

    function changePhase(next) {
        if (finishUiTimer) { clearTimeout(finishUiTimer); finishUiTimer = null; }
        phase = next;
        if (next !== 'incoming' && next !== 'dialing') silenceTones();
        renderStage();
    }

    function renderStage() {
        root.dataset.callPhase = phase;
        const state = el('call-state');
        const stateText = el('call-state-label');
        const title = el('call-title');
        const number = el('call-number');
        const name = currentContact || currentNumber || '';
        const stateMap = {
            idle: ['Listo para llamar', 'idle'],
            validating: ['Validando destino', 'dialing'],
            dialing: ['Llamada saliente', 'dialing'],
            incoming: ['Llamada entrante', 'incoming'],
            connected: ['Llamada en curso', 'connected'],
            held: ['Llamada en espera', 'connected'],
            ended: ['Llamada finalizada', 'idle'],
            error: ['No se pudo llamar', 'error'],
        };
        const [stateLabel, tone] = stateMap[phase] || stateMap.idle;
        stateText.textContent = stateLabel;
        state.dataset.tone = tone;
        title.textContent = phase === 'idle'
            ? (registered ? 'Tu teléfono está listo' : 'Teléfono desconectado')
            : (phase === 'validating' ? 'Preparando llamada…'
                : (phase === 'dialing' ? (currentContact || 'Marcando…')
                    : (phase === 'incoming' ? (currentContact || 'Llamada entrante')
                        : (phase === 'ended' ? 'Llamada finalizada' : (name || 'En llamada')))));
        number.textContent = phase === 'idle' ? `Extensión ${extension || 'sin asignar'}` : (currentNumber || 'Número no disponible');
        const callActions = {
            answer: phase === 'incoming',
            reject: phase === 'incoming',
            hangup: ['dialing', 'connected', 'held'].includes(phase),
            mute: ['connected', 'held'].includes(phase),
            hold: activeProvider === 'sip'
                && ['connected', 'held'].includes(phase),
        };
        for (const [button, visible] of Object.entries(callActions)) {
            el(`${button}-wrap`).hidden = !visible;
        }
        el('mute').setAttribute('aria-pressed', String(muted));
        el('hold').setAttribute('aria-pressed', String(held));
        el('mute-label').textContent = muted ? 'Activar micro' : 'Silenciar';
        el('hold-label').textContent = held ? 'Reanudar' : 'Espera';
        el('register-label').textContent = `Extensión ${extension || 'sin asignar'} · ${registered ? 'Registrada' : (connecting ? 'Conectando' : 'Sin registro')}`;
        el('connect-state').textContent = registered ? `Extensión ${extension} conectada con Asterisk` : (connecting ? 'Conectando con Asterisk…' : 'Esperando registro SIP');
        el('connect-pill').textContent = registered ? 'Conectado' : (connecting ? 'Conectando' : 'Sin registro');
    }

    el('sound-toggle').addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        el('sound-toggle').setAttribute('aria-pressed', String(soundEnabled));
        el('sound-toggle').classList.toggle('wt-sound-active', soundEnabled);
        el('sound-label').textContent = soundEnabled ? 'Sonidos activados' : 'Sonidos apagados';
        if (!soundEnabled) silenceTones();
        else if (phase === 'dialing' || phase === 'incoming') startRingtone(phase === 'incoming' ? 'incoming' : 'outgoing');
        else unlockAudio();
    });
    el('keypad-toggle').addEventListener('click', () => {
        const details = el('dialer');
        details.open = !details.open;
        if (details.open) el('destination').focus();
    });
    el('volume').addEventListener('input', () => {
        const volumen = Number(el('volume').value) / 100;
        remoteAudio.volume = volumen;

        if (twilioRemoteAudio) {
            twilioRemoteAudio.volume = volumen;
        }

        el('volume-value').textContent = `${el('volume').value}%`;
    });

    remoteAudio.volume = Number(el('volume').value) / 100;
    remoteAudio.addEventListener('loadedmetadata', () => {
        if (active && !incoming && activeProvider === 'sip') {
            void reproducirAudioRemoto();
        }
    });
    // Si Asterisk envía early media, no mezclarla con nuestro tono local de progreso.
    remoteAudio.addEventListener('playing', () => {
        if (activeProvider === 'sip' && phase === 'dialing') {
            silenceTones();
        }
    });
    // Los navegadores pueden bloquear timbres entrantes hasta la primera interacción.
    document.addEventListener('pointerdown', () => { unlockAudio(); }, { once: true, passive: true });

    async function identificarEntrante(numero, version) {
        if (!numero || !root.dataset.incomingUrl) return;
        const digits = numero.replace(/\D/g, '');
        if (digits.length < 10 || digits.length > 15) return;
        try {
            const url = new URL(root.dataset.incomingUrl, window.location.origin);
            url.searchParams.set('telefono', numero);
            const response = await fetch(url, {
                credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' },
            });
            if (!response.ok) return;
            const result = await response.json();
            if (version === incomingLookupVersion && phase === 'incoming' && result.encontrado && result.cliente) {
                currentContact = result.cliente;
                renderStage();
            }
        } catch (_) { /* identificación informativa, nunca bloquea contestar */ }
    }

    const formatClock = (secs) => `${String(Math.floor(secs / 60)).padStart(2, '0')}:${String(secs % 60).padStart(2, '0')}`;
    const notify = (message, error = false) => {
        el('status').textContent = message;
        el('status').classList.toggle('webtel-error', error);
    };
    const label = (message) => { el('call-label').textContent = message; };

    // Diagnostico de audio WebRTC: no transmite datos a terceros ni expone credenciales.
    // SIP conectado NO implica que ICE/DTLS ni la reproduccion de audio funcionen.
    const audioEstado = el('audio-estado');
    const audioDiagnostico = el('audio-diagnostico');
    function mensajeAudio(texto, falla = false) {
        audioEstado.textContent = texto;
        audioEstado.dataset.error = falla ? '1' : '0';
    }

    async function reproducirAudioRemoto() {
        if (!active || incoming || activeProvider !== 'sip') return;
        remoteAudio.autoplay = true;
        remoteAudio.playsInline = true;
        remoteAudio.muted = false;

        // SimpleUser publica remoteMediaStream; puede llegar despues del evento de llamada.
        const flujoSIP = phone?.remoteMediaStream;
        if (!remoteAudio.srcObject && flujoSIP?.getAudioTracks?.().length) {
            remoteAudio.srcObject = flujoSIP;
        }
        const pistas = remoteAudio.srcObject?.getAudioTracks?.() || [];
        if (!pistas.length) {
            mensajeAudio('La llamada SIP existe, pero aun no se recibe una pista remota de audio.', true);
            return;
        }
        try {
            await remoteAudio.play();
            mensajeAudio('Reproduccion de audio habilitada. Si sigue mudo, revisa las estadisticas RTP/ICE.', false);
        } catch (error) {
            mensajeAudio(`El navegador bloqueo la reproduccion (${error?.name || 'error'}). Pulsa Activar audio.`, true);
        }
    }

    function programarAudioRemoto() {
        // SIP.js puede adjuntar las pistas unos instantes despues de onCallAnswered.
        for (const milisegundos of [0, 500, 1600]) {
            window.setTimeout(() => {
                if (active && !incoming && ['connected', 'held'].includes(phase)) {
                    void reproducirAudioRemoto();
                }
            }, milisegundos);
        }
    }

    el('audio-activar').addEventListener('click', async () => {
        if (activeProvider === 'twilio' && twilioRemoteAudio) {
            try {
                await twilioRemoteAudio.play();
                mensajeAudio('Reproducción de audio Twilio habilitada.', false);
            } catch (error) {
                mensajeAudio(
                    `El navegador bloqueó el audio (${error?.name || 'error'}).`,
                    true
                );
            }
            return;
        }

        void reproducirAudioRemoto();
    });

    el('audio-bocinas').addEventListener('click', async () => {
        try {
            const ConstructorAudio = window.AudioContext || window.webkitAudioContext;
            if (!ConstructorAudio) throw new Error('AudioContext no disponible');
            const contexto = new ConstructorAudio();
            await contexto.resume();
            const oscilador = contexto.createOscillator();
            const volumen = contexto.createGain();
            oscilador.type = 'sine';
            oscilador.frequency.value = 660;
            volumen.gain.value = 0.035;
            oscilador.connect(volumen).connect(contexto.destination);
            oscilador.start();
            oscilador.stop(contexto.currentTime + 0.35);
            oscilador.onended = () => { void contexto.close().catch(() => {}); };
            mensajeAudio('Prueba de bocinas enviada. Si no la escuchaste, revisa la salida predeterminada.', false);
        } catch (error) {
            mensajeAudio(`No se pudieron probar las bocinas: ${error?.name || error?.message}`, true);
        }
    });

    el('audio-microfono').addEventListener('click', async () => {
        if (active) {
            mensajeAudio('Prueba el microfono cuando no haya una llamada activa.', true);
            return;
        }
        const boton = el('audio-microfono');
        boton.disabled = true;
        let flujo = null;
        let contexto = null;
        try {
            flujo = await navigator.mediaDevices.getUserMedia({audio: true, video: false});
            const pista = flujo.getAudioTracks()[0];
            if (!pista || pista.readyState !== 'live') throw new Error('No se obtuvo una pista activa');
            const ConstructorAudio = window.AudioContext || window.webkitAudioContext;
            if (!ConstructorAudio) throw new Error('No hay analizador de audio');
            contexto = new ConstructorAudio();
            await contexto.resume();
            const fuente = contexto.createMediaStreamSource(flujo);
            const analizador = contexto.createAnalyser();
            analizador.fftSize = 1024;
            fuente.connect(analizador);
            const datos = new Float32Array(analizador.fftSize);
            let maximo = 0;
            mensajeAudio('Escuchando tu microfono durante 2 segundos (no se graba nada)...');
            for (let i = 0; i < 12; i++) {
                analizador.getFloatTimeDomainData(datos);
                const rms = Math.sqrt(datos.reduce((s, x) => s + x * x, 0) / datos.length);
                maximo = Math.max(maximo, rms);
                await new Promise(resolve => window.setTimeout(resolve, 160));
            }
            mensajeAudio(maximo > 0.008
                ? 'Microfono permitido; se detecto sonido. Esto aun no garantiza que SIP.js lo envie.'
                : 'Microfono permitido, pero no se detecto voz. Revisa el dispositivo de entrada y habla al probar.', maximo <= 0.008);
        } catch (error) {
            mensajeAudio(`Microfono no disponible: ${error?.name || error?.message}`, true);
        } finally {
            flujo?.getTracks().forEach(pista => pista.stop());
            if (contexto) await contexto.close().catch(() => {});
            boton.disabled = false;
        }
    });

    el('audio-resumen').addEventListener('click', () => {
        // Solamente estados tecnicos; NO imprimir SDP, IP, telefono, contrasenas ni SIP headers.
        const enviar = phone?.localAudioTrack;
        const recibir = phone?.remoteAudioTrack;
        const flujo = remoteAudio.srcObject;
        const politica = document.permissionsPolicy || document.featurePolicy;
        const permiso = nombre => {
            try { return politica?.allowsFeature?.(nombre) ?? 'no consultable'; }
            catch (_) { return 'no consultable'; }
        };
        const resumen = {
            navegador: navigator.userAgent.match(/(Edg|Chrome|Firefox|Version)\/[0-9.]+/)?.[0] || 'otro',
            sipRegistrado: registered, llamada: phase,
            microfonoPista: enviar ? {estado: enviar.readyState, habilitada: enviar.enabled, muted: enviar.muted} : 'no asignada',
            remotoPista: recibir ? {estado: recibir.readyState, habilitada: recibir.enabled, muted: recibir.muted} : 'no asignada',
            reproductor: {tieneFlujo: !!flujo, pistasAudio: flujo?.getAudioTracks?.().length || 0,
                pausado: remoteAudio.paused, silenciado: remoteAudio.muted,
                volumen: remoteAudio.volume, readyState: remoteAudio.readyState},
            iframe: window.self !== window.top,
            politica: {microphone: permiso('microphone'), autoplay: permiso('autoplay')}
        };
        audioDiagnostico.hidden = false;
        audioDiagnostico.textContent = JSON.stringify(resumen, null, 2);
    });

    const updateButtons = () => {
        const sipDisponible = !!phone && registered;
        const twilioDisponible = !!twilioVoice?.enabled;

        const puedeSilenciar = active
            && !incoming
            && ['connected', 'held'].includes(phase)
            && (
                (activeProvider === 'sip' && !!phone)
                || (activeProvider === 'twilio' && !!twilioVoice?.hasCall)
            );

        const puedeColgar = active && (
            (activeProvider === 'sip' && !!phone)
            || (activeProvider === 'twilio' && !!twilioVoice?.hasAttempt)
        );

        el('connect').disabled =
            !configured || registered || connecting || active;

        el('disconnect').disabled =
            !phone || active || connecting;

        // Se puede marcar externamente aunque SIP no esté registrado.
        el('call').disabled =
            active || !(sipDisponible || twilioDisponible);

        el('answer').disabled =
            !phone || !incoming || !active;

        el('reject').disabled =
            !phone || !incoming || !active;

        el('hangup').disabled = !puedeColgar;
        el('mute').disabled = !puedeSilenciar;

        // El botón de espera solo funciona con llamadas SIP.
        el('hold').disabled =
            !puedeSilenciar || activeProvider !== 'sip';

        el('mute').setAttribute(
            'aria-label',
            muted ? 'Activar micrófono' : 'Silenciar micrófono'
        );

        el('hold').setAttribute(
            'aria-label',
            held ? 'Reanudar llamada' : 'Poner llamada en espera'
        );

        // Las fichas del directorio representan clientes externos.
        root.querySelectorAll('.webtel-client-call').forEach(button => {
            button.disabled = !twilioDisponible || active;
        });

        root.dataset.sipEstado = registered
            ? 'registrada'
            : (connecting ? 'conectando' : 'sin-registro');

        root.dataset.llamadaEstado = active ? 'en-curso' : 'inactiva';
        root.dataset.llamadaProveedor = activeProvider || 'ninguno';

        renderStage();
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
        if (!active
            && !['dialing', 'connected', 'held', 'incoming'].includes(phase)) {
            return;
        }

        const endedProvider = activeProvider;

        if (endedProvider === 'sip') {
            cerrarRegistroHistorial(motivoCierreHistorial);
        }

        motivoCierreHistorial = 'terminada';

        const endedFrom = phase;

        active = false;
        incoming = false;
        activeProvider = null;
        twilioRemoteAudio = null;

        muted = false;
        held = false;

        ++incomingLookupVersion;
        silenceTones();

        if (endedFrom === 'connected' || endedFrom === 'held') {
            playTone([660, 520], 170, 0.022);
        }

        clearClock();

        label('Llamada finalizada. Puedes iniciar otra cuando gustes.');
        changePhase('ended');

        notify(
            registered
                ? 'Extensión registrada. Lista para otra llamada.'
                : (twilioEnabled
                    ? 'Twilio disponible para llamadas externas.'
                    : 'No conectado.')
        );

        updateButtons();

        finishUiTimer = window.setTimeout(() => {
            if (phase !== 'ended' || active) return;

            currentContact = '';
            currentNumber = '';

            label('Elige un cliente o marca un número para iniciar una llamada.');
            changePhase('idle');
        }, 4200);
    };


    function manejarEventoTwilio(evento) {
        if (evento.type === 'attempt-created') {
            if (activeProvider === 'twilio') {
                updateButtons();
            }
            return;
        }

        if (evento.type === 'ended') {
            if (activeProvider === 'twilio') {
                onCallEnd();
            }
            return;
        }

        if ([
            'device-error',
            'token-error',
            'call-error',
        ].includes(evento.type)) {
            notify(
                `Twilio: ${evento.message || 'Error de conexión.'}`,
                true
            );

            if (activeProvider === 'twilio') {
                label('Problema de conexión con Twilio.');
            }

            return;
        }

        if (activeProvider !== 'twilio' || !active) {
            return;
        }

        switch (evento.type) {
            case 'ringing':
                silenceTones();

                label(evento.hasEarlyMedia
                    ? 'Recibiendo audio de espera…'
                    : 'El teléfono de destino está timbrando…');

                notify(evento.hasEarlyMedia
                    ? 'Twilio está enviando audio de espera.'
                    : 'El teléfono de destino está timbrando.');
                break;

            case 'media-ready':
                // La conexión de medios NO confirma que el cliente contestó.
                label('Conexión de medios lista; esperando respuesta…');

                notify(
                    'Conexión con Twilio establecida. Esperando que conteste el cliente.'
                );
                break;

            case 'audio-element':
                if (evento.audioElement) {
                    twilioRemoteAudio = evento.audioElement;

                    twilioRemoteAudio.volume =
                        Number(el('volume').value) / 100;

                    twilioRemoteAudio.autoplay = true;
                    twilioRemoteAudio.playsInline = true;

                    void twilioRemoteAudio.play().catch(error => {
                        mensajeAudio(
                            `El navegador bloqueó el audio Twilio (${error?.name || 'error'}).`,
                            true
                        );
                    });
                }
                break;

            case 'answered':
                incoming = false;
                silenceTones();

                changePhase('connected');

                label('En curso · audio conectado mediante Twilio Voice.');

                notify('El cliente contestó. Llamada externa establecida.');

                if (!answeredAt) {
                    startClock();
                }

                updateButtons();
                break;

            case 'media-disconnected':
                label(
                    'Conexión de medios finalizada; esperando confirmación del servidor…'
                );

                notify(
                    'Twilio está confirmando la finalización de la llamada.'
                );

                updateButtons();
                break;

            case 'remote-cancel':
                notify('La llamada externa fue cancelada por el proveedor.');
                break;

            case 'remote-reject':
                notify('El destino rechazó la llamada.');
                break;
        }
    }


    if (twilioEnabled) {
        twilioVoice = new TwilioVoice(
            root,
            csrf,
            manejarEventoTwilio
        );
    }

    if (!window.isSecureContext
        || !navigator.mediaDevices?.getUserMedia) {

        notify(
            'El navegador necesita HTTPS y permiso de micrófono para usar WebRTC.',
            true
        );

    } else if (!configured && twilioEnabled) {

        notify(
            'Twilio está habilitado para llamadas externas. Las llamadas internas requieren configurar WebRTC SIP.'
        );

    } else if (!configured) {

        notify(
            'Módulo pendiente: redes debe habilitar WebRTC y proporcionar WEBRTC_WSS_URL / WEBRTC_SIP_DOMAIN; el operario necesita extensión asignada.',
            true
        );

    } else {

        notify(
            'Conectando automáticamente a la extensión asignada…'
        );

    }

    async function conectar() {
        if (!configured || connecting || registered || active || !window.isSecureContext) return;
        connecting = true;
        const miConexion = ++versionConexion;
        updateButtons();
        try {
            // Si habia un registro anterior perdido, liberar su agente antes de volver a conectar.
            // Los eventos de ese agente se ignoran usando versionConexion.
            if (phone) {
                const anterior = phone;
                phone = null;
                try { await anterior.unregister(); } catch (_) { /* registro previo ya perdido */ }
                try { await anterior.disconnect(); } catch (_) { /* transporte previo ya cerrado */ }
            }
            // Nunca incluir credenciales SIP en HTML, Git o almacenamiento local.
            const respuesta = await fetch(root.dataset.credentialsUrl, {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const credenciales = await respuesta.json().catch(() => ({}));
            if (!respuesta.ok) {
                throw new Error(credenciales.message || `Credenciales HTTP ${respuesta.status}`);
            }
            if (credenciales.extension !== extension || !credenciales.password) {
                throw new Error('La extensión de la credencial SIP no coincide con tu cuenta.');
            }

            // El micrófono se solicitará al hacer o contestar una llamada.
            // Esto permite registrar la extensión sin intervención inicial.
            phone = new Web.SimpleUser(wss, {
                aor: `sip:${extension}@${domain}`,
                media: { constraints: { audio: true, video: false }, remote: { audio: el('remote-audio') } },
                reconnectionAttempts: 3,
                reconnectionDelay: 4,
                userAgentOptions: {
                    authorizationUsername: extension,
                    authorizationPassword: credenciales.password,
                    displayName: `COMICenter ${extension}`,
                    logBuiltinEnabled: false,
                    logConfiguration: false,
                },
                delegate: {
                    onServerConnect: () => {
                        if (miConexion !== versionConexion) return;
                        if (activeProvider !== 'twilio') {
                            notify('WebSocket conectado. Registrando extensión…');
                        }
                    },
                    onServerDisconnect: () => {
                        if (miConexion !== versionConexion) return;

                        const twilioEnCurso =
                            active && activeProvider === 'twilio';

                        registered = false;

                        if (active && activeProvider === 'sip') {
                            motivoCierreHistorial = 'interrumpida';
                            onCallEnd();
                        }

                        if (!twilioEnCurso) {
                            silenceTones();
                            notify(
                                'Se perdió la conexión WSS. Si no se recupera, pulsa Reintentar conexión.',
                                true
                            );
                        }

                        updateButtons();
                    },
                    onRegistered: () => {
                        if (miConexion !== versionConexion) return;
                        registered = true;

                        if (activeProvider !== 'twilio') {
                            notify(`Extensión ${extension} registrada en Asterisk.`);
                        }

                        updateButtons();
                    },
                    onUnregistered: () => {
                        if (miConexion !== versionConexion) return;
                        registered = false;

                        if (activeProvider !== 'twilio') {
                            if (phase === 'dialing' || phase === 'incoming') {
                                silenceTones();
                            }

                            notify(
                                'La extensión perdió el registro SIP. Revisa si está abierta en otra pestaña y pulsa Reintentar conexión.',
                                true
                            );

                            console.warn(
                                '[COMICenter WebRTC] Registro SIP perdido. Revisar contactos PJSIP y sesiones duplicadas.'
                            );
                        }

                        updateButtons();
                    },
                    onCallCreated: () => {
                        if (miConexion !== versionConexion) return;

                        // SIP.js dispara onCallCreated también para llamadas
                        // entrantes, antes de ejecutar onCallReceived.
                        // Diferimos el cambio de estado para distinguirlas.
                        Promise.resolve().then(() => {
                            if (miConexion !== versionConexion) return;

                            // Si ya se identificó como entrante, no marcarla
                            // como una llamada saliente activa.
                            if (incoming || phase === 'incoming') {
                                updateButtons();
                                return;
                            }

                            activeProvider = 'sip';
                            active = true;

                            if (phase !== 'connected') {
                                changePhase('dialing');
                            }

                            updateButtons();
                        });
                    },
                    onCallReceived: () => {
                        if (miConexion !== versionConexion) return;

                        if (active) {
                            console.warn(
                                '[COMICenter] Llamada SIP entrante rechazada: ya existe una llamada activa.',
                                activeProvider
                            );

                            void phone?.decline().catch(error => {
                                console.warn(
                                    '[COMICenter] No se pudo rechazar la llamada entrante:',
                                    error?.message
                                );
                            });

                            return;
                        }

                        activeProvider = 'sip';
                        active = true;
                        incoming = true;
                        ++incomingLookupVersion;
                        currentContact = '';
                        const caller = String(phone?.session?.remoteIdentity?.uri?.user || '');
                        currentNumber = /^[+0-9]{3,20}$/.test(caller) ? caller : '';
                        void iniciarRegistroHistorial({ sentido: 'entrante', origen: currentNumber, destino: extension })
                            .catch(error => console.warn('[COMICenter] No se registró la llamada entrante:', error.message));
                        label('Llamada entrante. Pulsa Contestar o Rechazar.');
                        changePhase('incoming');
                        startRingtone('incoming');
                        notify('Llamada entrante. Contesta o rechaza desde el CRM.');
                        identificarEntrante(currentNumber, incomingLookupVersion);
                        updateButtons();
                    },
                    onCallAnswered: () => {
                        if (miConexion !== versionConexion) return;
                        if (active && activeProvider === 'twilio') return;
                        activeProvider = 'sip';
                        active = true;
                        incoming = false;
                        changePhase('connected');
                        void enviarEventoHistorial('contestada').catch(error =>
                            console.warn('[COMICenter] Contestación sin registrar:', error.message));
                        label('En curso · audio conectado mediante WebRTC.');
                        notify('Llamada SIP establecida. Verificando audio WebRTC...');
                        if (!answeredAt) startClock();
                        programarAudioRemoto();
                        updateButtons();
                    },
                    onCallHold: (value) => {
                        if (miConexion !== versionConexion
                            || !active
                            || activeProvider !== 'sip') return;
                        held = !!value;
                        changePhase(held ? 'held' : 'connected');
                        label(held ? 'Llamada en espera' : 'En curso');
                        updateButtons();
                    },
                    onCallHangup: () => {
                        if (miConexion !== versionConexion
                            || activeProvider !== 'sip') return;
                        onCallEnd();
                    },
                },
            });
            credenciales.password = ''; // No conservar copia innecesaria del JSON.
            notify('Conectando al servidor WSS…');
            await phone.connect();
            await phone.register();
            registered = true;
            notify(`Extensión ${extension} registrada. Puedes llamar y recibir llamadas.`);
        } catch (error) {
            notify(`No se pudo conectar automáticamente: ${error?.message || 'Error desconocido'}`, true);
            if (phone) { try { await phone.disconnect(); } catch (_) { /* limpieza */ } }
            phone = null;
            registered = false;
            if (!active) changePhase('idle');
        } finally {
            connecting = false;
            updateButtons();
        }
    }

    el('connect').addEventListener('click', conectar);

    el('disconnect').addEventListener('click', async () => {
        if (!phone || active || connecting) return;
        connecting = true;
        ++versionConexion; // Ignorar callbacks tardios del agente que se va a cerrar.
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
            silenceTones();
            changePhase('idle');
            updateButtons();
        }
    });

    async function iniciarLlamada(rawPhone, contactName = '') {
        if (active) return;

        unlockAudio();

        const numero = (rawPhone || '').trim();

        if (!numero) {
            notify(
                'Selecciona un cliente o escribe una extensión permitida.',
                true
            );
            return;
        }

        const digitos = numero.replace(/\D/g, '');

        if (!/^\d{4}$/.test(numero)
            && (digitos.length < 10 || digitos.length > 15)) {
            notify(
                'Escribe una extensión interna autorizada o un número mexicano válido.',
                true
            );
            return;
        }

        currentNumber = numero;
        currentContact = contactName;
        el('destination').value = numero;

        active = true;
        activeProvider = null;

        label('Verificando que el destino esté autorizado…');
        changePhase('validating');
        updateButtons();

        try {
            const respuesta = await fetch(root.dataset.validateUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ telefono: numero }),
            });

            const data = await respuesta.json().catch(() => ({}));

            if (!respuesta.ok) {
                throw new Error(
                    data.message || `Validación HTTP ${respuesta.status}`
                );
            }

            const destino = String(data.telefono_marcacion ?? '');

            const tipoDestino = data.tipo_destino
                || (/^\d{10}$/.test(destino)
                    ? 'externo'
                    : (/^\d{4}$/.test(destino) ? 'interno' : ''));

            if (tipoDestino === 'interno') {
                // Laravel ya validó la extensión contra PostgreSQL.
                if (!/^\d{4}$/.test(destino)) {
                    throw new Error(
                        'El backend devolvió una extensión interna inválida.'
                    );
                }

                if (!phone || !registered) {
                    throw new Error(
                        'Tu extensión SIP todavía no está registrada en Asterisk.'
                    );
                }

                activeProvider = 'sip';

            } else if (tipoDestino === 'externo') {
                if (!/^\d{10}$/.test(destino)) {
                    throw new Error(
                        'El backend no devolvió un teléfono mexicano válido.'
                    );
                }

                if (!twilioVoice?.enabled) {
                    throw new Error(
                        'Twilio no está habilitado para llamadas externas.'
                    );
                }

                if (!window.isSecureContext
                    || !navigator.mediaDevices?.getUserMedia) {
                    throw new Error(
                        'Se necesita HTTPS y permiso de micrófono para llamar mediante Twilio.'
                    );
                }

                activeProvider = 'twilio';

            } else {
                throw new Error(
                    'El backend devolvió un destino o tipo de marcación desconocido.'
                );
            }

            currentNumber = destino;

            currentContact = data.cliente
                || contactName
                || (tipoDestino === 'interno'
                    ? `Extensión ${destino}`
                    : '');

            label(tipoDestino === 'interno'
                ? 'Solicitando conexión SIP…'
                : 'Preparando llamada externa mediante Twilio…');

            changePhase('dialing');
            el('dialer').open = false;
            startRingtone('outgoing');
            updateButtons();

            if (activeProvider === 'sip') {
                // El historial provisional corresponde únicamente a SIP.
                await iniciarRegistroHistorial({
                    sentido: 'saliente',
                    origen: extension,
                    destino,
                });

                notify('Solicitando llamada SIP desde el navegador…');

                await phone.call(`sip:${destino}@${domain}`);

            } else {
                // Twilio registra la llamada en CRM mediante sus webhooks.
                // No crear un segundo historial provisional.
                notify('Solicitando llamada externa mediante Twilio…');

                await twilioVoice.connect(destino);
            }

        } catch (error) {
            if (activeProvider === 'sip') {
                cerrarRegistroHistorial('fallida');
            }

            if (activeProvider === 'twilio') {
                try {
                    twilioVoice?.abortPending();
                } catch (_) {
                    // Limpieza de intento fallido.
                }
            }

            active = false;
            incoming = false;
            activeProvider = null;
            muted = false;
            held = false;

            clearClock();
            silenceTones();

            label(
                'No se pudo iniciar la llamada. Puedes corregir el destino e intentarlo de nuevo.'
            );

            changePhase('error');

            notify(
                `No se inició la llamada: ${error?.message || 'Error desconocido'}`,
                true
            );

            finishUiTimer = window.setTimeout(() => {
                if (phase === 'error' && !active) {
                    changePhase('idle');
                }
            }, 4200);

        } finally {
            updateButtons();
        }
    }

    el('call').addEventListener('click', () => iniciarLlamada(el('destination').value));

    el('answer').addEventListener('click', async () => {
        if (!phone || !incoming) return;
        unlockAudio();
        silenceTones();
        try { await phone.answer(); }
        catch (error) {
            notify(`Error al contestar: ${error?.message || ''}`, true);
            if (incoming && phase === 'incoming') startRingtone('incoming');
        }
    });
    el('reject').addEventListener('click', async () => {
        if (!phone || !incoming) return;
        silenceTones();
        motivoCierreHistorial = 'rechazada';
        try { await phone.decline(); onCallEnd(); }
        catch (error) { notify(`Error al rechazar: ${error?.message || ''}`, true); }
    });
    el('hangup').addEventListener('click', async () => {
        if (!active) return;

        silenceTones();

        try {
            if (activeProvider === 'twilio') {
                twilioVoice.hangup();

                label('Finalizando llamada externa…');

                notify(
                    'Solicitando a Twilio la finalización de la llamada.'
                );

                updateButtons();
                return;
            }

            if (activeProvider !== 'sip' || !phone) {
                return;
            }

            await phone.hangup();
            onCallEnd();

        } catch (error) {
            notify(
                `Error al colgar: ${error?.message || ''}`,
                true
            );
        }
    });

    el('mute').addEventListener('click', async () => {
        if (!active || incoming) return;

        try {
            const nuevoEstado = !muted;

            if (activeProvider === 'sip' && phone) {

                if (nuevoEstado) {
                    await phone.mute();
                } else {
                    await phone.unmute();
                }

            } else if (
                activeProvider === 'twilio'
                && twilioVoice?.hasCall
            ) {

                twilioVoice.mute(nuevoEstado);

            } else {
                return;
            }

            muted = nuevoEstado;
            updateButtons();

        } catch (error) {
            notify(
                `No se pudo cambiar el micrófono: ${error?.message || ''}`,
                true
            );
        }
    });

    el('hold').addEventListener('click', async () => {
        if (!phone
            || !active
            || incoming
            || activeProvider !== 'sip') return;
        try {
            const debePonerEnEspera = !held;
            if (debePonerEnEspera) await phone.hold(); else await phone.unhold();
            held = debePonerEnEspera;
            changePhase(held ? 'held' : 'connected');
            label(held ? 'Llamada en espera' : 'En curso');
            updateButtons();
        } catch (error) { notify(`No se pudo cambiar la espera: ${error?.message || ''}`, true); }
    });
    root.querySelectorAll('[data-webtel-digit]').forEach(button => {
        button.addEventListener('click', async () => {
            const digit = button.dataset.webtelDigit;

            unlockAudio();
            keyClick(digit);

            if (active && !incoming) {
                try {
                    if (activeProvider === 'sip' && phone) {
                        await phone.sendDTMF(digit);

                    } else if (
                        activeProvider === 'twilio'
                        && twilioVoice?.hasCall
                    ) {
                        twilioVoice.sendDigits(digit);
                    }

                } catch (error) {
                    notify(
                        `DTMF no disponible: ${error?.message || ''}`,
                        true
                    );
                }

            } else if (!active) {
                el('destination').value += digit;
            }
        });
    });

    const listaClientes = el('clients-list');
    const botonMas = el('clients-more');
    const contador = el('clients-count');

    function mensajeDirectorio(texto) {
        const bloque = document.createElement('div');
        bloque.className = 'webtel-client-empty';
        bloque.textContent = texto;
        listaClientes.replaceChildren(bloque);
    }

    function crearCliente(cliente) {
        const fila = document.createElement('div');
        fila.className = 'webtel-client';
        const info = document.createElement('div');
        info.className = 'webtel-client-info';
        const nombre = document.createElement('div');
        nombre.className = 'webtel-client-name';
        nombre.textContent = cliente.nombre || `Cliente #${cliente.id_cliente}`;
        const numeros = document.createElement('div');
        numeros.className = 'webtel-client-number';
        numeros.textContent = [cliente.telefono_principal, cliente.telefono_secundario]
            .filter(Boolean).join(' · ') || 'Sin teléfono';
        info.append(nombre, numeros);

        const botones = document.createElement('div');
        botones.className = 'webtel-client-buttons';
        const opciones = [
            ['Principal', cliente.telefono_principal],
            ['Secundario', cliente.telefono_secundario],
        ].filter(([, numero]) => !!numero);
        for (const [etiqueta, telefono] of opciones) {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'webtel-client-call';
            boton.innerHTML = '<svg class="wt-icon" aria-hidden="true"><use href="#wt-ico-phone"></use></svg><span></span>';
            boton.querySelector('span').textContent = etiqueta === 'Principal' ? 'Llamar' : 'Secundario';
            boton.title = `Llamar al ${etiqueta.toLowerCase()} de ${cliente.nombre}`;
            boton.disabled = !twilioEnabled || active;
            boton.addEventListener('click', () => iniciarLlamada(telefono, cliente.nombre || ''));
            botones.appendChild(boton);
        }
        fila.append(info, botones);
        return fila;
    }

    async function cargarClientes(nuevaBusqueda = false) {
        if (!root.dataset.clientsUrl) return;
        if (nuevaBusqueda) {
            clientesAbort?.abort();
            clientesAbort = null;
            clientesPagina = 1;
            clientesUltimaPagina = 1;
            clientesBusqueda = el('clients-search').value.trim();
            listaClientes.replaceChildren();
            botonMas.hidden = true;
        } else if (clientesCargando || clientesPagina > clientesUltimaPagina) {
            return;
        }
        const peticion = ++clientesSolicitud;
        const controller = new AbortController();
        clientesAbort = controller;
        clientesCargando = true;
        botonMas.disabled = true;
        contador.textContent = 'Consultando…';
        try {
            const url = new URL(root.dataset.clientsUrl, window.location.origin);
            url.searchParams.set('page', String(clientesPagina));
            if (clientesBusqueda) url.searchParams.set('buscar', clientesBusqueda);
            const respuesta = await fetch(url.toString(), {
                method: 'GET', credentials: 'same-origin', cache: 'no-store',
                headers: { 'Accept': 'application/json' }, signal: controller.signal,
            });
            if (!respuesta.ok) throw new Error(`HTTP ${respuesta.status}`);
            const datos = await respuesta.json();
            if (peticion !== clientesSolicitud) return;
            const items = Array.isArray(datos.clientes) ? datos.clientes : [];
            for (const cliente of items) listaClientes.appendChild(crearCliente(cliente));
            if (!listaClientes.children.length) mensajeDirectorio('No hay clientes que coincidan con la búsqueda.');
            clientesUltimaPagina = Number(datos.ultima_pagina) || 1;
            clientesPagina = (Number(datos.pagina) || clientesPagina) + 1;
            contador.textContent = `${Number(datos.total) || 0} cliente(s)`;
            botonMas.hidden = clientesPagina > clientesUltimaPagina;
            updateButtons();
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (!listaClientes.children.length) mensajeDirectorio('No se pudieron cargar tus clientes.');
            contador.textContent = 'Error al consultar';
        } finally {
            if (peticion === clientesSolicitud) {
                clientesCargando = false;
                botonMas.disabled = false;
            }
        }
    }

    let busquedaTimer = null;
    el('clients-search').addEventListener('input', () => {
        clearTimeout(busquedaTimer);
        busquedaTimer = setTimeout(() => cargarClientes(true), 300);
    });
    botonMas.addEventListener('click', () => cargarClientes());
    cargarClientes(true);
    if (configured && window.isSecureContext) conectar();

    window.addEventListener('beforeunload', (event) => {
        // Avisar que una navegación destruye el WebSocket y corta la llamada.
        silenceTones();
        if (active) { event.preventDefault(); event.returnValue = ''; }
    });
    updateButtons();
}
