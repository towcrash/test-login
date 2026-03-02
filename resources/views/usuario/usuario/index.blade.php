@extends('layouts.app')

@section('tituloPagina', 'Usuarios')
@section('cabecera', 'Listado de Usuarios')

@section('accionGlobal')
    @sisadmin
    <a href="{{ route($rutaBase . 'create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Usuario
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
                   id="busquedaUsuario"
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
                <th>RUT</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Estado</th>
                <th>Vigencia</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->id }}</td>
                <td><strong>{{ $usuario->user }}</strong></td>
                <td>{{ $usuario->nombre }}</td>
                <td>{{ $usuario->rut ?? '-' }}</td>
                <td>{{ $usuario->email ?? '-' }}</td>
                <td>
                    @forelse ($usuario->roles as $rol)
                        <span class="badge badge-primary">{{ $rol->nombre }}</span>
                    @empty
                        <span class="text-muted">Sin roles</span>
                    @endforelse
                </td>
                <td>
                    @if ($usuario->bloqueado)
                        <span class="badge badge-danger">Bloqueado</span>
                    @else
                        <span class="badge badge-success">Activo</span>
                    @endif
                </td>
                <td>
                    @if ($usuario->vigencia)
                        {{ $usuario->vigencia->format('d/m/Y') }}
                        @if ($usuario->vigencia->isPast())
                            <span class="badge badge-warning">Expirado</span>
                        @endif
                    @else
                        <span class="text-muted">Sin límite</span>
                    @endif
                </td>
                <td class="text-center">
                    <a href="{{ route($rutaBase . 'show', $usuario) }}" class="btn btn-xs btn-info" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    @sisadmin
                    <a href="{{ route($rutaBase . 'edit', $usuario) }}" class="btn btn-xs btn-warning" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route($rutaBase . 'destroy', $usuario) }}" class="d-inline"
                        onsubmit="return confirm('¿{{ $usuario->bloqueado ? 'Desbloquear' : 'Bloquear' }} este usuario?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-xs {{ $usuario->bloqueado ? 'btn-success' : 'btn-danger' }}"
                                title="{{ $usuario->bloqueado ? 'Desbloquear' : 'Bloquear' }}">
                            <i class="fas {{ $usuario->bloqueado ? 'fa-check' : 'fa-ban' }}"></i>
                        </button>
                    </form>
                    @endsisadmin
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted">
                    @if(request('buscar'))
                        <i class="fas fa-search mr-1"></i> Sin resultados para "{{ request('buscar') }}".
                    @else
                        No hay usuarios registrados.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
    <small class="text-muted">
        {{ $usuarios->total() }} usuario(s)
        @if(request('buscar')) &mdash; filtrando por "{{ request('buscar') }}" @endif
    </small>
    {{ $usuarios->links() }}
</div>

@endsection

@push('acciones')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('busquedaUsuario');
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