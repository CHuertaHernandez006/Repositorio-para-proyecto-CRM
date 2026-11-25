import { Device } from '@twilio/voice-sdk';

export class TwilioVoice {
    constructor(root, csrf, onEvent = () => {}) {
        this.root = root;
        this.csrf = csrf;
        this.onEvent = onEvent;

        this.enabled = root.dataset.twilioEnabled === '1';
        this.device = null;
        this.call = null;
        this.attempt = null;
        this.poller = null;
        this.pollVersion = 0;
        this.serverAnswered = false;
        this.cancelRequested = false;
        this.remoteAudioElement = null;
    }

    get hasCall() {
        return this.call !== null;
    }

    get hasAttempt() {
        return this.attempt !== null;
    }

    emit(type, data = {}) {
        this.onEvent({ type, ...data });
    }

    async request(url, payload = undefined) {
        const response = await fetch(url, {
            method: payload === undefined ? 'GET' : 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(payload === undefined ? {} : {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                }),
            },
            ...(payload === undefined ? {} : {
                body: JSON.stringify(payload),
            }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || `Twilio HTTP ${response.status}`);
        }

        return data;
    }

    async obtainToken() {
        const data = await this.request(this.root.dataset.twilioTokenUrl, {});

        if (!data.token) {
            throw new Error('Laravel no devolvió el token de Twilio.');
        }

        return data.token;
    }

    async prepareDevice() {
        const token = await this.obtainToken();

        if (!this.device) {
            this.device = new Device(token, {
                logLevel: 'warn',
            });

            this.device.on('error', error => {
                this.emit('device-error', {
                    message: error?.message || 'Error del dispositivo Twilio.',
                });
            });

            this.device.on('tokenWillExpire', async () => {
                try {
                    const renewed = await this.obtainToken();
                    this.device?.updateToken(renewed);
                    this.emit('token-renewed');
                } catch (error) {
                    this.emit('token-error', {
                        message: error?.message || 'No se pudo renovar el token.',
                    });
                }
            });

            // No se llama a device.register().
            // Las llamadas entrantes permanecen en Asterisk.
        } else {
            this.device.updateToken(token);
        }
    }

    async connect(destination) {
        if (!this.enabled) {
            throw new Error('Twilio no está habilitado para esta cuenta.');
        }

        if (this.call || this.attempt) {
            throw new Error('Ya existe un intento de llamada Twilio activo.');
        }

        await this.prepareDevice();

        // Laravel vuelve a comprobar cliente, empresa y permisos.
        const prepared = await this.request(
            this.root.dataset.twilioPrepareUrl,
            { telefono: destination },
        );

        if (!prepared.intento) {
            throw new Error('Laravel no generó el identificador del intento.');
        }

        this.attempt = prepared.intento;
        this.serverAnswered = false;
        this.cancelRequested = false;
        this.emit('attempt-created');

        const attempt = this.attempt;

        this.startPolling(attempt);

        try {
            // El destino nunca se envía desde el navegador.
            // Twilio lo consulta en PostgreSQL mediante el UUID.
            const call = await this.device.connect({
                params: {
                    Intento: attempt,
                },
            });

            if (this.attempt !== attempt) {
                call.disconnect();
                throw new Error('El intento de llamada ya no está vigente.');
            }

            if (this.cancelRequested) {
                call.disconnect();
                this.stopAttempt();
                this.emit('ended', {
                    estado: 'cancelada_localmente',
                });
                return null;
            }

            this.call = call;

            call.on('ringing', hasEarlyMedia => {
                this.emit('ringing', { hasEarlyMedia: !!hasEarlyMedia });
            });

            call.on('accept', () => {
                // Esto confirma la sesión de medios del navegador,
                // NO sustituye la confirmación del webhook de respuesta.
                this.emit('media-ready');
            });

            call.on('audio', audioElement => {
                this.remoteAudioElement = audioElement;
                this.emit('audio-element', { audioElement });
            });

            call.on('disconnect', () => {
                if (this.call === call) {
                    this.call = null;
                    this.remoteAudioElement = null;
                    this.emit('media-disconnected');
                }
                // El polling sigue activo hasta la confirmación de Laravel.
            });

            call.on('cancel', () => {
                this.emit('remote-cancel');
            });

            call.on('reject', () => {
                this.emit('remote-reject');
            });

            call.on('error', error => {
                this.emit('call-error', {
                    message: error?.message || 'Error durante la llamada.',
                });
            });

            return call;
        } catch (error) {
            const cancelled = this.cancelRequested;

            if (this.attempt === attempt) {
                this.stopAttempt();
            }

            if (cancelled) {
                this.emit('ended', {
                    estado: 'cancelada_localmente',
                });
                return null;
            }

            throw error;
        }
    }

    startPolling(attempt) {
        this.stopPolling();

        const version = ++this.pollVersion;

        void this.pollAttempt(attempt, version);

        this.poller = window.setInterval(() => {
            void this.pollAttempt(attempt, version);
        }, 2500);
    }

    async pollAttempt(attempt, version) {
        if (this.attempt !== attempt || version !== this.pollVersion) {
            return;
        }

        try {
            const url =
                `${this.root.dataset.twilioCheckBaseUrl}/${encodeURIComponent(attempt)}`;

            const state = await this.request(url);

            if (this.attempt !== attempt || version !== this.pollVersion) {
                return;
            }

            if (state.contestada && !this.serverAnswered) {
                this.serverAnswered = true;

                this.emit('answered', {
                    estado: state.estado,
                    idLlamada: state.id_llamada,
                });
            }

            if (state.finalizada) {
                this.stopPolling();

                this.call = null;
                this.attempt = null;
                this.serverAnswered = false;
                this.cancelRequested = false;
                this.remoteAudioElement = null;

                this.emit('ended', {
                    estado: state.estado,
                    idLlamada: state.id_llamada,
                });
            }
        } catch (_) {
            // Un error temporal de consulta no finaliza una llamada.
            // Se reintentará en el siguiente ciclo.
        }
    }

    stopPolling() {
        if (this.poller !== null) {
            window.clearInterval(this.poller);
            this.poller = null;
        }

        ++this.pollVersion;
    }

    stopAttempt() {
        this.stopPolling();
        this.call = null;
        this.attempt = null;
        this.serverAnswered = false;
        this.cancelRequested = false;
        this.remoteAudioElement = null;
    }

    abortPending() {
        if (this.call) {
            this.call.disconnect();
        } else if (this.attempt) {
            this.device?.disconnectAll();
        }

        this.stopAttempt();
    }

    hangup() {
        if (this.call) {
            this.call.disconnect();
            return;
        }

        // Device.connect puede seguir pendiente.
        if (this.attempt) {
            this.cancelRequested = true;
            this.device?.disconnectAll();
            return;
        }

        throw new Error(
            'No hay una conexión Twilio disponible para colgar.'
        );
    }

    mute(value) {
        if (!this.call) {
            throw new Error('No hay una llamada Twilio activa.');
        }

        this.call.mute(Boolean(value));
    }

    sendDigits(digits) {
        if (!this.call) {
            throw new Error('No hay una llamada Twilio activa.');
        }

        if (!/^[0-9*#w]{1,32}$/i.test(digits)) {
            throw new Error('Los tonos DTMF no tienen un formato válido.');
        }

        this.call.sendDigits(digits);
    }
}
