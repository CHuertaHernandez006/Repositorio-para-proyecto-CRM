@extends('layouts.app')

@section('title', 'Dashboard - CRM')
@section('header-title', 'Dashboard')

@section('content')

    {{-- Encabezado general de bienvenida 
    <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
        <h1 style="color: #16a34a; font-size: 1.4rem; margin: 0;">¡Acceso Correcto!</h1>
        <p style="color: #4b5563; margin-top: 8px; margin-bottom: 0;">
            Bienvenido, {{ $usuario->email }}.
        </p>
    </div> --}}

    {{-- Renderizado condicional según el rol del usuario --}}
    @switch($usuario->id_rol)
        @case(1)
            {{-- VISTA ADMINISTRADOR --}}
            <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb;">
                <h3 style="margin-top: 0; color: #111827;">Panel de Administrador</h3>
                <p style="color: #6b7280;">Vista global del sistema, gestión de empresas y configuración general.</p>
            </div>
            @break

        @case(2)
            {{-- VISTA SUPERVISOR --}}
            <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb;">
                <h3 style="margin-top: 0; color: #111827;">Panel de Supervisor</h3>
                <p style="color: #6b7280;">Monitoreo de operarios asignados, llamadas y estado de campañas.</p>
            </div>
            @break

        @case(3)
    {{-- VISTA OPERADOR (Métricas Operativas) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-top: 16px;">
        {{-- Tarjeta 1: Total de Llamadas --}}
        <div style="background: #111c2e; border: 1px solid #1e293b; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #64748b; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 8px 0;">LLAMADAS REALIZADAS</p>
                <h2 style="color: #f8fafc; font-size: 1.8rem; font-weight: 700; margin: 0;">{{ $metricasData['totalLlamadas'] ?? 0 }}</h2>
            </div>
            <div style="background: #1e293b; color: #38bdf8; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="phone-call"></i>
            </div>
        </div>

        {{-- Tarjeta 2: Tiempo Promedio --}}
        <div style="background: #111c2e; border: 1px solid #1e293b; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #64748b; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 8px 0;">TIEMPO PROMEDIO</p>
                <h2 style="color: #f8fafc; font-size: 1.8rem; font-weight: 700; margin: 0;">{{ $metricasData['tiempoPromedio'] ?? '00:00' }}</h2>
            </div>
            <div style="background: #1e293b; color: #34d399; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="clock"></i>
            </div>
        </div>

        {{-- Tarjeta 3: Prospectos Contactados --}}
        <div style="background: #111c2e; border: 1px solid #1e293b; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #64748b; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 8px 0;">PROSPECTOS ATENDIDOS</p>
                <h2 style="color: #f8fafc; font-size: 1.8rem; font-weight: 700; margin: 0;">{{ $metricasData['prospectos'] ?? 0 }}</h2>
            </div>
            <div style="background: #1e293b; color: #a855f7; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="users"></i>
            </div>
        </div>
    </div>
    @break

        @default
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 16px; border-radius: 8px;">
                No se ha asignado un rol válido a esta cuenta para mostrar el panel correspondiente.
            </div>
    @endswitch
@endsection