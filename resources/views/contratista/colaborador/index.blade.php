@extends('layouts.app')
@section('tituloPagina', 'Colaboradores')
@section('cabecera', 'Colaboradores')
@section('accionGlobal')
    @sisadmin
    <a href="{{ route($rutaBase . 'create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Colaborador
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
                   id="busquedaColaborador"
                   name="buscar"
                   value="{{ $buscar ?? '' }}"
                   class="form-control border-left-0"
                   placeholder="Buscar por nombre..."
                   autocomplete="off">
            @if(!empty($buscar))
            <div class="input-group-append">
                <button type="button" id="btnLimpiarBuscar" class="btn btn-outline-secondary btn-sm" title="Limpiar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif
        </div>
    </div>
</form>

@if ($seccionContratista->isNotEmpty())

    @if ($seccionEvaluador->isNotEmpty())
        <h4 class="mb-3">
            <i class="fas fa-hard-hat mr-1"></i> Como Contratista
        </h4>
    @endif

    @foreach ($seccionContratista as $contratista)
        <div class="mt-4">
            <h5 class="mb-2 text-secondary">
                <i class="fas fa-hard-hat mr-1"></i> {{ $contratista->nombre }}
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-2">
                    <thead class="thead-light">
                        <tr>
                            @sisadmin <th>#</th> @endsisadmin
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            @sisadmin <th>Estado</th><th></th> @endsisadmin
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contratista->colaboradoresFiltrados as $col)
                        <tr>
                            @sisadmin <td>{{ $col->id }}</td> @endsisadmin
                            <td>
                                {{ $col->usuario->user }}
                                <a href="{{ route($rutaBase . 'show', $col) }}" class="btn btn-xs btn-warning" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                            <td>{{ $col->usuario->nombre }}</td>
                            <td>{{ $col->usuario->email }}</td>
                            @sisadmin
                            <td>
                                @if ($col->bloqueado)
                                    <span class="badge badge-danger">Bloqueado</span>
                                @else
                                    <span class="badge badge-success">Activo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route($rutaBase . 'edit', $col) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route($rutaBase . 'destroy', $col) }}" class="d-inline"
                                    onsubmit="return confirm('¿{{ $col->bloqueado ? 'Desbloquear' : 'Bloquear' }} este colaborador?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs {{ $col->bloqueado ? 'btn-success' : 'btn-danger' }}"
                                            title="{{ $col->bloqueado ? 'Desbloquear' : 'Bloquear' }}">
                                        <i class="fas {{ $col->bloqueado ? 'fa-check' : 'fa-ban' }}"></i>
                                    </button>
                                </form>
                            </td>
                            @endsisadmin
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Sin colaboradores.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

@endif

@if ($seccionEvaluador->isNotEmpty())

    @if ($seccionContratista->isNotEmpty())
        <hr class="my-4">
        <h4 class="mb-3 text-primary">
            <i class="fas fa-clipboard-check mr-1"></i> Como Evaluador
        </h4>
    @endif

    @foreach ($seccionEvaluador as $evaluacion)
        <div class="mt-4">
            <h5 class="mb-2 text-primary">
                <i class="fas fa-clipboard-list mr-1"></i> {{ $evaluacion->nombre }}
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-2">
                    <thead class="thead-light">
                        <tr>
                            @sisadmin <th>#</th> @endsisadmin
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Contratista</th>
                            @sisadmin <th>Estado</th><th></th> @endsisadmin
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($evaluacion->colaboradoresFiltrados as $col)
                        <tr>
                            @sisadmin <td>{{ $col->id }}</td> @endsisadmin
                            <td>
                                {{ $col->usuario->user }}
                                <a href="{{ route($rutaBase . 'show', $col) }}" class="btn btn-xs btn-warning" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                            <td>{{ $col->usuario->nombre }}</td>
                            <td>{{ $col->usuario->email }}</td>
                            <td>{{ $col->contratista->nombre }}</td>
                            @sisadmin
                            <td>
                                @if ($col->bloqueado)
                                    <span class="badge badge-danger">Bloqueado</span>
                                @else
                                    <span class="badge badge-success">Activo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route($rutaBase . 'edit', $col) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route($rutaBase . 'destroy', $col) }}" class="d-inline"
                                    onsubmit="return confirm('¿{{ $col->bloqueado ? 'Desbloquear' : 'Bloquear' }} este colaborador?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs {{ $col->bloqueado ? 'btn-success' : 'btn-danger' }}"
                                            title="{{ $col->bloqueado ? 'Desbloquear' : 'Bloquear' }}">
                                        <i class="fas {{ $col->bloqueado ? 'fa-check' : 'fa-ban' }}"></i>
                                    </button>
                                </form>
                            </td>
                            @endsisadmin
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Sin colaboradores.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

@endif

@if ($seccionContratista->isEmpty() && $seccionEvaluador->isEmpty())
    <p class="text-muted">
        @if(!empty($buscar))
            <i class="fas fa-search mr-1"></i> Sin resultados para "{{ $buscar }}".
        @else
            No hay colaboradores para mostrar.
        @endif
    </p>
@endif

@endsection

@push('acciones')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('busquedaColaborador');
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