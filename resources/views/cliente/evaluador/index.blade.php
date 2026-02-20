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
<table class="table table-bordered table-hover table-sm">
    <thead class="thead-dark">
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
                <a href="{{ route($rutaBase . 'show', $ev) }}" class="btn btn-xs btn-info" title="Ver">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route($rutaBase . 'edit', $ev) }}" class="btn btn-xs btn-warning">
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
        <tr><td colspan="6" class="text-center text-muted">Sin evaluadores.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection