{{-- Incluir solo en los listados que deban refrescarse automáticamente. --}}
<script>
    (() => {
        const url = @json(route('crm.actualizaciones'));
        const intervaloMs = 8000;
        let versionAnterior = null;
        let consultando = false;

        async function verificarActualizaciones() {
            if (document.hidden || consultando) return;

            consultando = true;
            try {
                const respuesta = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: { 'Accept': 'application/json' }
                });

                if (!respuesta.ok) return;
                const datos = await respuesta.json();
                if (!datos.version) return;

                if (versionAnterior === null) {
                    versionAnterior = datos.version;
                    return;
                }

                if (versionAnterior !== datos.version) {
                    versionAnterior = datos.version;
                    // Se puede sustituir por una recarga parcial del listado.
                    if (typeof window.cargarCitas === 'function') {
                        await window.cargarCitas();
                    } else {
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Error al comprobar actualizaciones del CRM:', error);
            } finally {
                consultando = false;
            }
        }

        verificarActualizaciones();
        const temporizador = window.setInterval(verificarActualizaciones, intervaloMs);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) verificarActualizaciones();
        });
        window.addEventListener('pagehide', () => window.clearInterval(temporizador), { once: true });
    })();
</script>
