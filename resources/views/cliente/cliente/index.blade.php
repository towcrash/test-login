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

@php $esSisAdmin = isset($clientes); @endphp

<form id="formBuscar" method="GET" action="{{ route($rutaBase . 'index') }}">
    <div class="mb-3">
        <div class="input-group input-group-sm" style="max-width: 340px;">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
            </div>
            <input type="text"
                   id="busquedaCliente"
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
        @include('layouts.partials.tableClientes', [
            'filas'         => $clientes,
            'mostrarEstado' => true,
        ])
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
        <small class="text-muted">
            {{ $clientes->total() }} cliente(s)
            @if(request('buscar')) &mdash; filtrando por "{{ request('buscar') }}" @endif
        </small>
        {{ $clientes->links() }}
    </div>
@else
    @if ($clientesComoUsuario->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2">
            <i class="fas fa-user mr-1"></i> Mis Clientes
        </h6>
        <div class="table-responsive">
            @include('layouts.partials.tableClientes', [
                'filas'         => $clientesComoUsuario,
                'mostrarEstado' => false,
            ])
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-4 gap-2">
            <small class="text-muted">{{ $clientesComoUsuario->total() }} cliente(s)</small>
            {{ $clientesComoUsuario->links() }}
        </div>
    @endif

    @if ($clientesComoEvaluador->isNotEmpty())
        <h6 class="text-muted text-uppercase mb-2 mt-4">
            <i class="fas fa-user-check mr-1"></i> Clientes donde soy Evaluador
        </h6>
        <div class="table-responsive">
            @include('layouts.partials.tableClientes', [
                'filas'         => $clientesComoEvaluador,
                'mostrarEstado' => false,
            ])
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
            <small class="text-muted">{{ $clientesComoEvaluador->total() }} cliente(s)</small>
            {{ $clientesComoEvaluador->links() }}
        </div>
    @endif

    @if ($clientesComoUsuario->isEmpty() && $clientesComoEvaluador->isEmpty())
        <p class="text-muted text-center mt-3">
            @if(request('buscar'))
                <i class="fas fa-search mr-1"></i> Sin resultados para "{{ request('buscar') }}".
            @else
                No tienes clientes asignados.
            @endif
        </p>
    @endif
@endif

@endsection

@push('acciones')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('busquedaCliente');
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