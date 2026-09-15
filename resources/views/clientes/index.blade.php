<!-- resources/views/clientes/index.blade.php -->
@extends('layouts.app')

@section('title', 'Clientes - CRM')

@section('content')
<!-- Incluir icono desde CDN para evitar SVG gigante -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .crm-card {
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        padding: 24px;
        border: 1px solid #f1f5f9;
        margin-top: 10px;
    }

    .crm-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
    }

    .crm-table thead tr {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .crm-table th {
        padding: 12px 16px;
        font-weight: 600;
    }

    .crm-table th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .crm-table th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

    .crm-table td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: #334155;
    }

    .search-box {
        position: relative;
        width: 100%;
        max-width: 320px;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .search-input {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        outline: none;
        background-color: #f8fafc;
        font-size: 0.875rem;
    }

    .btn-purple {
        background-color: #7c3aed;
        color: white;
        padding: 9px 18px;
        border-radius: 10px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-purple:hover { background-color: #6d28d9; }

    .badge-lead {
        background-color: #f3e8ff;
        color: #7e22ce;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 24px;
    }

    .page-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .page-btn.active {
        background-color: #7c3aed;
        color: white;
    }
</style>

<div style="padding: 24px;">
    <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;">Clientes</h2>

    <div class="crm-card">
        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="search-input" placeholder="Buscar cliente...">
            </div>

            <button class="btn-purple">
                <i class="bi bi-plus-lg"></i> Nuevo cliente
            </button>
        </div>

        <!-- Tabla -->
        <div style="overflow-x: auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Teléfono</th>
                        <th>Estado Lead</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td style="font-weight: 600;">{{ $cliente->id_cliente }}</td>
                            <td>{{ $cliente->nombre }} {{ $cliente->apellido_paterno }}</td>
                            <td>{{ $cliente->empresa ?? 'N/A' }}</td>
                            <td>{{ $cliente->telefono_principal ?? 'N/A' }}</td>
                            <td><span class="badge-lead">Interesado</span></td>
                            <td style="text-align: center;">
                                <button style="border:none; background:none; cursor:pointer; color:#94a3b8; margin-right: 8px;"><i class="bi bi-pencil"></i></button>
                                <button style="border:none; background:none; cursor:pointer; color:#94a3b8;"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 32px;">
                                No hay clientes registrados actualmente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="pagination-container">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">4</button>
            <button class="page-btn">5</button>
            <button class="page-btn">&gt;</button>
        </div>
    </div>
</div>
@endsection