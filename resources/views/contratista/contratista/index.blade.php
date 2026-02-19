@extends('layouts.app')
@section('tituloPagina', 'Contratistas')
@section('cabecera', 'Listado de Contratistas')
@section('accionGlobal')
    @sisadmin
    <a href="{{ route('contratista.contratista.create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Contratista
    </a>
    @endsisadmin
@endsection
@section('contenido')
<table class="table table-bordered table-hover table-sm">
    <thead class="thead-dark">
        <tr>
            <th>#</th><th>Nombre</th><th>RUT</th>
            <th>Colaboradores</th><th>Clientes</th><th>Estado</th><th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($contratistas as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td><strong>{{ $c->nombre }}</strong></td>
            <td>{{ $c->rut }}</td>
            <td><span class="badge badge-secondary">{{ $c->colaboradores_count }}</span></td>
            <td><span class="badge badge-secondary">{{ $c->clientes_count }}</span></td>
            <td>
                @if ($c->bloqueado)
                    <span class="badge badge-danger">Bloqueado</span>
                @else
                    <span class="badge badge-success">Activo</span>
                @endif
            </td>
            <td class="text-center">
                <a href="{{ route('contratista.contratista.show', $c) }}" class="btn btn-xs btn-info">
                    <i class="fas fa-eye"></i>
                </a>
                @sisadmin
                <a href="{{ route('contratista.contratista.edit', $c) }}" class="btn btn-xs btn-warning">
                    <i class="fas fa-edit"></i>
                </a>
                <form method="POST" action="{{ route('contratista.contratista.destroy', $c) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar {{ $c->nombre }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                </form>
                @endsisadmin
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted">Sin contratistas.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection