@extends('layouts.app')
@section('tituloPagina', 'Evaluadores')
@section('cabecera', 'Todos los Evaluadores')
@section('accionGlobal')
    @sisadmin
    <a href="{{ route($rutaBase . 'create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Evaluador
    </a>
    @endsisadmin
@endsection
@section('contenido')

<form id="formBuscar" method="GET" action="{{ route($rutaBase . 'index') }}">
    <div class="mb-3">
        <div class="input-group input-group-sm" style="max-width: 340px;">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
            </div>
            <input type="text"
                   id="busquedaEvaluador"
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

<div class="table-responsive">
    <table class="table table-bordered table-hover table-sm">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($evaluadores as $ev)
            <tr>
                <td>{{ $ev->id }}</td>
                <td>{{ $ev->usuario->user }}</td>
                <td>{{ $ev->usuario->nombre }}</td>
                <td>{{ $ev->cliente->nombre }}</td>
                <td>
                    @if ($ev->bloqueado)
                        <span class="badge badge-danger">Bloqueado</span>
                    @else
                        <span class="badge badge-success">Activo</span>
                    @endif
                </td>
                <td class="text-center">
                    @sisadmin
                    <a href="{{ route($rutaBase . 'show', $ev) }}" class="btn btn-xs btn-warning" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route($rutaBase . 'edit', $ev) }}" class="btn btn-xs btn-info">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route($rutaBase . 'destroy', $ev) }}" class="d-inline"
                        onsubmit="return confirm('¿{{ $ev->bloqueado ? 'Desbloquear' : 'Bloquear' }} este evaluador?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-xs {{ $ev->bloqueado ? 'btn-success' : 'btn-danger' }}"
                                title="{{ $ev->bloqueado ? 'Desbloquear' : 'Bloquear' }}">
                            <i class="fas {{ $ev->bloqueado ? 'fa-check' : 'fa-ban' }}"></i>
                        </button>
                    </form>
                    @endsisadmin
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">
                    @if(request('buscar'))
                        <i class="fas fa-search mr-1"></i> Sin resultados para "{{ request('buscar') }}".
                    @else
                        Sin evaluadores.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
    <small class="text-muted">
        {{ $evaluadores->total() }} evaluador(es)
        @if(request('buscar')) &mdash; filtrando por "{{ request('buscar') }}" @endif
    </small>
    {{ $evaluadores->links() }}
</div>

@endsection

@push('acciones')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('busquedaEvaluador');
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