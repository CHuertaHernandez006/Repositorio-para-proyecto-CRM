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

    const formatClock = (secs) => `${String(Math.floor(secs / 60)).padStart(2, '0')}:${String(secs % 60).padStart(2, '0')}`;
    const notify = (message, error = false) => {
        el('status').textContent = message;
        el('status').classList.toggle('webtel-error', error);
    };
    const label = (message) => { el('call-label').textContent = message; };

    const updateButtons = () => {
        // Si SIP perdio el registro, permitir reintentar incluso con el objeto anterior presente.
        el('connect').disabled = !configured || registered || connecting || active;
        el('disconnect').disabled = !phone || active || connecting;
        el('call').disabled = !phone || !registered || active;
        el('answer').disabled = !registered || !incoming || !active;
        el('reject').disabled = !registered || !incoming || !active;
        el('hangup').disabled = !registered || !active;
        el('mute').disabled = !registered || !active || incoming;
        el('hold').disabled = !registered || !active || incoming;
        el('mute').textContent = muted ? 'Activar micrófono' : 'Silenciar';
        el('hold').textContent = held ? 'Recuperar' : 'Espera';
        root.querySelectorAll('.webtel-client-call').forEach(button => {
            button.disabled = !phone || active || !registered;
        });
        // Estado no sensible disponible para diagnostico en DevTools.
        root.dataset.sipEstado = registered ? 'registrada' : (connecting ? 'conectando' : 'sin-registro');
        root.dataset.llamadaEstado = active ? 'en-curso' : 'inactiva';
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
                media: { remote: { audio: el('remote-audio') } },
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
                        notify('La extensión perdió el registro SIP. Revisa si está abierta en otra pestaña y pulsa Reintentar conexión.', true);
                        console.warn('[COMICenter WebRTC] Registro SIP perdido. Revisar contactos PJSIP y sesiones duplicadas.');
                        updateButtons();
                    },
                    onCallCreated: () => {
                        if (miConexion !== versionConexion) return;
                        active = true;
                        updateButtons();
                    },
                    onCallReceived: () => {
                        if (miConexion !== versionConexion) return;
                        active = true;
                        incoming = true;
                        label('Llamada entrante · número pendiente de identificación');
                        notify('Llamada entrante. Contesta o rechaza desde el CRM.');
                        updateButtons();
                    },
                    onCallAnswered: () => {
                        if (miConexion !== versionConexion) return;
                        active = true;
                        incoming = false;
                        label('En curso');
                        notify('Llamada conectada. Audio mediante WebRTC.');
                        startClock();
                        updateButtons();
                    },
                    onCallHold: (value) => {
                        if (miConexion !== versionConexion) return;
                        held = !!value;
                        label(held ? 'En espera' : 'En curso');
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
            updateButtons();
        }
    });

    async function iniciarLlamada(rawPhone) {
        if (!phone || !registered || active) {
            if (!registered) notify('La extensión aún no está registrada.', true);
            return;
        }
        const numero = (rawPhone || '').trim();
        if (!numero) { notify('Selecciona o escribe el teléfono de un cliente.', true); return; }
        el('destination').value = numero;
        active = true; // Impedir doble clic mientras el backend valida.
        updateButtons();
        try {
            const respuesta = await fetch(root.dataset.validateUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json', 'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ telefono: numero }),
            });
            const data = await respuesta.json().catch(() => ({}));
            if (!respuesta.ok) throw new Error(data.message || `Validación HTTP ${respuesta.status}`);
            const destino = data.telefono_marcacion;
            if (!/^\d{10}$/.test(destino)) throw new Error('El backend no devolvió un teléfono válido.');
            label(`Marcando ${destino} · ${data.cliente || 'Cliente registrado'}`);
            notify('Solicitando llamada SIP desde el navegador…');
            await phone.call(`sip:${destino}@${domain}`);
        } catch (error) {
            active = false;
            label('Sin llamada activa');
            notify(`No se inició la llamada: ${error?.message || 'Error desconocido'}`, true);
        } finally {
            updateButtons();
        }
    }

    el('call').addEventListener('click', () => iniciarLlamada(el('destination').value));

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
            boton.textContent = `Llamar · ${etiqueta}`;
            boton.title = `Llamar al ${etiqueta.toLowerCase()} de ${cliente.nombre}`;
            boton.disabled = !registered || active;
            boton.addEventListener('click', () => iniciarLlamada(telefono));
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
        if (active) { event.preventDefault(); event.returnValue = ''; }
    });
    updateButtons();
}
