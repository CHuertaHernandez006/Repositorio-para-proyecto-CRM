<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Iniciar Sesión - COMI Center</title>

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
    <body class="bg-[#F8FAFC] dark:bg-[#0F172A] text-[#1E293B] flex items-center justify-center min-h-screen flex-col font-sans p-4 lg:p-8">
        
        <header class="w-full lg:max-w-4xl text-sm mb-6">
            <nav class="flex items-center justify-between bg-[#1E293B] text-white px-6 py-3 rounded-lg shadow-md border border-[#334155]">
                <div class="flex items-center gap-3">
                    <!-- Icono Genérico de Conmutador/Red -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-[#38BDF8]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75v-4.5m0 4.5h4.5m-4.5 0l6-6m-3 18c-8.284 0-15-6.716-15-15V4.5A2.25 2.25 0 014.5 2.25h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173l-1.293.97a1.062 1.062 0 00-.38 1.21 12.035 12.035 0 007.143 7.143c.441.162.928-.004 1.21-.38l.97-1.293a1.125 1.125 0 011.173-.417l4.423 1.106c.5.125.852.575.852 1.091V19.5a2.25 2.25 0 01-2.25 2.25h-2.25z" />
                    </svg>
                    <span class="font-bold text-xl tracking-tight">COMI<span class="text-[#38BDF8]">Center</span></span>
                </div>
                <div class="text-xs text-[#94A3B8] font-medium tracking-wide">
                    GESTIÓN DE INTERACCIONES Y DATOS
                </div>
            </nav>
        </header>

        <div class="flex items-center justify-center w-full lg:max-w-4xl transition-opacity opacity-100 duration-750">
            <main class="w-full bg-white dark:bg-[#1E293B] dark:text-[#F8FAFC] border border-[#E2E8F0] dark:border-[#334155] rounded-xl shadow-xl overflow-hidden flex flex-col lg:flex-row">
                
                <!-- Columna Izquierda: Formulario -->
                <div class="flex-1 p-6 lg:p-10 flex flex-col justify-between">
                    <div>
                        <div class="mb-8">
                            <h1 class="text-2xl font-bold text-[#334155] dark:text-[#F1F5F9] mb-2 tracking-tight">
                                Acceso al Sistema
                            </h1>
                            <p class="text-[#64748B] dark:text-[#94A3B8] text-sm">
                                Ingresa tus credenciales operativas para iniciar sesión en el entorno de COMI.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="mb-5 p-4 bg-[#FEF2F2] border border-[#FCA5A5] text-[#B91C1C] text-xs rounded-lg dark:bg-[#7F1D1D]/20 dark:border-[#991B1B] dark:text-[#FCA5A5]">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.verificar') }}" class="space-y-5">
                            @csrf

                            <div>
                                <label for="correo" class="block text-[11px] font-bold text-[#475569] dark:text-[#94A3B8] uppercase tracking-wider mb-1.5">
                                    Correo Operativo
                                </label>
                                <input 
                                    type="email" 
                                    id="correo" 
                                    name="correo" 
                                    value="{{ old('correo') }}"
                                    required 
                                    autofocus 
                                    placeholder="operador@sistema.local"
                                    class="w-full px-4 py-2.5 text-sm bg-[#F1F5F9] dark:bg-[#0F172A] border border-[#CBD5E1] dark:border-[#334155] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#38BDF8] focus:border-transparent dark:text-white transition-all duration-200"
                                />
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="password" class="block text-[11px] font-bold text-[#475569] dark:text-[#94A3B8] uppercase tracking-wider">
                                        Contraseña
                                    </label>
                                </div>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    placeholder="••••••••"
                                    class="w-full px-4 py-2.5 text-sm bg-[#F1F5F9] dark:bg-[#0F172A] border border-[#CBD5E1] dark:border-[#334155] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#38BDF8] focus:border-transparent dark:text-white transition-all duration-200"
                                />
                            </div>

                            <button 
                                type="submit" 
                                class="w-full bg-[#0284C7] hover:bg-[#0369A1] text-white font-semibold text-sm py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 mt-4"
                            >
                                Autenticar Sesión
                            </button>
                        </form>
                    </div>

                    <div class="mt-10 pt-5 border-t border-[#E2E8F0] dark:border-[#334155] text-xs text-[#94A3B8] flex justify-between items-center">
                        <span>Plataforma COMI v1.0</span>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-[#38BDF8] hover:text-[#7DD3FC] font-medium transition-colors">
                                Solicitar credenciales
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Columna Derecha: Información del Sistema -->
                <div class="lg:w-80 bg-[#0F172A] text-white p-6 lg:p-10 flex flex-col justify-between relative overflow-hidden border-l border-[#334155]">
                    
                    <!-- Elementos de Diseño de Fondo -->
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 rounded-full bg-gradient-to-br from-[#0284C7]/20 to-transparent blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-56 h-56 rounded-full bg-gradient-to-tr from-[#38BDF8]/10 to-transparent blur-3xl"></div>

                    <div class="relative z-10">
                        <div class="inline-block px-3 py-1 bg-[#334155] rounded-full text-[10px] font-bold tracking-widest text-[#38BDF8] mb-4">
                            MÓDULO C
                        </div>
                        <h3 class="text-2xl font-bold mb-3 tracking-tight">Core Operativo</h3>
                        <p class="text-sm text-[#94A3B8] leading-relaxed mb-8">
                            Plataforma independiente para el registro de interacciones, gestión de entidades telefónicas y supervisión de inteligencia conversacional.
                        </p>
                        
                        <!-- Panel de Estado -->
                        <div class="bg-[#1E293B] rounded-lg p-4 space-y-3 border border-[#334155]">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[#94A3B8] font-medium">Estado del Sistema:</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="relative flex h-2.5 w-2.5">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                    </span>
                                    <span class="text-emerald-400 font-semibold tracking-wide">En línea</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs border-t border-[#334155] pt-3">
                                <span class="text-[#94A3B8] font-medium">Puerto de Enlace:</span>
                                <span class="text-white font-mono bg-[#0F172A] px-2 py-0.5 rounded border border-[#334155]">127.0.0.1:8000</span>
                            </div>
                        </div>
                    </div>

                    <div class="relative z-10 mt-10 text-[11px] text-[#64748B] text-center font-medium">
                        © {{ date('Y') }} Plataforma COMI.<br>Todos los derechos reservados.
                    </div>
                </div>

            </main>
        </div>
    </body>
</html>