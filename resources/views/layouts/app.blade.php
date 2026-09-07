<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRM System')</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #f4f5f9; color: #333; display: flex; min-height: 100vh; }
        .layout { display: flex; width: 100%; }
        .sidebar { width: 250px; background-color: #ffffff; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; padding: 20px 15px; flex-shrink: 0; }
        .brand { font-size: 1.2rem; font-weight: bold; color: #1e1b4b; margin-bottom: 30px; padding-left: 10px; }
        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 4px; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #4b5563; text-decoration: none; border-radius: 8px; font-size: 0.95rem; font-weight: 500; }
        .nav-item a:hover { background-color: #f3f4f6; color: #111827; }
        .nav-item.active a { background-color: #f3e8ff; color: #6b21a8; }
        .main-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        .top-header { background-color: #ffffff; border-bottom: 1px solid #e5e7eb; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .user-profile { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; }
        .content { padding: 30px; flex-grow: 1; }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">CRM System</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                </li>
                <li class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('clientes.index') ? route('clientes.index') : '#' }}"><i data-lucide="users"></i> Clientes</a>
                </li>
                <li class="nav-item {{ request()->routeIs('campanas.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('campanas.index') ? route('campanas.index') : '#' }}"><i data-lucide="megaphone"></i> Campañas</a>
                </li>
                <li class="nav-item {{ request()->routeIs('llamadas.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('llamadas.index') ? route('llamadas.index') : '#' }}"><i data-lucide="phone"></i> Llamadas</a>
                </li>
            </ul>
        </aside>

        <div class="main-wrapper">
            <header class="top-header">
                <h2>@yield('header-title', 'Dashboard')</h2>
                <div class="user-profile">
                    <div class="avatar"><i data-lucide="user"></i></div>
                    <div>
                        <!-- Se obtiene el correo desde Auth o desde la Session almacenada -->
                        <div style="font-weight: 600;">
                            {{ Auth::user()->correo ?? session('usuario')->correo ?? 'Usuario' }}
                        </div>
                        <div style="font-size: 0.8rem; color: #6b7280;">Usuario verificado</div>
                    </div>
                </div>
            </header>

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>