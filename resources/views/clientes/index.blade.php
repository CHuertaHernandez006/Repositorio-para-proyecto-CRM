<!-- resources/views/clientes/index.blade.php -->
@extends('layouts.app')

@section('title', 'Clientes - CRM')
@section('header-title', 'Gestión de Clientes')

@section('content')
    <div class="overflow-x-auto mt-4">
    <table class="min-w-full text-left text-sm font-light">
        <thead class="border-b font-medium dark:border-neutral-500">
            <tr>
                <th scope="col" class="px-6 py-4">ID</th>
                <th scope="col" class="px-6 py-4">Nombre</th>
                <th scope="col" class="px-6 py-4">Empresa</th>
                <th scope="col" class="px-6 py-4">Teléfono</th>
                <th scope="col" class="px-6 py-4">Correo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
            <tr class="border-b dark:border-neutral-500">
                <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $cliente->id_cliente }}</td>
                <td class="whitespace-nowrap px-6 py-4">{{ $cliente->nombre }} {{ $cliente->apellido_paterno }}</td>
                <td class="whitespace-nowrap px-6 py-4">{{ $cliente->empresa }}</td>
                <td class="whitespace-nowrap px-6 py-4">{{ $cliente->telefono_principal }}</td>
                <td class="whitespace-nowrap px-6 py-4">{{ $cliente->correo }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection