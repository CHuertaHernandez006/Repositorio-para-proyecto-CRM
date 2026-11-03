
(() => {
    'use strict';

    if (window.__crmAutoRefreshActivo) return;
    window.__crmAutoRefreshActivo = true;

    const INTERVALO = 5000;
    let actualizando = false;
    let cambiosSinGuardar = false;

    // No borrar filtros o formularios que el usuario está editando.
    document.addEventListener('input', (evento) => {
        if (evento.target.closest('[data-autorefresh]')) {
            cambiosSinGuardar = true;
        }
    });

    document.addEventListener('change', (evento) => {
        if (evento.target.closest('[data-autorefresh]')) {
            cambiosSinGuardar = true;
        }
    });

    async function actualizar() {
        const paneles = [
            ...document.querySelectorAll('[data-autorefresh][id]')
        ];

        if (
            !paneles.length ||
            actualizando ||
            document.hidden ||
            cambiosSinGuardar
        ) return;

        const activo = document.activeElement;

        if (
            activo?.matches('input, select, textarea, [contenteditable="true"]') &&
            paneles.some(panel => panel.contains(activo))
        ) return;

        actualizando = true;

        try {
            const respuesta = await fetch(window.location.href, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'text/html'
                }
            });

            if (!respuesta.ok) {
                throw new Error(`HTTP ${respuesta.status}`);
            }

            const tipo = respuesta.headers.get('content-type') || '';

            if (!tipo.includes('text/html')) {
                throw new Error('La respuesta no es HTML');
            }

            const html = await respuesta.text();

            const documento = new DOMParser()
                .parseFromString(html, 'text/html');

            for (const panel of paneles) {
                const nuevo = documento.getElementById(panel.id);

                if (!nuevo) {
                    console.warn(
                        'Autorefresh: panel no encontrado:',
                        panel.id
                    );
                    continue;
                }

                if (panel.innerHTML === nuevo.innerHTML) continue;

                const scroll = panel.querySelector('.scroll');
                const scrollX = scroll?.scrollLeft ?? 0;
                const scrollY = scroll?.scrollTop ?? 0;

                panel.innerHTML = nuevo.innerHTML;

                const scrollNuevo = panel.querySelector('.scroll');

                if (scrollNuevo) {
                    scrollNuevo.scrollLeft = scrollX;
                    scrollNuevo.scrollTop = scrollY;
                }

                console.log('Panel actualizado:', panel.id);

                document.dispatchEvent(
                    new CustomEvent('crm:panel-actualizado', {
                        detail: { id: panel.id }
                    })
                );
            }
        } catch (error) {
            console.warn('Error de autorefresh:', error);
        } finally {
            actualizando = false;
        }
    }

    window.CRMRefresh = {
        actualizar,
        reanudar() {
            cambiosSinGuardar = false;
        }
    };

    setInterval(actualizar, INTERVALO);
})();
