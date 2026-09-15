<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selección de Registro - CRM System</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f3f4f6] text-[#1e293b] font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-4xl">
        <!-- Encabezado / Branding -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-[#1e293b] mb-2">CRM System</h1>
            <p class="text-gray-500 text-sm">Selecciona el tipo de cuenta con el que deseas registrarte</p>
        </div>

        <!-- Tarjeta Principal (Estilo Dashboard) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 lg:p-10">
            
            <div class="mb-8 border-b border-gray-100 pb-4">
                <h2 class="text-xl font-semibold text-[#1e293b]">Registro de Usuario</h2>
                <p class="text-xs text-gray-500 mt-1">Elige el perfil que se adapte a tus responsabilidades dentro de la plataforma.</p>
            </div>

            <!-- Opciones de Registro -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Tipo Usuario 1 -->
                <div class="border border-gray-200 hover:border-[#6366f1] rounded-xl p-6 transition-all duration-200 hover:shadow-md flex flex-col justify-between group bg-white">
                    <div>
                        <div class="w-12 h-12 bg-indigo-50 text-[#6366f1] rounded-lg flex items-center justify-center mb-4 group-hover:bg-[#6366f1] group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-lg text-[#1e293b] mb-1">Operador</h3>
                        <p class="text-xs text-gray-500 leading-relaxed mb-6">
                            Acceso estándar a las funciones de atención, gestión de llamadas y clientes asignados.
                        </p>
                    </div>
                    <a href="{{ Route::has('seleccion.registro_operador') ? route('seleccion.registro_operador') : '#' }}" class="w-full text-center bg-gray-50 hover:bg-[#6366f1] hover:text-white text-[#1e293b] text-sm font-medium py-2.5 px-4 rounded-lg border border-gray-200 hover:border-[#6366f1] transition-all duration-200 block">
                        Registrarse
                    </a>
                </div>

                <!-- Tipo Usuario 2 -->
                <div class="border border-gray-200 hover:border-[#6366f1] rounded-xl p-6 transition-all duration-200 hover:shadow-md flex flex-col justify-between group bg-white">
                    <div>
                        <div class="w-12 h-12 bg-indigo-50 text-[#6366f1] rounded-lg flex items-center justify-center mb-4 group-hover:bg-[#6366f1] group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-lg text-[#1e293b] mb-1">Supervisor</h3>
                        <p class="text-xs text-gray-500 leading-relaxed mb-6">
                            Permisos para supervisión de campanias, métricas generales y gestión de equipos.
                        </p>
                    </div>
                    <a href="#" class="w-full text-center bg-gray-50 hover:bg-[#6366f1] hover:text-white text-[#1e293b] text-sm font-medium py-2.5 px-4 rounded-lg border border-gray-200 hover:border-[#6366f1] transition-all duration-200 block">
                        Registrarse
                    </a>
                </div>

                <!-- Tipo Usuario 3 -->
                <div class="border border-gray-200 hover:border-[#6366f1] rounded-xl p-6 transition-all duration-200 hover:shadow-md flex flex-col justify-between group bg-white">
                    <div>
                        <div class="w-12 h-12 bg-indigo-50 text-[#6366f1] rounded-lg flex items-center justify-center mb-4 group-hover:bg-[#6366f1] group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-lg text-[#1e293b] mb-1">Administrador</h3>
                        <p class="text-xs text-gray-500 leading-relaxed mb-6">
                            Acceso administrativo completo a la configuración del sistema y reportes globales.
                        </p>
                    </div>
                    <a href="#" class="w-full text-center bg-gray-50 hover:bg-[#6366f1] hover:text-white text-[#1e293b] text-sm font-medium py-2.5 px-4 rounded-lg border border-gray-200 hover:border-[#6366f1] transition-all duration-200 block">
                        Registrarse
                    </a>
                </div>

            </div>

            <!-- Botón para regresar al Login -->
            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400">
                <a href="{{ route('login') }}" class="text-[#6366f1] hover:underline font-medium flex items-center gap-1">
                    ← Volver al inicio de sesión
                </a>
                <span>CRM System v1.0</span>
            </div>

        </div>
    </div>

</body>
</html>