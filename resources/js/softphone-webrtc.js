import { Web } from 'sip.js';

// Softphone SIP.js. No reemplaza AMI ni registra por si mismo el historial.
// Cualquier evento definitivo de llamadas debe provenir de Asterisk.
// La clave SIP se solicita SOLO al backend autenticado, en memoria, y no se persiste.
const root = document.getElementById('webtel-root');

if (root) {
    const el = (name) => document.getElementById(`webtel-${name}`);
    const extension = root.dataset.extension?.trim() || '';
    const wss = root.dataset.wss?.trim() || '';
    const domain = root.dataset.domain?.trim() || '';
    // Lista de marcacion interna; no implica permiso para aprovisionar claves SIP.
    const internas = new Set((root.dataset.internos || '').split(',').map(s => s.trim()).filter(Boolean));
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
            hangup: ['dialing','connected','held'].includes(phase),
            mute: ['connected','held'].includes(phase),
            hold: ['connected','held'].includes(phase),
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
        remoteAudio.volume = Number(el('volume').value) / 100;
        el('volume-value').textContent = `${el('volume').value}%`;
    });
    remoteAudio.volume = Number(el('volume').value) / 100;
    remoteAudio.addEventListener('loadedmetadata', () => {
        if (active && !incoming) void reproducirAudioRemoto();
    });
    // Si Asterisk envía early media, no mezclarla con nuestro tono local de progreso.
    remoteAudio.addEventListener('playing', () => {
        if (phase === 'dialing') silenceTones();
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
        if (!active || incoming) return;
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

    el('audio-activar').addEventListener('click', () => {
        // La llamada a play() ocurre como consecuencia de una accion del usuario.
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
        // Si SIP perdio el registro, permitir reintentar incluso con el objeto anterior presente.
        el('connect').disabled = !configured || registered || connecting || active;
        el('disconnect').disabled = !phone || active || connecting;
        el('call').disabled = !phone || !registered || active;
        el('answer').disabled = !phone || !incoming || !active;
        el('reject').disabled = !phone || !incoming || !active;
        el('hangup').disabled = !phone || !active;
        el('mute').disabled = !phone || !active || incoming || !['connected', 'held'].includes(phase);
        el('hold').disabled = !phone || !active || incoming || !['connected', 'held'].includes(phase);
        el('mute').setAttribute('aria-label', muted ? 'Activar micrófono' : 'Silenciar micrófono');
        el('hold').setAttribute('aria-label', held ? 'Reanudar llamada' : 'Poner llamada en espera');
        root.querySelectorAll('.webtel-client-call').forEach(button => {
            button.disabled = !phone || active || !registered;
        });
        // Estado no sensible disponible para diagnostico en DevTools.
        root.dataset.sipEstado = registered ? 'registrada' : (connecting ? 'conectando' : 'sin-registro');
        root.dataset.llamadaEstado = active ? 'en-curso' : 'inactiva';
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
        if (!active && !['dialing', 'connected', 'held', 'incoming'].includes(phase)) return;
        const endedFrom = phase;
        active = false;
        incoming = false;
        muted = false;
        held = false;
        ++incomingLookupVersion;
        silenceTones();
        if (endedFrom === 'connected' || endedFrom === 'held') playTone([660, 520], 170, 0.022);
        clearClock();
        label('Llamada finalizada. Puedes iniciar otra cuando gustes.');
        changePhase('ended');
        notify(registered ? 'Extensión registrada. Lista para otra llamada.' : 'No conectado.');
        updateButtons();
        finishUiTimer = window.setTimeout(() => {
            if (phase !== 'ended' || active) return;
            currentContact = '';
            currentNumber = '';
            label('Elige un cliente o marca un número para iniciar una llamada.');
            changePhase('idle');
        }, 4200);
    };

    if (!configured) {
        notify('Módulo pendiente: redes debe habilitar WebRTC y proporcionar WEBRTC_WSS_URL / WEBRTC_SIP_DOMAIN; el operario necesita extensión asignada.', true);
    } else if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
        notify('El navegador necesita HTTPS y permiso de micrófono para usar WebRTC.', true);
    } else {
        notify('Conectando automáticamente a la extensión asignada…');
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
                        notify('WebSocket conectado. Registrando extensión…');
                    },
                    onServerDisconnect: () => {
                        if (miConexion !== versionConexion) return;
                        registered = false;
                        if (active) onCallEnd();
                        silenceTones();
                        notify('Se perdió la conexión WSS. Si no se recupera, pulsa Reintentar conexión.', true);
                        updateButtons();
                    },
                    onRegistered: () => {
                        if (miConexion !== versionConexion) return;
                        registered = true;
                        notify(`Extensión ${extension} registrada en Asterisk.`);
                        updateButtons();
                    },
                    onUnregistered: () => {
                        if (miConexion !== versionConexion) return;
                        registered = false;
                        if (phase === 'dialing' || phase === 'incoming') silenceTones();
                        notify('La extensión perdió el registro SIP. Revisa si está abierta en otra pestaña y pulsa Reintentar conexión.', true);
                        console.warn('[COMICenter WebRTC] Registro SIP perdido. Revisar contactos PJSIP y sesiones duplicadas.');
                        updateButtons();
                    },
                    onCallCreated: () => {
                        if (miConexion !== versionConexion) return;
                        active = true;
                        if (phase !== 'incoming' && phase !== 'connected') {
                            changePhase('dialing');
                        }
                        updateButtons();
                    },
                    onCallReceived: () => {
                        if (miConexion !== versionConexion) return;
                        active = true;
                        incoming = true;
                        ++incomingLookupVersion;
                        currentContact = '';
                        const caller = String(phone?.session?.remoteIdentity?.uri?.user || '');
                        currentNumber = /^[+0-9]{3,20}$/.test(caller) ? caller : '';
                        label('Llamada entrante. Pulsa Contestar o Rechazar.');
                        changePhase('incoming');
                        startRingtone('incoming');
                        notify('Llamada entrante. Contesta o rechaza desde el CRM.');
                        identificarEntrante(currentNumber, incomingLookupVersion);
                        updateButtons();
                    },
                    onCallAnswered: () => {
                        if (miConexion !== versionConexion) return;
                        active = true;
                        incoming = false;
                        changePhase('connected');
                        label('En curso · audio conectado mediante WebRTC.');
                        notify('Llamada SIP establecida. Verificando audio WebRTC...');
                        if (!answeredAt) startClock();
                        programarAudioRemoto();
                        updateButtons();
                    },
                    onCallHold: (value) => {
                        if (miConexion !== versionConexion || !active) return;
                        held = !!value;
                        changePhase(held ? 'held' : 'connected');
                        label(held ? 'Llamada en espera' : 'En curso');
                        updateButtons();
                    },
                    onCallHangup: () => {
                        if (miConexion !== versionConexion) return;
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
        if (!phone || !registered || active) {
            if (!registered) notify('La extensión aún no está registrada.', true);
            return;
        }
        unlockAudio(); // Debe iniciarse dentro del clic para respetar restricciones del navegador.
        const numero = (rawPhone || '').trim();
        if (!numero) { notify('Selecciona un cliente o escribe una extensión permitida.', true); return; }
        const digitos = numero.replace(/\D/g, '');
        // Filtro UX: el backend valida además empresa, inventario y cliente.
        if (/^\d{4}$/.test(numero) && !internas.has(numero)) {
            notify('La extensión interna no está permitida.', true); return;
        }
        if (!/^\d{4}$/.test(numero) && (digitos.length < 10 || digitos.length > 15)) {
            notify('Escribe una extensión interna de 4 dígitos autorizada o un número mexicano válido.', true);
            return;
        }
        currentNumber = numero;
        currentContact = contactName;
        el('destination').value = numero;
        active = true; // Evita solicitudes dobles durante la validación del backend.
        label('Verificando que el destino esté autorizado…');
        changePhase('validating');
        updateButtons();
        try {
            const respuesta = await fetch(root.dataset.validateUrl, {
                method: 'POST', credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json', 'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ telefono: numero }),
            });
            const data = await respuesta.json().catch(() => ({}));
            if (!respuesta.ok) throw new Error(data.message || `Validación HTTP ${respuesta.status}`);
            const destino = data.telefono_marcacion;
            // Compatible tanto con el backend actual (sin tipo_destino y 10 dígitos)
            // como con el clasificador interno/externo, si CRM lo incorpora después.
            // Siempre exigimos validación exitosa de Laravel antes de usar SIP.js.
            if (data.tipo_destino === 'interno') {
                if (!/^\d{4}$/.test(destino) || !internas.has(destino)) {
                    throw new Error('El backend devolvió una extensión interna no autorizada.');
                }
            } else if (data.tipo_destino === 'externo' || (!data.tipo_destino && /^\d{10}$/.test(destino))) {
                if (!/^\d{10}$/.test(destino)) {
                    throw new Error('El backend no devolvió un teléfono mexicano válido.');
                }
            } else {
                throw new Error('El backend devolvió un destino o tipo de marcación desconocido.');
            }
            currentNumber = destino;
            currentContact = data.cliente || contactName || (data.tipo_destino === 'interno' ? `Extensión ${destino}` : '');
            label('Solicitando conexión SIP. El tono es local y no confirma que el destino esté timbrando.');
            changePhase('dialing');
            el('dialer').open = false;
            startRingtone('outgoing');
            notify('Solicitando llamada SIP desde el navegador…');
            updateButtons();
            await phone.call(`sip:${destino}@${domain}`);
        } catch (error) {
            active = false;
            incoming = false;
            silenceTones();
            label('No se pudo iniciar la llamada. Puedes corregir el destino e intentarlo de nuevo.');
            changePhase('error');
            notify(`No se inició la llamada: ${error?.message || 'Error desconocido'}`, true);
            finishUiTimer = window.setTimeout(() => {
                if (phase === 'error' && !active) changePhase('idle');
            }, 4200);
        } finally { updateButtons(); }
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
        try { await phone.decline(); onCallEnd(); }
        catch (error) { notify(`Error al rechazar: ${error?.message || ''}`, true); }
    });
    el('hangup').addEventListener('click', async () => {
        if (!phone || !active) return;
        silenceTones();
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
            const debePonerEnEspera = !held;
            if (debePonerEnEspera) await phone.hold(); else await phone.unhold();
            held = debePonerEnEspera;
            changePhase(held ? 'held' : 'connected');
            label(held ? 'Llamada en espera' : 'En curso');
            updateButtons();
        } catch (error) { notify(`No se pudo cambiar la espera: ${error?.message || ''}`, true); }
    });
    root.querySelectorAll('[data-webtel-digit]').forEach(button => button.addEventListener('click', async () => {
        const digit = button.dataset.webtelDigit;
        unlockAudio();
        keyClick(digit);
        if (active && !incoming && phone) {
            try { await phone.sendDTMF(digit); }
            catch (error) { notify(`DTMF no disponible: ${error?.message || ''}`, true); }
        } else if (!active) {
            el('destination').value += digit;
        }
    }));
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
            boton.disabled = !registered || active;
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
