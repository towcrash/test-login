@extends('layouts.app')

@section('tituloPagina', 'Contratistas')
@section('cabecera', 'Listado de Contratistas')

@section('accionGlobal')
    @sisadmin
    <a href="{{ route($rutaBase .'create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Contratista
    </a>
    @endsisadmin
@endsection

@section('contenido')

@php $esSisAdmin = isset($contratistas); @endphp

@if ($esSisAdmin)
    @include('layouts.partials.tableContratista', ['filas' => $contratistas, 'mostrarEstado' => true])
@else
    @if ($contratistasComoUsuario->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2">
            <i class="fas fa-hard-hat mr-1"></i> Mis Contratistas
        </h6>
        @include('layouts.partials.tableContratista', ['filas' => $contratistasComoUsuario, 'mostrarEstado' => false])
    @endif

    @if ($contratistasComoEvaluador->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2 {{ $contratistasComoUsuario->isNotEmpty() ? 'mt-4' : '' }}">
            <i class="fas fa-user-check mr-1"></i> Contratistas de mis Clientes (Evaluador)
        </h6>
        @include('layouts.partials.tableContratista', ['filas' => $contratistasComoEvaluador, 'mostrarEstado' => false])
    @endif

    @if ($contratistasComoUsuario->isEmpty() && $contratistasComoEvaluador->isEmpty())
        <p class="text-muted text-center mt-3">No tienes contratistas asignados.</p>
    @endif
@endif

@endsection