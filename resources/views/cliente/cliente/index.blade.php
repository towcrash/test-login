@extends('layouts.app')

@section('tituloPagina', 'Clientes')
@section('cabecera', 'Listado de Clientes')

@section('accionGlobal')
    @sisadmin
    <a href="{{ route($rutaBase . 'create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Cliente
    </a>
    @endsisadmin
@endsection

@section('contenido')

@php
    $esSisAdmin = isset($clientes);
@endphp

@if ($esSisAdmin)
    {{-- ── Vista SisAdmin: tabla única ── --}}
    @include('layouts.partials.tableClientes', [
        'filas'      => $clientes,
        'mostrarEstado' => true,
    ])

@else
    {{-- ── Vista Cliente ── --}}
    @if ($clientesComoUsuario->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2">
            <i class="fas fa-user mr-1"></i> Mis Clientes
        </h6>
        @include('layouts.partials.tableClientes', [
            'filas'         => $clientesComoUsuario,
            'mostrarEstado' => false,
        ])
    @endif

    {{-- ── Vista Evaluador ── --}}
    @if ($clientesComoEvaluador->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2 {{ $clientesComoUsuario->isNotEmpty() ? 'mt-4' : '' }}">
            <i class="fas fa-user-check mr-1"></i> Clientes donde soy Evaluador
        </h6>
        @include('layouts.partials.tableClientes', [
            'filas'         => $clientesComoEvaluador,
            'mostrarEstado' => false,
        ])
    @endif

    @if ($clientesComoUsuario->isEmpty() && $clientesComoEvaluador->isEmpty())
        <p class="text-muted text-center mt-3">No tienes clientes asignados.</p>
    @endif

@endif

@endsection