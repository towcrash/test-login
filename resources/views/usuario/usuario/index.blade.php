@extends('layouts.app')

@section('tituloPagina', 'Usuarios')
@section('cabecera', 'Listado de Usuarios')

@section('accionGlobal')
    @sisadmin
    <a href="{{ route('usuario.usuario.create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Usuario
    </a>
    @endsisadmin
@endsection

@section('contenido')
<table class="table table-bordered table-hover table-sm">
    <thead class="thead-dark">
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
                <a href="{{ route('usuario.usuario.show', $usuario) }}" class="btn btn-xs btn-info" title="Ver">
                    <i class="fas fa-eye"></i>
                </a>
                @sisadmin
                <a href="{{ route('usuario.usuario.edit', $usuario) }}" class="btn btn-xs btn-warning" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <form method="POST" action="{{ route('usuario.usuario.destroy', $usuario) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar usuario {{ $usuario->user }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                @endsisadmin
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center text-muted">No hay usuarios registrados.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection