<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Teléfono WebRTC') — COMICenter</title>
    <style>
        :root { color-scheme: dark; }
        html, body { margin: 0; padding: 0; min-height: 100%; background: #101827; color: #eaf2ff; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; }
        *, *::before, *::after { box-sizing: border-box; }
        .cfp-view-content { padding: 14px; }
        /* La interfaz original ya es adaptable y contiene sus propios estilos. */
        @media(max-width:600px) { .cfp-view-content { padding: 8px; } }
    </style>
</head>
<body>
<main class="cfp-view-content">
    @yield('content')
</main>
</body>
</html>
