{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'COMICenter - CRM')</title>

    <script src="https://unpkg.com/lucide@latest" defer></script>

    <style>
        :root {
            color-scheme: dark;
        }

        body {
            margin: 0;
            background: #0f172a;
            color: #f1f5f9;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                'Segoe UI',
                Roboto,
                sans-serif;
        }

        .comi-shell,
        .comi-shell *,
        .comi-shell *::before,
        .comi-shell *::after {
            box-sizing: border-box;
        }

        .comi-shell {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
        }

        /* =========================================================
           SIDEBAR
        ========================================================== */

        .comi-sidebar {
            width: 248px;
            flex-shrink: 0;
            height: 100vh;
            height: 100dvh;
            position: sticky;
            top: 0;

            display: flex;
            flex-direction: column;

            padding: 26px 16px 18px;

            background: #131e30;
            border-right: 1px solid #2b3a50;

            overflow-y: auto;
        }

        .comi-brand {
            display: flex;
            align-items: center;
            gap: 10px;

            padding: 0 10px;

            color: #f8fafc;
            text-decoration: none;
        }

        .comi-brand-mark {
            display: grid;
            place-items: center;

            width: 38px;
            height: 38px;

            border: 1px solid #285569;
            background: #15374a;

            border-radius: 11px;

            color: #38bdf8;

            flex-shrink: 0;
        }

        .comi-brand-name {
            font-size: 21px;
            font-weight: 750;
            letter-spacing: -.8px;
        }

        .comi-brand-name span {
            color: #38bdf8;
        }

        .comi-brand-sub {
            margin: 7px 0 0 58px;

            color: #94a3b8;

            font-size: 10px;
            letter-spacing: .04em;
        }

        /* =========================================================
           NAVEGACIÓN
        ========================================================== */

        .comi-nav-label {
            margin: 36px 12px 13px;

            color: #91a4bd;

            font-size: 10px;
            font-weight: 650;

            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .comi-nav-list {
            list-style: none;

            margin: 0;
            padding: 0;

            display: flex;
            flex-direction: column;

            gap: 6px;
        }

        .comi-nav-link {
            min-height: 46px;

            display: flex;
            align-items: center;

            gap: 12px;

            padding: 12px;

            border: 1px solid transparent;
            border-radius: 9px;

            color: #b6c8df;
            text-decoration: none;

            font-size: 13px;
            font-weight: 500;

            transition:
                background-color .15s,
                color .15s,
                border-color .15s;
        }

        .comi-nav-link:hover {
            background: #1e2d43;
            color: #f1f5f9;
        }

        .comi-nav-link[aria-current="page"] {
            border-color: #285569;
            background: #15374a;
            color: #7dd3fc;
        }

        .comi-nav-link[aria-current="page"]::after {
            content: '';

            margin-left: auto;

            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: #38bdf8;

            flex-shrink: 0;
        }

        .comi-nav-link[aria-disabled="true"] {
            color: #94a3b8;
            cursor: default;
        }

        .comi-nav-link[aria-disabled="true"]:hover {
            background: transparent;
        }

        .comi-nav-soon {
            margin-left: auto;

            font-size: 9px;
            color: #94a3b8;
        }

        /* =========================================================
           PARTE INFERIOR SIDEBAR
        ========================================================== */

        .comi-sidebar-bottom {
            margin-top: auto;
            padding-top: 32px;
        }

        .comi-workspace {
            display: flex;
            align-items: center;

            gap: 10px;

            padding: 13px 12px;

            border: 1px solid #334155;
            border-radius: 10px;

            background: #1a283c;
        }

        .comi-workspace > svg {
            color: #38bdf8;
            flex-shrink: 0;
        }

        .comi-workspace strong {
            display: block;

            font-size: 12px;
            font-weight: 600;

            color: #e2e8f0;
        }

        .comi-workspace p {
            margin: 4px 0 0;

            color: #a5b4c8;

            font-size: 11px;
        }

        .comi-sidebar-caption {
            margin: 14px 0 0;

            text-align: center;

            color: #94a3b8;

            font-size: 10px;
        }

        /* =========================================================
           CONTENEDOR PRINCIPAL
        ========================================================== */

        .comi-main-wrapper {
            display: flex;
            flex-direction: column;

            flex: 1;
            min-width: 0;
        }

        /* =========================================================
           HEADER
        ========================================================== */

        .comi-top-header {
            min-height: 86px;

            padding: 18px 30px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            border-bottom: 1px solid #2b3a50;

            background: #131e30;
        }

        .comi-header-start {
            display: flex;
            align-items: center;

            gap: 13px;

            min-width: 0;
        }

        .comi-header-eyebrow {
            margin: 0 0 5px;

            color: #94a3b8;

            font-size: 10px;

            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .comi-header-title {
            margin: 0;

            font-size: 17px;
            font-weight: 600;

            color: #f1f5f9;

            line-height: 1.3;

            overflow-wrap: anywhere;
        }

        /* =========================================================
           USUARIO
        ========================================================== */

        .comi-user-profile {
            display: flex;
            align-items: center;

            gap: 11px;

            min-width: 0;
        }

        .comi-user-avatar {
            display: grid;
            place-items: center;

            width: 39px;
            height: 39px;

            flex-shrink: 0;

            border: 1px solid #31516a;
            border-radius: 11px;

            color: #7dd3fc;

            background: #17364a;
        }

        .comi-user-info {
            min-width: 0;
        }

        .comi-user-email {
            max-width: 290px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

            color: #e2e8f0;

            font-size: 12px;
            font-weight: 600;
        }

        .comi-user-role {
            margin: 4px 0 0;

            color: #a5b4c8;

            font-size: 11px;
        }

        /* =========================================================
           LOGOUT
        ========================================================== */

        .comi-logout-form {
            display: flex;
            align-items: center;

            margin-left: 8px;
        }

        .comi-logout-button {
            display: grid;
            place-items: center;

            width: 38px;
            height: 38px;

            border: 1px solid #334155;
            border-radius: 10px;

            background: #1e293b;

            color: #94a3b8;

            cursor: pointer;

            transition:
                background-color .15s,
                border-color .15s,
                color .15s;
        }

        .comi-logout-button:hover {
            background: rgba(239, 68, 68, 0.15);

            border-color: #ef4444;

            color: #f87171;
        }

        /* =========================================================
           CONTENIDO
        ========================================================== */

        .comi-content {
            flex: 1;

            min-width: 0;

            padding: 20px;
        }

        /* =========================================================
           FOOTER
        ========================================================== */

        .comi-page-footer {
            padding: 16px 30px 20px;

            display: flex;
            justify-content: space-between;

            gap: 12px;

            flex-wrap: wrap;

            color: #94a3b8;

            font-size: 10px;
        }

        /* =========================================================
           BOTÓN MENÚ MÓVIL
        ========================================================== */

        .comi-menu-button {
            display: none;
            place-items: center;

            width: 42px;
            height: 42px;

            flex-shrink: 0;

            border: 1px solid #475569;
            border-radius: 9px;

            background: #1e293b;

            color: #e2e8f0;

            cursor: pointer;
        }

        .comi-menu-button:hover {
            border-color: #38bdf8;
            color: #7dd3fc;
        }

        /* =========================================================
           ICONOS
        ========================================================== */

        .comi-sidebar svg,
        .comi-top-header svg {
            width: 19px;
            height: 19px;

            stroke-width: 1.8;

            flex-shrink: 0;
        }

        .comi-sidebar [data-lucide],
        .comi-top-header [data-lucide] {
            width: 19px;
            height: 19px;
        }

        /* =========================================================
           ACCESSIBILITY
        ========================================================== */

        .comi-skip {
            position: fixed;

            top: -100px;
            left: 16px;

            z-index: 100;

            padding: 12px 16px;

            background: #0284c7;

            color: white;

            border-radius: 8px;

            text-decoration: none;
        }

        .comi-skip:focus {
            top: 12px;
        }

        :is(
            .comi-nav-link,
            .comi-brand,
            .comi-menu-button,
            .comi-skip,
            .comi-logout-button
        ):focus-visible {
            outline: 2px solid #38bdf8;
            outline-offset: 3px;
        }

        /* =========================================================
           RESPONSIVE
        ========================================================== */

        @media (max-width: 1000px) {

            .comi-sidebar {
                width: 220px;

                padding-left: 12px;
                padding-right: 12px;
            }

            .comi-top-header {
                padding: 18px 22px;
            }

            .comi-user-email {
                max-width: 210px;
            }

            .comi-content {
                padding: 14px;
            }
        }

        @media (max-width: 760px) {

            .comi-shell {
                flex-direction: column;
            }

            .comi-sidebar {
                position: static;

                width: 100%;
                height: auto;

                padding: 18px 16px;

                border-right: 0;
                border-bottom: 1px solid #2b3a50;
            }

            .comi-sidebar-bottom {
                display: none;
            }

            .comi-nav-label {
                margin-top: 22px;
            }

            .comi-shell.comi-enhanced .comi-sidebar {
                display: none;
            }

            .comi-shell.comi-enhanced.comi-nav-open .comi-sidebar {
                display: flex;
            }

            .comi-shell.comi-enhanced .comi-menu-button {
                display: grid;
            }

            .comi-top-header {
                min-height: 78px;

                padding: 14px 16px;

                gap: 12px;

                flex-wrap: wrap;
            }

            .comi-header-eyebrow {
                font-size: 9px;
            }

            .comi-header-title {
                font-size: 15px;
            }

            .comi-user-email {
                max-width: 165px;

                font-size: 11px;
            }

            .comi-content {
                padding: 10px;
            }

            .comi-page-footer {
                padding: 16px 22px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .comi-nav-link {
                transition: none;
            }

            .comi-logout-button {
                transition: none;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

@php
    $usuarioActual = Auth::user();

    /*
    |--------------------------------------------------------------------------
    | Datos del usuario actual
    |--------------------------------------------------------------------------
    */
    $correoActual =
        $usuarioActual->correo
        ?? $usuarioActual->email
        ?? data_get(session('usuario'), 'correo')
        ?? data_get(session('usuario'), 'email')
        ?? 'Usuario';

    $rolActual = (int) (
        $usuarioActual->id_rol
        ?? data_get(session('usuario'), 'id_rol')
        ?? 0
    );

    $rolEtiqueta = [
        1 => 'Super Admin',
        2 => 'Administrador Cliente',
        3 => 'Operario',
    ][$rolActual] ?? 'Usuario';

    /*
    |--------------------------------------------------------------------------
    | Título de la sección actual
    |--------------------------------------------------------------------------
    */
    $tituloSeccion = 'Dashboard';

    foreach ([
        'clientes' => 'Clientes',
        'empresas' => 'Empresas',
        'operarios' => 'Operarios',
        'citas' => 'Citas',
        'campanas' => 'Campañas',
        'llamadas' => 'Llamadas',
    ] as $prefijo => $etiqueta) {
        if (request()->routeIs($prefijo . '.*')) {
            $tituloSeccion = $etiqueta;
            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Menú principal (Roles: 1 = Super Admin, 2 = Admin Cliente, 3 = Operario)
    |--------------------------------------------------------------------------
    */
    $menuItems = [
        [
            'ruta' => 'dashboard',
            'patron' => 'dashboard',
            'texto' => 'Dashboard',
            'icono' => 'layout-dashboard',
            'visible' => true,
        ],
        [
            'ruta' => 'clientes.index',
            'patron' => 'clientes.*',
            'texto' => 'Clientes',
            'icono' => 'users',
            'visible' => Auth::check() && in_array($rolActual, [1, 2], true),
        ],
        [
            'ruta' => 'empresas.index',
            'patron' => 'empresas.*',
            'texto' => 'Empresas',
            'icono' => 'building-2',
            'visible' => Auth::check() && $rolActual === 1,
        ],
        [
            'ruta' => 'operarios.index',
            'patron' => 'operarios.*',
            'texto' => 'Operarios',
            'icono' => 'user-round',

            'visible' =>
                Auth::check()
                && in_array($rolActual, [2], true),
        ],
        [
            'ruta' => 'campanas.index',
            'patron' => 'campanas.*',
            'texto' => 'Campañas',
            'icono' => 'megaphone',

            'visible' =>
                Auth::check()
                && in_array($rolActual, [2], true),
        ],
        [
            'ruta' => 'llamadas.index',
            'patron' => 'llamadas.*',
            'texto' => 'Llamadas',
            'icono' => 'phone',

            'visible' =>
                Auth::check()
                && in_array($rolActual, [2], true),
        ],
    ];
@endphp

<a class="comi-skip" href="#contenido-principal">
    Saltar al contenido
</a>

<div class="comi-shell" id="comi-shell">

    {{-- SIDEBAR --}}
    <aside class="comi-sidebar" id="comi-sidebar" aria-label="Menú principal">

        {{-- Marca --}}
        <a class="comi-brand" href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" aria-label="COMICenter, ir al dashboard">
            <span class="comi-brand-mark">
                <i data-lucide="headset" aria-hidden="true"></i>
            </span>
            <span class="comi-brand-name">
                COMI<span>Center</span>
            </span>
        </a>

        <p class="comi-brand-sub">
            Gestión de interacciones y datos
        </p>

        {{-- Navegación --}}
        <nav aria-label="Secciones del CRM">
            <p class="comi-nav-label">
                Espacio de trabajo
            </p>

            <ul class="comi-nav-list">
                @foreach ($menuItems as $item)
                    @if ($item['visible'])
                        <li>
                            @if (Route::has($item['ruta']))
                                <a
                                    class="comi-nav-link"
                                    href="{{ route($item['ruta']) }}"
                                    @if (request()->routeIs($item['patron'])) aria-current="page" @endif
                                >
                                    <i data-lucide="{{ $item['icono'] }}" aria-hidden="true"></i>
                                    <span>{{ $item['texto'] }}</span>
                                </a>
                            @else
                                <span class="comi-nav-link" aria-disabled="true">
                                    <i data-lucide="{{ $item['icono'] }}" aria-hidden="true"></i>
                                    <span>{{ $item['texto'] }}</span>
                                    <span class="comi-nav-soon">No disponible</span>
                                </span>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>

        {{-- Parte inferior del sidebar --}}
        <div class="comi-sidebar-bottom">
            <div class="comi-workspace">
                <i data-lucide="layers" aria-hidden="true"></i>
                <div>
                    <strong>Tu espacio de gestión</strong>
                    <p>Contactos e interacciones</p>
                </div>
            </div>

            <p class="comi-sidebar-caption">
                Plataforma COMI
            </p>
        </div>

    </aside>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="comi-main-wrapper">

        {{-- HEADER --}}
        <header class="comi-top-header">

            <div class="comi-header-start">
                {{-- Botón móvil --}}
                <button
                    type="button"
                    class="comi-menu-button"
                    id="comi-menu-button"
                    aria-label="Abrir menú principal"
                    aria-controls="comi-sidebar"
                    aria-expanded="false"
                >
                    <i data-lucide="menu" aria-hidden="true"></i>
                </button>

                <div>
                    <p class="comi-header-eyebrow">
                        COMICenter / Panel de gestión
                    </p>
                    <h2 class="comi-header-title">
                        @yield('header-title', $tituloSeccion)
                    </h2>
                </div>
            </div>

            {{-- Usuario --}}
            <div class="comi-user-profile">
                <div class="comi-user-avatar">
                    <i data-lucide="user" aria-hidden="true"></i>
                </div>

                <div class="comi-user-info">
                    <div class="comi-user-email" title="{{ $correoActual }}">
                        {{ $correoActual }}
                    </div>
                    <p class="comi-user-role">
                        {{ $rolEtiqueta }}
                    </p>
                </div>

                {{-- Cerrar sesión --}}
                @if (Route::has('logout'))
                    <form action="{{ route('logout') }}" method="POST" class="comi-logout-form">
                        @csrf
                        <button
                            type="submit"
                            class="comi-logout-button"
                            title="Cerrar sesión"
                            aria-label="Cerrar sesión"
                        >
                            <i data-lucide="log-out" aria-hidden="true"></i>
                        </button>
                    </form>
                @endif
            </div>

        </header>

        {{-- CONTENIDO DE LA PÁGINA --}}
        <main class="comi-content" id="contenido-principal" tabindex="-1">
            @yield('content')
        </main>

        {{-- FOOTER --}}
        <footer class="comi-page-footer">
            <span>© {{ date('Y') }} Plataforma COMI</span>
            <span>Gestión de interacciones y datos</span>
        </footer>

    </div>

</div>

{{-- JAVASCRIPT DEL MENÚ --}}
<script>
    (() => {
        const shell = document.getElementById('comi-shell');
        const toggle = document.getElementById('comi-menu-button');
        const sidebar = document.getElementById('comi-sidebar');
        const mobile = window.matchMedia('(max-width: 760px)');

        if (shell && toggle && sidebar) {
            shell.classList.add('comi-enhanced');

            function setMenu(open, moveFocus = false) {
                shell.classList.toggle('comi-nav-open', open);
                toggle.setAttribute('aria-expanded', String(open));
                toggle.setAttribute(
                    'aria-label',
                    open ? 'Cerrar menú principal' : 'Abrir menú principal'
                );

                if (open && moveFocus) {
                    sidebar.querySelector('a')?.focus();
                }
            }

            toggle.addEventListener('click', () => {
                setMenu(!shell.classList.contains('comi-nav-open'), true);
            });

            document.addEventListener('keydown', (event) => {
                if (
                    event.key === 'Escape' &&
                    mobile.matches &&
                    shell.classList.contains('comi-nav-open')
                ) {
                    setMenu(false);
                    toggle.focus();
                }
            });

            mobile.addEventListener('change', () => {
                const focusInSidebar = sidebar.contains(document.activeElement);
                const focusOnToggle = document.activeElement === toggle;

                setMenu(false);

                if (mobile.matches && focusInSidebar) {
                    toggle.focus();
                }

                if (!mobile.matches && focusOnToggle) {
                    sidebar.querySelector('a')?.focus();
                }
            });
        }

        window.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    })();
</script>

@stack('scripts')

</body>
</html>