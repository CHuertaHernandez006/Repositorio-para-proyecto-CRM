<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>CRM BeeNear - InterAcom</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */
                @layer theme {
                    :root, :host {
                        --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                    }
                }
            </style>
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="bg-[#F4F6F8] dark:bg-[#0A0E1A] text-[#1B1B18] flex items-center justify-center min-h-screen flex-col font-sans p-4 lg:p-8">
        
        <header class="w-full lg:max-w-5xl text-sm mb-6">
            <nav class="flex items-center justify-between bg-[#0F4280] text-white px-6 py-3 rounded-lg shadow-md">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-xl tracking-tight">Inter<span class="text-[#00A8FF]">Acom</span></span>
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded text-white/90">CRM BeeNear</span>
                </div>

                @if (Route::has('login'))
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-1.5 bg-white text-[#0F4280] hover:bg-gray-100 font-medium rounded-md text-sm transition-all">
                                Panel Principal
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-1.5 text-white/90 hover:text-white hover:bg-white/10 rounded-md text-sm transition-all">
                                Iniciar Sesión
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-4 py-1.5 bg-[#00A8FF] hover:bg-[#0086CC] text-white font-medium rounded-md text-sm transition-all">
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </nav>
        </header>

        <div class="flex items-center justify-center w-full lg:max-w-5xl transition-opacity opacity-100 duration-750">
            <main class="w-full bg-white dark:bg-[#121824] dark:text-[#EDEDEC] border border-gray-200 dark:border-gray-800 rounded-xl shadow-lg overflow-hidden flex flex-col lg:flex-row">
                
                <div class="flex-1 p-6 lg:p-10 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h1 class="text-2xl font-bold text-[#0F4280] dark:text-[#3B82F6]">
                                Bienvenid@ al CRM de BeeNear
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                En línea
                            </span>
                        </div>
                        
                        <p class="mb-6 text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                            Plataforma de gestión del Contact Center Profesional. Administra llamadas, prospectos y métricas operativas en tiempo real.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                            <div class="p-4 bg-gray-50 dark:bg-[#1A2332] rounded-lg border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Llamadas Hoy</span>
                                <p class="text-xl font-semibold text-[#0F4280] dark:text-white mt-1">1,248</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#1A2332] rounded-lg border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Agentes Activos</span>
                                <p class="text-xl font-semibold text-[#0F4280] dark:text-white mt-1">42 / 50</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#1A2332] rounded-lg border border-gray-100 dark:border-gray-800">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Conversión</span>
                                <p class="text-xl font-semibold text-green-600 dark:text-green-400 mt-1">18.4%</p>
                            </div>
                        </div>

                        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Módulos del Sistema</h2>
                        <ul class="space-y-2">
                            <li class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-[#1A2332] transition-colors cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-[#00A8FF]"></span>
                                    <span class="text-sm font-medium">Gestión de Leads & Campañas</span>
                                </div>
                                <span class="text-xs text-gray-400">&rarr;</span>
                            </li>
                            <li class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-[#1A2332] transition-colors cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-[#0F4280]"></span>
                                    <span class="text-sm font-medium">Monitoreo de Operaciones BeeNear</span>
                                </div>
                                <span class="text-xs text-gray-400">&rarr;</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400 flex justify-between items-center">
                        <span>BeeNear CRM v1.0 — Powered by InterAcom</span>
                        <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
                    </div>
                </div>

                <div class="lg:w-80 bg-[#0F4280] text-white p-6 lg:p-8 flex flex-col justify-between relative overflow-hidden">
                    <div class="relative z-10">
                        <span class="text-xs font-semibold uppercase tracking-widest text-[#00A8FF] mb-2 block">Contacto Profesional</span>
                        <h3 class="text-xl font-bold mb-3">Impulsa el Crecimiento</h3>
                        <p class="text-xs text-white/80 leading-relaxed">
                            Controla los flujos de atención telefónica, canales digitales y métricas del equipo de soporte desde una interfaz centralizada.
                        </p>
                    </div>

                    <div class="relative z-10 mt-8">
                        <a href="#" class="block w-full text-center bg-[#00A8FF] hover:bg-[#0086CC] text-white font-medium text-sm py-2.5 rounded-lg shadow transition-all">
                            Acceder a la Consola
                        </a>
                    </div>

                    <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-white/5 rounded-full pointer-events-none"></div>
                </div>

            </main>
        </div>
    </body>
</html>