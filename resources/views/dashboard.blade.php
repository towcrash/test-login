@extends('layouts.app')

@section('tituloPagina', 'Dashboard')
@section('cabecera', 'Inicio')

@section('contenido')

{{-- Saludo --}}
<p class="text-muted mb-4">
    <i class="far fa-calendar-alt mr-1"></i>
    {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    &nbsp;·&nbsp;
    <strong>{{ auth()->user()->nombre }}</strong>
</p>

{{-- Sin roles asignados --}}
@if(!isset($cliente) && !isset($evaluador) && !isset($contratista) && !isset($colaborador))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        No tienes roles asignados. Contacta al administrador del sistema.
    </div>
@endif

@isset($cliente)
    @include('layouts.partials.dashboard.cliente')
@endisset

@isset($evaluador)
    @include('layouts.partials.dashboard.evaluador')
@endisset

@isset($contratista)
    @include('layouts.partials.dashboard.contratista')
@endisset

@isset($colaborador)
    @include('layouts.partials.dashboard.colaborador')
@endisset

@endsection