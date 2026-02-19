@extends('layouts.app')

@section('tituloPagina', 'Clientes')
@section('cabecera', 'Listado de Clientes')

@section('accionGlobal')
    @sisadmin
    <a href="{{ route('cliente.cliente.create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Cliente
    </a>
    @endsisadmin
@endsection

@section('contenido')
<table class="table table-bordered table-hover table-sm">
    <thead class="thead-dark">
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>RUT</th>
            <th>Evaluaciones</th>
            <th>Evaluadores</th>
            <th>Contratistas</th>
            @sisadmin
            <th>Estado</th>
            @endsisadmin
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($clientes as $cliente)
        <tr>
            <td>{{ $cliente->id }}</td>
            <td><strong>{{ $cliente->nombre }}</strong></td>
            <td>{{ $cliente->rut }}</td>
            <td>
                @if($cliente->evaluaciones_count == 1)
                    <span class="badge badge-primary">{{ $cliente->evaluaciones_count }} evaluación</span>
                @else
                    <span class="badge badge-primary">{{ $cliente->evaluaciones_count }} evaluaciones</span>
                @endif
            </td>
            <td>
                @if($cliente->evaluadores_count == 1)
                    <span class="badge badge-primary">{{ $cliente->evaluadores_count }} evaluador</span>
                @else
                    <span class="badge badge-primary">{{ $cliente->evaluadores_count }} evaluadores</span>
                @endif
            </td>
            <td>
                @if($cliente->contratistas_count == 1)
                    <span class="badge badge-primary">{{ $cliente->contratistas_count }} contratista</span>
                @else
                    <span class="badge badge-primary">{{ $cliente->contratistas_count }} contratistas</span>
                @endif
            </td>
            @sisadmin
            <td>
                @if ($cliente->bloqueado)
                    <span class="badge badge-danger">Bloqueado</span>
                @else
                    <span class="badge badge-success">Activo</span>
                @endif
            </td>
            @endsisadmin
            <td class="text-center">
                <a href="{{ route('cliente.cliente.show', $cliente) }}" class="btn btn-xs btn-info" title="Ver">
                    <i class="fas fa-eye"></i>
                </a>
                @sisadmin
                <a href="{{ route('cliente.cliente.edit', $cliente) }}" class="btn btn-xs btn-warning" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <form method="POST" action="{{ route('cliente.cliente.destroy', $cliente) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar cliente {{ $cliente->nombre }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                </form>
                @endsisadmin
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted">No hay clientes registrados.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection