<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Iniciar Sesión - CRM BeeNear</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
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
        
        <header class="w-full lg:max-w-4xl text-sm mb-6">
            <nav class="flex items-center justify-between bg-[#0F4280] text-white px-6 py-3 rounded-lg shadow-md">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-xl tracking-tight">Inter<span class="text-[#00A8FF]">Acom</span></span>
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded text-white/90">CRM BeeNear</span>
                </div>
                <div class="text-xs text-white/80">
                    Portal de Agentes & Operaciones
                </div>
            </nav>
        </header>

        <div class="flex items-center justify-center w-full lg:max-w-4xl transition-opacity opacity-100 duration-750">
            <main class="w-full bg-white dark:bg-[#121824] dark:text-[#EDEDEC] border border-gray-200 dark:border-gray-800 rounded-xl shadow-lg overflow-hidden flex flex-col lg:flex-row">
                
                <div class="flex-1 p-6 lg:p-10 flex flex-col justify-between">
                    <div>
                        <div class="mb-6">
                            <h1 class="text-2xl font-bold text-[#0F4280] dark:text-[#3B82F6] mb-1">
                                Iniciar Sesión
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Ingresa tus credenciales para acceder al CRM de BeeNear.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.verificar') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="correo" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Correo Electrónico
                                </label>
                                <input 
                                    type="email" 
                                    id="correo" 
                                    name="correo" 
                                    value="{{ old('correo') }}"
                                    required 
                                    autofocus 
                                    placeholder="agente@beenear.com"
                                    class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-[#1A2332] border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:border-[#00A8FF] dark:text-white transition-colors"
                                />
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label for="password" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Contraseña
                                    </label>
                                </div>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    placeholder="••••••••"
                                    class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-[#1A2332] border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:border-[#00A8FF] dark:text-white transition-colors"
                                />
                            </div>

                            <button 
                                type="submit" 
                                class="w-full bg-[#0F4280] hover:bg-[#0A3160] text-white font-medium text-sm py-2.5 rounded-lg shadow transition-all duration-200 mt-2"
                            >
                                Entrar a la Consola
                            </button>
                        </form>
                    </div>

                    <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400 flex justify-between items-center">
                        <span>BeeNear CRM v1.0 — InterAcom</span>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-[#00A8FF] hover:underline font-medium">
                                Solicitar acceso
                            </a>
                        @endif
                    </div>
                </div>

                <div class="lg:w-80 bg-[#0F4280] text-white p-6 lg:p-8 flex flex-col justify-between relative overflow-hidden">
                    <div class="relative z-10">
                        <span class="text-xs font-semibold uppercase tracking-widest text-[#00A8FF] mb-2 block">Contact Center</span>
                        <h3 class="text-xl font-bold mb-3">CRM BeeNear</h3>
                        <p class="text-xs text-white/80 leading-relaxed mb-4">
                            Gestión centralizada de campañas, omnicanalidad y flujo de atención en tiempo real.
                        </p>
                        
                        <div class="space-y-2 mt-6 pt-6 border-t border-white/10">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-white/70">Estado del servidor:</span>
                                <span class="text-green-400 font-medium">● Operativo</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-white/70">Soporte Técnico:</span>
                                <span class="text-white/90">Ext. 4002</span>
                            </div>
                        </div>
                    </div>

                    <div class="relative z-10 mt-8 text-xs text-white/50 text-center">
                        © {{ date('Y') }} InterAcom. Todos los derechos reservados.
                    </div>

                    <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-white/5 rounded-full pointer-events-none"></div>
                </div>

            </main>
        </div>
    </body>
</html>