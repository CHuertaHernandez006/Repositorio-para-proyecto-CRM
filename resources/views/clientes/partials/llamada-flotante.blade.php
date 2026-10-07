@if(
    Auth::check()
    && (int) Auth::user()->id_rol === 3
)
    <div
        id="comi-call-float-root"
        class="comi-call-float-root"
        data-endpoint="{{ route('llamadas.widget.activa') }}"
        aria-live="polite"
    >
        {{-- Lanzador compacto cuando la ventana está minimizada --}}
        <button
            type="button"
            id="comi-call-launcher"
            class="comi-call-launcher"
            aria-label="Abrir llamada en curso"
            title="Abrir llamada en curso"
            hidden
        >
            <span class="comi-call-launcher-ring"></span>

            <i data-lucide="phone-call"></i>

            <span
                id="comi-call-launcher-time"
                class="comi-call-launcher-time"
            >
                00:00
            </span>
        </button>


        {{-- Ventana flotante --}}
        <section
            id="comi-call-widget"
            class="comi-call-widget"
            role="dialog"
            aria-label="Llamada en curso"
            hidden
        >
            <header class="comi-call-widget-header">

                <div class="comi-call-brand">
                    <span class="comi-call-brand-icon">
                        <i data-lucide="headphones"></i>
                    </span>

                    <span>
                        COMI<span>Center</span>
                    </span>
                </div>


                <div class="comi-call-widget-header-actions">

                    <span class="comi-call-source">
                        Audio en Zoiper
                    </span>

                    <button
                        type="button"
                        id="comi-call-minimize"
                        class="comi-call-icon-button"
                        aria-label="Minimizar llamada"
                        title="Minimizar"
                    >
                        <i data-lucide="minus"></i>
                    </button>

                </div>

            </header>


            <div class="comi-call-widget-body">

                <div class="comi-call-main">

                    <div
                        id="comi-call-avatar"
                        class="comi-call-avatar"
                    >
                        CL
                    </div>


                    <div class="comi-call-contact">

                        <div class="comi-call-contact-top">

                            <div>
                                <h3 id="comi-call-client">
                                    Cliente
                                </h3>

                                <div class="comi-call-number-row">

                                    <span id="comi-call-phone">
                                        —
                                    </span>

                                    <button
                                        type="button"
                                        id="comi-call-copy"
                                        class="comi-call-copy"
                                        title="Copiar número"
                                        aria-label="Copiar número"
                                    >
                                        <i data-lucide="copy"></i>
                                    </button>

                                </div>
                            </div>


                            <div class="comi-call-live">

                                <div
                                    id="comi-call-status"
                                    class="comi-call-status"
                                >
                                    <span></span>
                                    Llamada en curso
                                </div>

                                <div
                                    id="comi-call-timer"
                                    class="comi-call-timer"
                                >
                                    00:00
                                </div>

                            </div>

                        </div>


                        <div class="comi-call-context">

                            <span>
                                <i data-lucide="building-2"></i>

                                <strong id="comi-call-company">
                                    Sin organización
                                </strong>
                            </span>

                            <span>
                                <i data-lucide="megaphone"></i>

                                <strong id="comi-call-campaign">
                                    Sin campaña
                                </strong>
                            </span>

                        </div>


                        <div class="comi-call-meta">

                            <span
                                id="comi-call-type"
                                class="comi-call-type"
                            >
                                Saliente
                            </span>

                            <span id="comi-call-extension">
                                Ext. —
                            </span>

                        </div>

                    </div>

                </div>


                <div class="comi-call-divider"></div>


                <div class="comi-call-actions">

                    <button
                        type="button"
                        class="comi-call-action"
                        data-softphone-control="Silenciar"
                        title="El audio todavía se controla desde Zoiper"
                    >
                        <i data-lucide="mic-off"></i>
                        <span>Silenciar</span>
                    </button>


                    <button
                        type="button"
                        class="comi-call-action"
                        data-softphone-control="Teclado"
                        title="El teclado DTMF todavía se controla desde Zoiper"
                    >
                        <i data-lucide="grid-3x3"></i>
                        <span>Teclado</span>
                    </button>


                    <a
                        href="#"
                        id="comi-call-detail"
                        class="comi-call-action"
                        title="Abrir detalle y notas"
                    >
                        <i data-lucide="notebook-pen"></i>
                        <span>Notas</span>
                    </a>


                    <button
                        type="button"
                        class="comi-call-action"
                        data-softphone-control="Transferir"
                        title="La transferencia todavía se controla desde Zoiper"
                    >
                        <i data-lucide="corner-up-right"></i>
                        <span>Transferir</span>
                    </button>


                    <button
                        type="button"
                        id="comi-call-end"
                        class="comi-call-action comi-call-end"
                        data-softphone-control="Finalizar llamada"
                        title="Por ahora cuelga desde Zoiper"
                    >
                        <i data-lucide="phone-off"></i>
                        <span>Finalizar</span>
                    </button>

                </div>


                <div
                    id="comi-call-message"
                    class="comi-call-message"
                    hidden
                ></div>

            </div>

        </section>
    </div>


    <style>
        .comi-call-float-root,
        .comi-call-float-root *,
        .comi-call-float-root *::before,
        .comi-call-float-root *::after {
            box-sizing: border-box;
        }

        .comi-call-float-root {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 99999;
            pointer-events: none;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;
        }

        .comi-call-widget,
        .comi-call-launcher {
            pointer-events: auto;
        }

        .comi-call-widget[hidden],
        .comi-call-launcher[hidden] {
            display: none !important;
        }

        /* =========================================================
           VENTANA
        ========================================================== */

        .comi-call-widget {
            width: min(430px, calc(100vw - 32px));
            overflow: hidden;
            border: 1px solid rgba(56, 189, 248, .42);
            border-radius: 20px;
            background:
                linear-gradient(
                    145deg,
                    rgba(14, 31, 53, .98),
                    rgba(7, 20, 36, .99)
                );
            box-shadow:
                0 26px 70px rgba(0, 0, 0, .48),
                0 0 0 1px rgba(56, 189, 248, .04),
                0 0 34px rgba(14, 165, 233, .10);
            color: #eaf4ff;
            backdrop-filter: blur(18px);
            transform-origin: bottom right;
            animation: comi-call-enter .22s ease-out;
        }

        @keyframes comi-call-enter {
            from {
                opacity: 0;
                transform: translateY(12px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .comi-call-widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            min-height: 66px;
            padding: 14px 17px;
            border-bottom: 1px solid rgba(148, 163, 184, .11);
            background: rgba(7, 20, 36, .46);
        }

        .comi-call-brand {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #f8fafc;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .comi-call-brand > span:last-child span {
            color: #38bdf8;
        }

        .comi-call-brand-icon {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border: 1px solid rgba(56, 189, 248, .30);
            border-radius: 10px;
            background: rgba(14, 165, 233, .10);
            color: #38bdf8;
        }

        .comi-call-brand-icon svg {
            width: 18px;
            height: 18px;
        }

        .comi-call-widget-header-actions {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .comi-call-source {
            padding: 5px 8px;
            border: 1px solid rgba(148, 163, 184, .10);
            border-radius: 999px;
            color: #718198;
            background: rgba(148, 163, 184, .04);
            font-size: 9px;
            font-weight: 700;
        }

        .comi-call-icon-button {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border: 1px solid rgba(148, 163, 184, .12);
            border-radius: 9px;
            background: rgba(148, 163, 184, .05);
            color: #94a3b8;
            cursor: pointer;
            transition: .18s ease;
        }

        .comi-call-icon-button:hover {
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, .28);
            background: rgba(148, 163, 184, .10);
        }

        .comi-call-icon-button svg {
            width: 17px;
            height: 17px;
        }

        .comi-call-widget-body {
            padding: 18px;
        }

        /* =========================================================
           CLIENTE / ESTADO
        ========================================================== */

        .comi-call-main {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .comi-call-avatar {
            display: grid;
            place-items: center;
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
            border: 1px solid rgba(99, 102, 241, .22);
            border-radius: 50%;
            background:
                linear-gradient(
                    145deg,
                    #4f64a8,
                    #6d72c8
                );
            color: #f8fafc;
            font-size: 21px;
            font-weight: 800;
            box-shadow: 0 8px 25px rgba(79, 70, 229, .16);
        }

        .comi-call-contact {
            min-width: 0;
            flex: 1;
        }

        .comi-call-contact-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .comi-call-contact h3 {
            margin: 0;
            color: #fff;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -.025em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .comi-call-number-row {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 4px;
            color: #e2e8f0;
            font-size: 15px;
            font-weight: 700;
        }

        .comi-call-copy {
            display: grid;
            place-items: center;
            width: 27px;
            height: 27px;
            padding: 0;
            border: 1px solid rgba(56, 189, 248, .12);
            border-radius: 7px;
            background: rgba(56, 189, 248, .05);
            color: #7dd3fc;
            cursor: pointer;
        }

        .comi-call-copy svg {
            width: 13px;
            height: 13px;
        }

        .comi-call-live {
            flex: 0 0 auto;
            text-align: right;
        }

        .comi-call-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 8px;
            border: 1px solid rgba(52, 211, 153, .20);
            border-radius: 999px;
            background: rgba(52, 211, 153, .07);
            color: #6ee7b7;
            font-size: 9px;
            font-weight: 800;
        }

        .comi-call-status > span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #34d399;
            box-shadow: 0 0 8px rgba(52, 211, 153, .65);
        }

        .comi-call-timer {
            margin-top: 7px;
            color: #34d399;
            font-size: 23px;
            line-height: 1;
            font-weight: 850;
            font-variant-numeric: tabular-nums;
            letter-spacing: -.04em;
        }

        .comi-call-context {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-top: 11px;
        }

        .comi-call-context span {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
            color: #73849b;
            font-size: 10px;
        }

        .comi-call-context svg {
            width: 12px;
            height: 12px;
            flex: 0 0 auto;
            color: #64748b;
        }

        .comi-call-context strong {
            min-width: 0;
            overflow: hidden;
            color: #9cabbf;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .comi-call-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 9px;
            color: #64748b;
            font-size: 9px;
        }

        .comi-call-type {
            padding: 4px 7px;
            border: 1px solid rgba(56, 189, 248, .16);
            border-radius: 999px;
            background: rgba(56, 189, 248, .06);
            color: #67d5ff;
            font-weight: 800;
        }

        .comi-call-divider {
            height: 1px;
            margin: 17px 0;
            background: rgba(148, 163, 184, .10);
        }

        /* =========================================================
           ACCIONES
        ========================================================== */

        .comi-call-actions {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr))
                minmax(74px, 1.12fr);
            gap: 8px;
        }

        .comi-call-action {
            display: flex;
            min-width: 0;
            min-height: 66px;
            padding: 8px 5px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid rgba(148, 163, 184, .12);
            border-radius: 11px;
            background: rgba(30, 53, 82, .62);
            color: #c7d5e6;
            font: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: .18s ease;
        }

        .comi-call-action:hover {
            transform: translateY(-1px);
            border-color: rgba(56, 189, 248, .26);
            background: rgba(31, 67, 98, .72);
            color: #f0f9ff;
        }

        .comi-call-action svg {
            width: 19px;
            height: 19px;
        }

        .comi-call-action span {
            max-width: 100%;
            color: inherit;
            font-size: 9px;
            font-weight: 750;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .comi-call-end {
            border-color: rgba(248, 113, 113, .35);
            background:
                linear-gradient(
                    145deg,
                    #ef4444,
                    #dc2626
                );
            color: #fff;
        }

        .comi-call-end:hover {
            border-color: rgba(254, 202, 202, .45);
            background:
                linear-gradient(
                    145deg,
                    #f05252,
                    #e11d48
                );
        }

        /* =========================================================
           MENSAJE
        ========================================================== */

        .comi-call-message {
            margin-top: 11px;
            padding: 9px 11px;
            border: 1px solid rgba(251, 191, 36, .16);
            border-radius: 9px;
            background: rgba(251, 191, 36, .06);
            color: #fcd34d;
            font-size: 9px;
            line-height: 1.5;
        }

        /* =========================================================
           LANZADOR MINIMIZADO
        ========================================================== */

        .comi-call-launcher {
            position: relative;
            display: grid;
            place-items: center;
            width: 66px;
            height: 66px;
            padding: 0;
            border: 1px solid rgba(56, 189, 248, .45);
            border-radius: 50%;
            background:
                linear-gradient(
                    145deg,
                    #0f3b55,
                    #0b2439
                );
            color: #67d5ff;
            box-shadow:
                0 18px 45px rgba(0, 0, 0, .40),
                0 0 22px rgba(56, 189, 248, .15);
            cursor: pointer;
        }

        .comi-call-launcher svg {
            width: 24px;
            height: 24px;
        }

        .comi-call-launcher-ring {
            position: absolute;
            inset: -5px;
            border: 1px solid rgba(52, 211, 153, .20);
            border-radius: 50%;
            animation: comi-call-pulse 1.7s infinite;
            pointer-events: none;
        }

        @keyframes comi-call-pulse {
            0% {
                opacity: .85;
                transform: scale(.93);
            }

            70% {
                opacity: 0;
                transform: scale(1.16);
            }

            100% {
                opacity: 0;
                transform: scale(1.16);
            }
        }

        .comi-call-launcher-time {
            position: absolute;
            right: -5px;
            bottom: -6px;
            min-width: 40px;
            padding: 3px 6px;
            border: 1px solid rgba(52, 211, 153, .24);
            border-radius: 999px;
            background: #09233a;
            color: #6ee7b7;
            font-size: 8px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }

        /* =========================================================
           RESPONSIVE
        ========================================================== */

        @media (max-width: 600px) {
            .comi-call-float-root {
                right: 12px;
                bottom: 12px;
            }

            .comi-call-widget {
                width: calc(100vw - 24px);
            }

            .comi-call-main {
                align-items: flex-start;
            }

            .comi-call-avatar {
                width: 50px;
                height: 50px;
                flex-basis: 50px;
                font-size: 17px;
            }

            .comi-call-contact-top {
                flex-direction: column;
            }

            .comi-call-live {
                text-align: left;
            }

            .comi-call-actions {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

            .comi-call-end {
                grid-column: span 2;
            }

            .comi-call-source {
                display: none;
            }
        }
    </style>


    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const root =
                    document.getElementById(
                        'comi-call-float-root'
                    );

                if (!root) {
                    return;
                }


                const endpoint =
                    root.dataset.endpoint;

                const widget =
                    document.getElementById(
                        'comi-call-widget'
                    );

                const launcher =
                    document.getElementById(
                        'comi-call-launcher'
                    );

                const minimize =
                    document.getElementById(
                        'comi-call-minimize'
                    );

                const timer =
                    document.getElementById(
                        'comi-call-timer'
                    );

                const launcherTime =
                    document.getElementById(
                        'comi-call-launcher-time'
                    );

                const avatar =
                    document.getElementById(
                        'comi-call-avatar'
                    );

                const client =
                    document.getElementById(
                        'comi-call-client'
                    );

                const phone =
                    document.getElementById(
                        'comi-call-phone'
                    );

                const company =
                    document.getElementById(
                        'comi-call-company'
                    );

                const campaign =
                    document.getElementById(
                        'comi-call-campaign'
                    );

                const type =
                    document.getElementById(
                        'comi-call-type'
                    );

                const extension =
                    document.getElementById(
                        'comi-call-extension'
                    );

                const status =
                    document.getElementById(
                        'comi-call-status'
                    );

                const detail =
                    document.getElementById(
                        'comi-call-detail'
                    );

                const copy =
                    document.getElementById(
                        'comi-call-copy'
                    );

                const message =
                    document.getElementById(
                        'comi-call-message'
                    );


                let activeCall = null;
                let fetching = false;
                let timerInterval = null;
                let hideFinishedTimeout = null;


                function isMinimized() {
                    return (
                        sessionStorage.getItem(
                            'comi-call-widget-minimized'
                        ) === '1'
                    );
                }


                function setMinimized(value) {

                    sessionStorage.setItem(
                        'comi-call-widget-minimized',
                        value ? '1' : '0'
                    );

                    if (!activeCall) {
                        widget.hidden = true;
                        launcher.hidden = true;
                        return;
                    }

                    widget.hidden = value;
                    launcher.hidden = !value;
                }


                function formatDuration(totalSeconds) {

                    const seconds =
                        Math.max(
                            0,
                            Math.floor(totalSeconds)
                        );

                    const hours =
                        Math.floor(seconds / 3600);

                    const minutes =
                        Math.floor(
                            (seconds % 3600) / 60
                        );

                    const remainingSeconds =
                        seconds % 60;


                    const mm =
                        String(minutes)
                            .padStart(2, '0');

                    const ss =
                        String(remainingSeconds)
                            .padStart(2, '0');


                    if (hours > 0) {
                        return (
                            String(hours)
                                .padStart(2, '0')
                            + ':'
                            + mm
                            + ':'
                            + ss
                        );
                    }

                    return mm + ':' + ss;
                }


                function updateTimer() {

                    if (
                        !activeCall ||
                        !activeCall.fecha_inicio
                    ) {
                        return;
                    }


                    const startedAt =
                        new Date(
                            activeCall.fecha_inicio
                        ).getTime();

                    if (
                        Number.isNaN(startedAt)
                    ) {
                        return;
                    }


                    const elapsed =
                        (
                            Date.now()
                            - startedAt
                        )
                        / 1000;

                    const formatted =
                        formatDuration(elapsed);

                    timer.textContent =
                        formatted;

                    launcherTime.textContent =
                        formatted;
                }


                function startTimer() {

                    if (timerInterval) {
                        clearInterval(
                            timerInterval
                        );
                    }

                    updateTimer();

                    timerInterval =
                        setInterval(
                            updateTimer,
                            1000
                        );
                }


                function stopTimer() {

                    if (!timerInterval) {
                        return;
                    }

                    clearInterval(
                        timerInterval
                    );

                    timerInterval = null;
                }


                function refreshIcons() {

                    if (
                        typeof lucide !==
                        'undefined'
                    ) {
                        lucide.createIcons();
                    }
                }


                function showMessage(text) {

                    message.textContent =
                        text;

                    message.hidden = false;

                    window.clearTimeout(
                        message._hideTimer
                    );

                    message._hideTimer =
                        window.setTimeout(
                            function () {
                                message.hidden = true;
                            },
                            4200
                        );
                }


                function renderActiveCall(call) {

                    window.clearTimeout(
                        hideFinishedTimeout
                    );

                    activeCall = call;

                    avatar.textContent =
                        call.iniciales || 'CL';

                    client.textContent =
                        call.cliente || 'Cliente';

                    phone.textContent =
                        call.telefono || '—';

                    company.textContent =
                        call.organizacion
                        || 'Sin organización';

                    campaign.textContent =
                        call.campana
                        || 'Sin campaña';

                    type.textContent =
                        call.tipo_texto
                        || 'Llamada';

                    extension.textContent =
                        call.extension
                            ? 'Ext. ' + call.extension
                            : 'Ext. —';

                    status.innerHTML =
                        '<span></span>'
                        + (
                            call.estado
                            || 'En curso'
                        );

                    detail.href =
                        call.detalle_url || '#';

                    message.hidden = true;

                    startTimer();

                    setMinimized(
                        isMinimized()
                    );

                    refreshIcons();
                }


                function renderNoCall() {

                    if (!activeCall) {
                        widget.hidden = true;
                        launcher.hidden = true;
                        return;
                    }

                    /*
                     * La llamada estaba visible y en el siguiente
                     * sondeo ya tiene fecha_fin: la mostramos unos
                     * segundos como finalizada.
                     */

                    stopTimer();

                    status.innerHTML =
                        '<span></span>Finalizada';

                    status.style.borderColor =
                        'rgba(148,163,184,.20)';

                    status.style.background =
                        'rgba(148,163,184,.07)';

                    status.style.color =
                        '#aab7c8';

                    const statusDot =
                        status.querySelector(
                            'span'
                        );

                    if (statusDot) {
                        statusDot.style.background =
                            '#94a3b8';

                        statusDot.style.boxShadow =
                            'none';
                    }

                    showMessage(
                        'La llamada terminó. '
                        + 'Puedes abrir Notas para registrar '
                        + 'el resultado y seguimiento.'
                    );

                    widget.hidden = false;
                    launcher.hidden = true;

                    hideFinishedTimeout =
                        window.setTimeout(
                            function () {
                                widget.hidden = true;
                                launcher.hidden = true;
                                activeCall = null;
                            },
                            9000
                        );
                }


                async function pollActiveCall() {

                    if (fetching) {
                        return;
                    }

                    fetching = true;

                    try {

                        const response =
                            await fetch(
                                endpoint,
                                {
                                    method: 'GET',
                                    headers: {
                                        'Accept':
                                            'application/json',
                                        'X-Requested-With':
                                            'XMLHttpRequest'
                                    },
                                    credentials:
                                        'same-origin',
                                    cache:
                                        'no-store'
                                }
                            );


                        if (
                            response.status === 401 ||
                            response.status === 403
                        ) {
                            widget.hidden = true;
                            launcher.hidden = true;
                            return;
                        }


                        if (!response.ok) {
                            return;
                        }


                        const data =
                            await response.json();


                        if (
                            data.activa &&
                            data.llamada
                        ) {
                            renderActiveCall(
                                data.llamada
                            );

                            return;
                        }


                        renderNoCall();

                    } catch (error) {
                        /*
                         * Si falla un sondeo temporalmente no
                         * ocultamos una llamada que ya está visible.
                         */
                    } finally {
                        fetching = false;
                    }
                }


                minimize.addEventListener(
                    'click',
                    function () {
                        setMinimized(true);
                    }
                );


                launcher.addEventListener(
                    'click',
                    function () {
                        setMinimized(false);
                    }
                );


                copy.addEventListener(
                    'click',
                    async function () {

                        if (!activeCall) {
                            return;
                        }

                        try {
                            await navigator.clipboard
                                .writeText(
                                    activeCall.telefono
                                    || ''
                                );

                            showMessage(
                                'Número copiado.'
                            );

                        } catch (error) {
                            showMessage(
                                'No se pudo copiar el número.'
                            );
                        }
                    }
                );


                document
                    .querySelectorAll(
                        '[data-softphone-control]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const control =
                                        button.dataset
                                            .softphoneControl
                                        || 'Este control';

                                    showMessage(
                                        control
                                        + ': por ahora esta acción '
                                        + 'se realiza desde Zoiper. '
                                        + 'La interfaz del CRM ya '
                                        + 'queda preparada para '
                                        + 'conectarla a WebRTC/AMI.'
                                    );
                                }
                            );

                        }
                    );


                /*
                 * Primer sondeo inmediato y después cada 2.5 s.
                 */
                pollActiveCall();

                window.setInterval(
                    pollActiveCall,
                    2500
                );

                refreshIcons();
            }
        );
    </script>
@endif
