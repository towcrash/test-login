@extends('layouts.app')

@section('tituloPagina', 'Detalle de Usuario')
@section('cabecera', $usuario->nombre)

@section('accionGlobal')
    @sisadmin
    <a href="{{ route($rutaBase . 'edit', $usuario) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit mr-1"></i> Editar
    </a>
    @endsisadmin
    <a href="{{ route($rutaBase . 'index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
@endsection

@section('contenido')
<div class="row">
    {{-- Info general --}}
    <div class="col-md-4">
        <x-card titulo="Información General">
            <table class="table table-sm table-borderless mb-0">
                <tr><th>Usuario</th><td>{{ $usuario->user }}</td></tr>
                <tr><th>Nombre</th><td>{{ $usuario->nombre }}</td></tr>
                <tr><th>RUT</th><td>{{ $usuario->rut ?? '-' }}</td></tr>
                <tr><th>Email</th><td>{{ $usuario->email ?? '-' }}</td></tr>
                <tr>
                    <th>Estado</th>
                    <td>
                        @if ($usuario->bloqueado)
                            <span class="badge badge-danger">Bloqueado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Vigencia</th>
                    <td>
                        @if ($usuario->vigencia)
                            {{ $usuario->vigencia->format('d/m/Y') }}
                            @if ($usuario->vigencia->isPast())
                                <span class="badge badge-warning">Expirado</span>
                            @endif
                        @else
                            Sin límite
                        @endif
                    </td>
                </tr>
            </table>
        </x-card>

        <x-card titulo="Roles Asignados">
            @forelse ($usuario->roles as $rol)
                <span class="badge badge-primary mr-1 mb-1" style="font-size:.9em">{{ $rol->nombre }}</span>
            @empty
                <p class="text-muted mb-0">Sin roles asignados.</p>
            @endforelse
        </x-card>
    </div>

    {{-- Clientes --}}
    <div class="col-md-4">
        <x-card titulo="Como Evaluador">
            @forelse ($usuario->evaluadores as $ev)
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span>
                        <i class="fas fa-building mr-1 text-primary"></i>
                        {{ $ev->cliente->nombre }}
                    </span>
                    @if ($ev->bloqueado)
                        <span class="badge badge-danger">Bloqueado</span>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">No es evaluador en ningún cliente.</p>
            @endforelse
        </x-card>
    </div>

    {{-- Contratistas --}}
    <div class="col-md-4">
        <x-card titulo="Como Colaborador">
            @forelse ($usuario->colaboradores as $col)
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span>
                        <i class="fas fa-hard-hat mr-1 text-warning"></i>
                        {{ $col->contratista->nombre }}
                    </span>
                    @if ($col->bloqueado)
                        <span class="badge badge-danger">Bloqueado</span>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">No es colaborador en ningún contratista.</p>
            @endforelse
        </x-card>
    </div>
</div>
@endsection