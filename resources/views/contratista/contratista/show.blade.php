@extends('layouts.app')

@section('tituloPagina', 'Contratista')
@section('cabecera', $contratista->nombre)

@section('accionGlobal')
    @sisadmin
    <a href="{{ route('contratista.contratista.edit', $contratista) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit mr-1"></i> Editar
    </a>
    @endsisadmin
    <a href="{{ route('contratista.contratista.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
@endsection

@section('contenido')
<div class="row">

    <div class="col-md-4">
        <x-card titulo="Datos del Contratista">
            <table class="table table-sm table-borderless mb-0">
                <tr><th>Nombre</th><td>{{ $contratista->nombre }}</td></tr>
                <tr><th>RUT</th><td>{{ $contratista->rut }}</td></tr>
                <tr>
                    <th>Estado</th>
                    <td>
                        @if ($contratista->bloqueado)
                            <span class="badge badge-danger">Bloqueado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                </tr>
            </table>
        </x-card>

        @sisadmin
        <x-card titulo="Asignar Colaborador">
            <form method="POST" action="{{ route('contratista.contratista.asignarColaborador', $contratista) }}">
                @csrf
                <div class="input-group input-group-sm">
                    <select name="Usuario_id" class="form-control select2" style="width:100%" required>
                        <option value="" disabled selected>— Seleccionar usuario —</option>
                        @foreach ($usuariosDisponibles as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-success btn-sm" type="submit">
                            <i class="fas fa-plus"></i> Asignar
                        </button>
                    </div>
                </div>
            </form>
        </x-card>
        @endsisadmin

        <x-card titulo="Clientes Asociados">
            @forelse ($contratista->clientes as $cliente)
                <div class="mb-1">
                    <i class="fas fa-building mr-1 text-primary"></i>
                    <a href="{{ route('cliente.cliente.show', $cliente) }}">{{ $cliente->nombre }}</a>
                </div>
            @empty
                <p class="text-muted mb-0">Sin clientes asociados.</p>
            @endforelse
        </x-card>
    </div>

    <div class="col-md-8">
        <x-card titulo="Colaboradores ({{ $contratista->colaboradores->count() }})">
            @forelse ($contratista->colaboradores as $colaborador)
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div>
                    <strong>{{ $colaborador->usuario->nombre }}</strong><br>
                    <small class="text-muted">{{ $colaborador->usuario->user }}</small>
                    @if ($colaborador->usuario->email)
                        <small class="text-muted ml-2">— {{ $colaborador->usuario->email }}</small>
                    @endif
                </div>
                @sisadmin
                <form method="POST"
                      action="{{ route('contratista.contratista.desasignarColaborador', [$contratista, $colaborador]) }}"
                      onsubmit="return confirm('¿Quitar colaborador?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger"><i class="fas fa-unlink"></i></button>
                </form>
                @endsisadmin
            </div>
            @empty
            <p class="text-muted mb-0">Sin colaboradores asignados.</p>
            @endforelse
        </x-card>
    </div>

</div>
@endsection