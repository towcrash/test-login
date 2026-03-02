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

<form id="formBuscar" method="GET" action="{{ route($rutaBase . 'index') }}">
    <div class="mb-3">
        <div class="input-group input-group-sm" style="max-width: 340px;">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
            </div>
            <input type="text"
                   id="busquedaContratista"
                   name="buscar"
                   value="{{ request('buscar') }}"
                   class="form-control border-left-0"
                   placeholder="Buscar por nombre..."
                   autocomplete="off">
            @if(request('buscar'))
            <div class="input-group-append">
                <button type="button" id="btnLimpiarBuscar" class="btn btn-outline-secondary btn-sm" title="Limpiar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif
        </div>
    </div>
</form>

@if ($esSisAdmin)
    <div class="table-responsive">
        @include('layouts.partials.tableContratista', ['filas' => $contratistas, 'mostrarEstado' => true])
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
        <small class="text-muted">
            {{ $contratistas->total() }} contratista(s)
            @if(request('buscar')) &mdash; filtrando por "{{ request('buscar') }}" @endif
        </small>
        {{ $contratistas->links() }}
    </div>
@else
    @if ($contratistasComoUsuario->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2">
            <i class="fas fa-hard-hat mr-1"></i> Mis Contratistas
        </h6>
        <div class="table-responsive">
            @include('layouts.partials.tableContratista', ['filas' => $contratistasComoUsuario, 'mostrarEstado' => false])
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-2">
            <small class="text-muted">{{ $contratistasComoUsuario->total() }} contratista(s)</small>
            {{ $contratistasComoUsuario->links() }}
        </div>
    @endif

    @if ($contratistasComoCliente->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2 mt-4">
            <i class="fas fa-building mr-1"></i> Contratistas de mis clientes
        </h6>
        <div class="table-responsive">
            @include('layouts.partials.tableContratista', ['filas' => $contratistasComoCliente, 'mostrarEstado' => false])
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-2">
            <small class="text-muted">{{ $contratistasComoCliente->total() }} contratista(s)</small>
            {{ $contratistasComoCliente->links() }}
        </div>
    @endif

    @if ($contratistasComoEvaluador->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2 mt-4">
            <i class="fas fa-user-check mr-1"></i> Mis Contratistas en los cuales soy evaluador
        </h6>
        <div class="table-responsive">
            @include('layouts.partials.tableContratista', ['filas' => $contratistasComoEvaluador, 'mostrarEstado' => false])
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
            <small class="text-muted">{{ $contratistasComoEvaluador->total() }} contratista(s)</small>
            {{ $contratistasComoEvaluador->links() }}
        </div>
    @endif

    @if ($contratistasComoUsuario->isEmpty() && $contratistasComoCliente->isEmpty() && $contratistasComoEvaluador->isEmpty())
        <p class="text-muted text-center mt-3">
            @if(request('buscar'))
                <i class="fas fa-search mr-1"></i> Sin resultados para "{{ request('buscar') }}".
            @else
                No tienes contratistas asignados.
            @endif
        </p>
    @endif
@endif

@endsection

@push('acciones')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('busquedaContratista');
    var form  = document.getElementById('formBuscar');
    if (!input || !form) return;
    var timer = null;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { form.submit(); }, 400);
    });
    var btn = document.getElementById('btnLimpiarBuscar');
    if (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            input.value = '';
            form.submit();
        });
    }
});
</script>
@endpush