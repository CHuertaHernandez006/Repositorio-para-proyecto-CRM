<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title>Iniciar sesión | COMI Center</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">

    {{-- Conserva la integración de recursos del proyecto Laravel, cuando está disponible. --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            color-scheme: dark;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --cc-bg: #0f172a;
            --cc-panel: #1e293b;
            --cc-panel-soft: #172338;
            --cc-input: #111c2e;
            --cc-line: #334155;
            --cc-text: #f1f5f9;
            --cc-muted: #94a3b8;
            --cc-accent: #38bdf8;
            background: var(--cc-bg);
            color: var(--cc-text);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { min-height: 100%; }
        body { margin: 0; min-height: 100dvh; }
        button, input { font: inherit; }
        button { cursor: pointer; }
        a { color: inherit; text-decoration: none; }

        .cc-page {
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: clamp(12px, 2.2vw, 24px);
            background:
                radial-gradient(ellipse at 7% 8%, rgba(14, 165, 233, .11), transparent 32%),
                radial-gradient(ellipse at 95% 92%, rgba(37, 99, 235, .10), transparent 38%),
                var(--cc-bg);
        }
        .cc-shell {
            width: min(100%, 1080px);
            min-height: min(600px, calc(100dvh - 32px));
            display: grid;
            grid-template-columns: minmax(0, 1.10fr) minmax(0, .90fr);
            position: relative;
            background: var(--cc-panel);
            border: 1px solid #384b63;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 30px 85px rgba(0, 4, 14, .52), 0 0 0 1px rgba(56, 189, 248, .025);
        }
        .cc-shell::before {
            content: ''; position: absolute; height: 3px; top: 0; left: 0; right: 0; z-index: 3;
            background: linear-gradient(90deg, #0284c7, #38bdf8 48%, #0e7490);
        }
        .cc-login {
            padding: clamp(24px, 3.2vw, 38px);
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(145deg, #202e42 0%, #1e293b 70%, #1b293d 100%);
        }
        .cc-brand { display: inline-flex; align-items: center; gap: 12px; width: max-content; max-width: 100%; }
        .cc-brand-mark {
            width: 38px; height: 38px;
            display: grid; place-items: center;
            border: 1px solid rgba(56, 189, 248, .35);
            border-radius: 13px;
            background: linear-gradient(145deg, #114463, #163151);
            color: var(--cc-accent);
            box-shadow: 0 6px 24px rgba(2, 132, 199, .13);
        }
        .cc-brand-mark svg { width: 22px; height: 22px; }
        .cc-brand-name { display: block; font-size: 22px; font-weight: 800; letter-spacing: -.055em; color: #f8fafc; }
        .cc-brand-name span { color: var(--cc-accent); }
        .cc-brand-sub { margin: 3px 0 0; font-size: 10px; font-weight: 700; color: #839ab6; letter-spacing: .16em; }

        .cc-main { width: 100%; max-width: 410px; margin: 18px auto 16px; }
        .cc-kicker { margin: 0 0 7px; color: var(--cc-accent); font-size: 11px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        .cc-title { margin: 0; font-size: clamp(27px, 3vw, 34px); line-height: 1.16; letter-spacing: -.055em; font-weight: 800; color: var(--cc-text); }
        .cc-subtitle { margin: 9px 0 18px; color: #9db1c9; font-size: 13px; line-height: 1.55; }
        .cc-alert { margin-bottom: 11px; padding: 9px 12px; border: 1px solid; border-radius: 11px; font-size: 13px; line-height: 1.6; }
        .cc-alert-success { color: #6ee7b7; background: rgba(6, 78, 59, .27); border-color: rgba(52, 211, 153, .35); }
        .cc-alert-error { color: #fda4af; background: rgba(127, 29, 29, .26); border-color: rgba(251, 113, 133, .4); }
        .cc-alert ul { margin: 0; padding-left: 19px; }
        .cc-field { margin-bottom: 13px; }
        .cc-field-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px; }
        .cc-label { font-size: 12px; font-weight: 700; color: #cbd5e1; }
        .cc-forgot { font-size: 12px; font-weight: 700; color: var(--cc-accent); transition: color .2s; }
        .cc-forgot:hover { color: #7dd3fc; text-decoration: underline; }
        .cc-input-wrap { position: relative; }
        .cc-input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 19px; height: 19px; pointer-events: none; color: #7890ad; }
        .cc-input {
            display: block; width: 100%; height: 44px;
            padding: 0 15px 0 45px;
            font-size: 14px; color: #f8fafc;
            background: var(--cc-input);
            border: 1px solid #3b4d65; border-radius: 11px; outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .cc-input::placeholder { color: #7087a2; }
        .cc-input:hover { background: #142238; border-color: #57718d; }
        .cc-input:focus { background: #111f34; border-color: var(--cc-accent); box-shadow: 0 0 0 3px rgba(56, 189, 248, .14); }
        .cc-input:-webkit-autofill,
        .cc-input:-webkit-autofill:hover,
        .cc-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #f8fafc !important;
            -webkit-box-shadow: 0 0 0 1000px #111c2e inset !important;
            caret-color: #f8fafc;
            border-color: #3b4d65;
            transition: background-color 9999s ease-in-out 0s;
        }
        .cc-input-password { padding-right: 87px; }
        .cc-reveal {
            position: absolute; top: 50%; right: 9px; transform: translateY(-50%);
            padding: 9px; border: 0; border-radius: 7px;
            font-size: 12px; font-weight: 700; background: transparent; color: var(--cc-accent);
        }
        .cc-reveal:hover, .cc-reveal:focus-visible { background: #1e3852; outline: none; }
        .cc-submit {
            display: flex; align-items: center; justify-content: center; gap: 12px;
            width: 100%; min-height: 46px; margin-top: 5px;
            border: 1px solid rgba(125, 211, 252, .14); border-radius: 11px;
            background: linear-gradient(110deg, #0284c7, #0369a1); color: #fff;
            box-shadow: 0 10px 25px rgba(2, 132, 199, .20);
            font-size: 14px; font-weight: 800;
            transition: filter .2s, transform .2s, box-shadow .2s;
        }
        .cc-submit svg { width: 18px; height: 18px; transition: transform .2s; }
        .cc-submit:hover { filter: brightness(1.13); transform: translateY(-1px); box-shadow: 0 15px 30px rgba(2, 132, 199, .29); }
        .cc-submit:hover svg { transform: translateX(3px); }
        .cc-submit:focus-visible { outline: 3px solid #7dd3fc; outline-offset: 3px; }
        .cc-login-footer { padding-top: 12px; border-top: 1px solid #3a4a60; color: #8fa3bc; font-size: 11px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .cc-register { color: #7dd3fc; font-weight: 700; }
        .cc-register:hover { color: #bae6fd; text-decoration: underline; }

        .cc-aside {
            position: relative; isolation: isolate;
            display: flex; flex-direction: column; justify-content: space-between;
            min-width: 0; padding: clamp(24px, 3.2vw, 38px); overflow: hidden;
            color: #f8fafc;
            background: linear-gradient(155deg, #0f2541 0%, #0d1b32 52%, #0f172a 100%);
            border-left: 1px solid rgba(148, 163, 184, .18);
        }
        .cc-aside::before {
            content: ''; position: absolute; z-index: -1; inset: 0; pointer-events: none;
            opacity: .21;
            background-image: linear-gradient(rgba(148, 183, 228, .12) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 183, 228, .12) 1px, transparent 1px);
            background-size: 43px 43px;
            mask-image: linear-gradient(to bottom, transparent, #000 35%, transparent);
        }
        .cc-glow-one, .cc-glow-two { position: absolute; z-index: -1; border-radius: 50%; pointer-events: none; filter: blur(8px); }
        .cc-glow-one { width: 360px; height: 360px; right: -180px; top: -155px; background: radial-gradient(circle, rgba(41, 163, 242, .26), transparent 70%); }
        .cc-glow-two { width: 330px; height: 330px; left: -170px; bottom: -160px; background: radial-gradient(circle, rgba(23, 94, 211, .2), transparent 70%); }
        .cc-aside-content { position: relative; z-index: 1; }
        .cc-module { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 15px; padding: 6px 12px; border: 1px solid rgba(115, 189, 249, .22); background: rgba(95, 163, 218, .12); border-radius: 999px; color: #7dd3fc; font-size: 10px; font-weight: 800; letter-spacing: .16em; }
        .cc-module-dot { width: 7px; height: 7px; background: #38bdf8; border-radius: 50%; box-shadow: 0 0 12px rgba(68, 194, 255, .5); }
        .cc-aside-title { max-width: 370px; margin: 0; font-size: clamp(28px, 3vw, 36px); line-height: 1.13; letter-spacing: -.057em; font-weight: 800; }
        .cc-aside-title span { display: block; color: #7dd3fc; }
        .cc-aside-description { margin: 12px 0 18px; max-width: 340px; color: #adc2da; font-size: 13px; line-height: 1.55; }
        .cc-flow { position: relative; display: grid; gap: 8px; }
        .cc-flow::before { content: ''; position: absolute; left: 26px; top: 28px; bottom: 28px; width: 1px; background: linear-gradient(#52b9f1, rgba(82, 185, 241, .1)); }
        .cc-flow-item { position: relative; display: flex; align-items: center; gap: 12px; min-height: 62px; padding: 9px 12px; border: 1px solid rgba(148, 163, 184, .2); border-radius: 13px; background: rgba(30, 41, 59, .67); backdrop-filter: blur(10px); transition: border-color .2s, background .2s; }
        .cc-flow-item:hover { border-color: rgba(56, 189, 248, .48); background: rgba(35, 57, 82, .84); }
        .cc-flow-icon { position: relative; z-index: 1; display: grid; place-items: center; flex: 0 0 35px; width: 35px; height: 35px; border: 1px solid rgba(126, 198, 244, .2); border-radius: 11px; background: #183a59; color: #7dd3fc; }
        .cc-flow-icon svg { width: 18px; height: 18px; }
        .cc-flow-title { margin: 0 0 2px; font-size: 12.5px; font-weight: 700; color: #f8fafc; }
        .cc-flow-desc { margin: 0; color: #a1b6cd; font-size: 11px; line-height: 1.45; }
        .cc-aside-footer { position: relative; z-index: 1; display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-top: 16px; padding-top: 12px; border-top: 1px solid rgba(176, 209, 238, .17); color: #859cb5; font-size: 11px; }
        .cc-aside-footer strong { color: #d3e6f6; font-weight: 700; }

        /* En escritorio ajusta la altura a una sola pantalla sin recortar contenido. */
        @media (min-width: 851px) and (max-height: 680px) {
            .cc-page { padding-block: 8px; }
            .cc-shell { min-height: 0; }
            .cc-login, .cc-aside { padding-top: 22px; padding-bottom: 22px; }
            .cc-main { margin: 12px auto; }
            .cc-subtitle { margin-bottom: 12px; }
            .cc-aside-description { margin-bottom: 12px; }
            .cc-flow-item { min-height: 57px; padding: 7px 11px; }
            .cc-aside-footer { margin-top: 11px; }
        }
        @media (max-width: 850px) {
            .cc-shell { grid-template-columns: minmax(0, 1fr); max-width: 600px; min-height: 0; }
            .cc-login { padding: 36px clamp(25px, 8vw, 58px); }
            .cc-main { margin: 26px auto 24px; }
            .cc-aside { border-left: 0; border-top: 1px solid rgba(148, 163, 184, .18); padding: 35px clamp(25px, 8vw, 58px); }
            .cc-aside-title { font-size: 33px; }
            .cc-module { margin-bottom: 18px; }
            .cc-flow { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
            .cc-flow::before { display: none; }
            .cc-flow-item { align-items: flex-start; flex-direction: column; gap: 9px; padding: 12px; }
        }
        @media (max-width: 520px) {
            .cc-page { padding: 10px; min-height: 100dvh; }
            .cc-shell { border-radius: 17px; }
            .cc-login { padding: 29px 23px 25px; }
            .cc-main { margin: 24px auto 20px; }
            .cc-aside { padding: 30px 23px; }
            .cc-flow { grid-template-columns: 1fr; }
            .cc-flow-item { flex-direction: row; align-items: center; }
            .cc-aside-footer { gap: 6px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <div class="cc-page">
        <main class="cc-shell">
            {{-- Inicio de sesión: conserva el endpoint y nombres de campos existentes. --}}
            <section class="cc-login" aria-labelledby="cc-login-title">
                <header>
                    <a class="cc-brand" href="{{ url('/') }}" aria-label="COMI Center, inicio">
                        <span class="cc-brand-mark" aria-hidden="true">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 7.5A4.5 4.5 0 0 1 9.5 3h5A4.5 4.5 0 0 1 19 7.5v7a4.5 4.5 0 0 1-4.5 4.5H10l-4 2v-4A4.5 4.5 0 0 1 5 14.5z"/>
                                <path d="M9 10h6M9 13h4"/>
                            </svg>
                        </span>
                        <span>
                            <span class="cc-brand-name">COMI<span>Center</span></span>
                            <span class="cc-brand-sub" style="display:block">PLATAFORMA OPERATIVA</span>
                        </span>
                    </a>
                </header>

                <div class="cc-main">
                    <p class="cc-kicker">Tu espacio de trabajo</p>
                    <h1 id="cc-login-title" class="cc-title">Bienvenido de nuevo.</h1>
                    <p class="cc-subtitle">Ingresa tus credenciales para acceder al entorno de gestión de COMI Center.</p>

                    @if (session('exito'))
                        <div class="cc-alert cc-alert-success" role="status">{{ session('exito') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="cc-alert cc-alert-error" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login.verificar') }}" method="POST">
                        @csrf
                        <div class="cc-field">
                            <div class="cc-field-top">
                                <label class="cc-label" for="correo">Correo electrónico</label>
                            </div>
                            <div class="cc-input-wrap">
                                <svg class="cc-input-icon" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>
                                </svg>
                                <input class="cc-input" type="email" id="correo" name="correo" value="{{ old('correo') }}" placeholder="nombre@empresa.com" autocomplete="email" required autofocus>
                            </div>
                        </div>

                        <div class="cc-field">
                            <div class="cc-field-top">
                                <label class="cc-label" for="password">Contraseña</label>
                                @if (Route::has('password.request'))
                                    <a class="cc-forgot" href="{{ route('password.request') }}">¿La olvidaste?</a>
                                @endif
                            </div>
                            <div class="cc-input-wrap">
                                <svg class="cc-input-icon" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/><path d="M12 14v3"/>
                                </svg>
                                <input class="cc-input cc-input-password" type="password" id="password" name="password" placeholder="Ingresa tu contraseña" autocomplete="current-password" required>
                                <button class="cc-reveal" type="button" id="cc-toggle-password" aria-controls="password" aria-label="Mostrar contraseña" aria-pressed="false">Mostrar</button>
                            </div>
                        </div>

                        <button class="cc-submit" type="submit">
                            Iniciar sesión
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </button>
                    </form>
                </div>

                <footer class="cc-login-footer">
                    <span>COMI Center · Acceso a la plataforma</span>
                    @if (Route::has('register'))
                        <a class="cc-register" href="{{ route('register') }}">Solicitar credenciales</a>
                    @endif
                </footer>
            </section>

            {{-- Presentación del producto: sin estado ficticio del servidor ni IP local. Panel compacto. --}}
            <aside class="cc-aside" aria-label="Presentación del Core Operativo">
                <div class="cc-glow-one" aria-hidden="true"></div>
                <div class="cc-glow-two" aria-hidden="true"></div>

                <div class="cc-aside-content">
                    <span class="cc-module"><span class="cc-module-dot" aria-hidden="true"></span>MÓDULO C</span>
                    <h2 class="cc-aside-title">Core Operativo <span>Todo conectado.</span></h2>
                    <p class="cc-aside-description">Un espacio para organizar las interacciones, coordinar la operación telefónica y mantener el seguimiento de cada cliente.</p>

                    <div class="cc-flow" aria-label="Capacidades de la plataforma">
                        <div class="cc-flow-item">
                            <span class="cc-flow-icon" aria-hidden="true">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"/><path d="M3.5 20v-2a5.5 5.5 0 0 1 11 0v2M16 5a3 3 0 0 1 0 6m1.5 4c2 0 3.5 1.5 3.5 3.5V20"/></svg>
                            </span>
                            <div><p class="cc-flow-title">Clientes</p><p class="cc-flow-desc">Información y contactos centralizados.</p></div>
                        </div>
                        <div class="cc-flow-item">
                            <span class="cc-flow-icon" aria-hidden="true">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h3l1.5 4-2.1 1.8a14 14 0 0 0 5.8 5.8L17 12.5l4 1.5v3a3 3 0 0 1-3 3C9.7 20 4 14.3 4 7a4 4 0 0 1 3-4z"/></svg>
                            </span>
                            <div><p class="cc-flow-title">Interacciones</p><p class="cc-flow-desc">Llamadas y actividad en un solo flujo.</p></div>
                        </div>
                        <div class="cc-flow-item">
                            <span class="cc-flow-icon" aria-hidden="true">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h4"/></svg>
                            </span>
                            <div><p class="cc-flow-title">Seguimiento</p><p class="cc-flow-desc">Citas, actividades y continuidad operativa.</p></div>
                        </div>
                    </div>
                </div>

                <footer class="cc-aside-footer">
                    <strong>COMI Center</strong>
                    <span>© {{ date('Y') }} · Todos los derechos reservados</span>
                </footer>
            </aside>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('cc-toggle-password');
            const password = document.getElementById('password');
            if (!toggle || !password) return;

            toggle.addEventListener('click', function () {
                const showing = password.type === 'password';
                password.type = showing ? 'text' : 'password';
                toggle.textContent = showing ? 'Ocultar' : 'Mostrar';
                toggle.setAttribute('aria-label', showing ? 'Ocultar contraseña' : 'Mostrar contraseña');
                toggle.setAttribute('aria-pressed', showing ? 'true' : 'false');
            });
        });
    </script>
</body>
</html>
