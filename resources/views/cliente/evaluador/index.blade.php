@extends('layouts.app')
@section('tituloPagina', 'Evaluadores')
@section('cabecera', 'Todos los Evaluadores')
@section('accionGlobal')
    @sisadmin
    <a href="{{ route('cliente.evaluador.create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Evaluador
    </a>
    @endsisadmin
@endsection
@section('contenido')
<table class="table table-bordered table-hover table-sm">
    <thead class="thead-dark">
        <tr><th>#</th><th>Usuario</th><th>Nombre</th><th>Cliente</th><th>Estado</th><th></th></tr>
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
                <a href="{{ route('cliente.evaluador.edit', $ev) }}" class="btn btn-xs btn-warning">
                    <i class="fas fa-edit"></i>
                </a>
                <form method="POST" action="{{ route('cliente.evaluador.destroy', $ev) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar este evaluador?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                </form>
                @endsisadmin
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted">Sin evaluadores.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection