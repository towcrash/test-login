@extends('layouts.app')
@section('tituloPagina', 'Colaboradores')
@section('cabecera', 'Todos los Colaboradores')
@section('accionGlobal')
    @sisadmin
    <a href="{{ route('contratista.colaborador.create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Colaborador
    </a>
    @endsisadmin
@endsection
@section('contenido')
<table class="table table-bordered table-hover table-sm">
    <thead class="thead-dark">
        <tr><th>#</th><th>Usuario</th><th>Nombre</th><th>Contratista</th><th>Estado</th><th></th></tr>
    </thead>
    <tbody>
        @forelse ($colaboradores as $col)
        <tr>
            <td>{{ $col->id }}</td>
            <td>{{ $col->usuario->user }}</td>
            <td>{{ $col->usuario->nombre }}</td>
            <td>{{ $col->contratista->nombre }}</td>
            <td>
                @if ($col->bloqueado)
                    <span class="badge badge-danger">Bloqueado</span>
                @else
                    <span class="badge badge-success">Activo</span>
                @endif
            </td>
            <td class="text-center">
                @sisadmin
                <a href="{{ route('contratista.colaborador.edit', $col) }}" class="btn btn-xs btn-warning">
                    <i class="fas fa-edit"></i>
                </a>
                <form method="POST" action="{{ route('contratista.colaborador.destroy', $col) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar este colaborador?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                </form>
                @endsisadmin
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted">Sin colaboradores.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection