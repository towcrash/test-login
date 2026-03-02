@extends('layouts.app')
@section('tituloPagina', 'Evaluaciones')
@section('cabecera', 'Evaluaciones')
@section('accionGlobal')
    @sisadmin
    <a href="{{ route($rutaBase . 'create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nueva Evaluación
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
                   id="busquedaEvaluacion"
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

@if ($esAdmin)

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>SID</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($evaluaciones as $eva)
                <tr>
                    <td>{{ $eva->id }}</td>
                    <td>{{ $eva->nombre }}</td>
                    <td class="text-muted">{{ $eva->descripcion ?? '—' }}</td>
                    <td><code>{{ $eva->sid ?? '—' }}</code></td>
                    <td>
                        @if ($eva->bloqueado)
                            <span class="badge badge-danger">Bloqueada</span>
                        @else
                            <span class="badge badge-success">Activa</span>
                        @endif
                    </td>
                    <td class="text-center text-nowrap">
                        <a href="{{ route($rutaBase . 'show', $eva) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route($rutaBase . 'edit', $eva) }}" class="btn btn-xs btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route($rutaBase . 'destroy', $eva) }}" class="d-inline"
                            onsubmit="return confirm('¿{{ $eva->bloqueado ? 'Desbloquear' : 'Bloquear' }} esta evaluación?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs {{ $eva->bloqueado ? 'btn-success' : 'btn-danger' }}"
                                    title="{{ $eva->bloqueado ? 'Desbloquear' : 'Bloquear' }}">
                                <i class="fas {{ $eva->bloqueado ? 'fa-check' : 'fa-ban' }}"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        @if(request('buscar'))
                            <i class="fas fa-search mr-1"></i> Sin resultados para "{{ request('buscar') }}".
                        @else
                            Sin evaluaciones.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
        <small class="text-muted">
            {{ $evaluaciones->total() }} evaluación(es)
            @if(request('buscar')) &mdash; filtrando por "{{ request('buscar') }}" @endif
        </small>
        {{ $evaluaciones->links() }}
    </div>

@else

    @forelse ($secciones as $seccion)
        <div class="mt-4">
            <h5 class="mb-1 text-secondary">
                @if ($seccion['subtitulo'] === 'Cliente')
                    <i class="fas fa-user-tie mr-1"></i>
                @elseif ($seccion['subtitulo'] === 'Contratista')
                    <i class="fas fa-building mr-1"></i>
                @elseif ($seccion['subtitulo'] === 'Evaluador')
                    <i class="fas fa-clipboard-check mr-1"></i>
                @else
                    <i class="fas fa-hard-hat mr-1"></i>
                @endif
                {{ $seccion['titulo'] }}
                <small class="text-muted font-weight-normal">({{ $seccion['subtitulo'] }})</small>
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-4">
                    <thead class="thead-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($seccion['evaluaciones'] as $eva)
                        <tr>
                            <td>
                                {{ $eva->nombre }}
                                <a href="{{ route($rutaBase . 'show', $eva) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                            <td class="text-muted">{{ $eva->descripcion ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">Sin evaluaciones.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <p class="text-muted">
            @if(!empty($buscar))
                <i class="fas fa-search mr-1"></i> Sin resultados para "{{ $buscar }}".
            @else
                No hay evaluaciones para mostrar.
            @endif
        </p>
    @endforelse

@endif

@endsection

@push('acciones')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('busquedaEvaluacion');
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