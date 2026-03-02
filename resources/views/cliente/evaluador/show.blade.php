@extends('layouts.app')

@section('tituloPagina', 'Detalle Evaluador')
@section('cabecera', $evaluador->usuario->nombre)

@section('accionGlobal')
    <a href="{{ route('cliente.evaluador.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
@endsection

@section('contenido')
<div class="row">

    {{-- Info del usuario evaluador --}}
    <div class="col-12 col-md-4 mb-4">
        <x-card titulo="Datos del Evaluador">
            <table class="table table-sm table-borderless mb-0">
                <tr><th>Usuario</th><td>{{ $evaluador->usuario->user }}</td></tr>
                <tr><th>Nombre</th><td>{{ $evaluador->usuario->nombre }}</td></tr>
                <tr><th>Email</th><td>{{ $evaluador->usuario->email ?? '-' }}</td></tr>
                <tr>
                    <th>Estado</th>
                    <td>
                        @if ($evaluador->bloqueado)
                            <span class="badge badge-danger">Bloqueado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                </tr>
            </table>
        </x-card>
    </div>

    {{-- Clientes y evaluaciones por cliente --}}
    <div class="col-12 col-md-8 mb-4">
        <x-card titulo="Clientes y Evaluaciones asignadas ({{ $todasInstancias->total() }} cliente(s) en total)">
            @forelse ($todasInstancias as $instancia)
            <div class="mb-3 p-3 border rounded">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">
                        <i class="fas fa-building mr-1 text-primary"></i>
                        <a href="{{ route('cliente.cliente.show', $instancia->cliente) }}">
                            {{ $instancia->cliente->nombre }}
                        </a>
                    </h6>
                    <div class="mt-1">
                        @if ($instancia->bloqueado)
                            <span class="badge badge-danger">Evaluador bloqueado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                        <span class="badge badge-info ml-1">
                            {{ $instancia->evaluaciones->count() }} evaluación(es)
                        </span>
                    </div>
                </div>

                @forelse ($instancia->evaluaciones as $evaluacion)
                <div class="d-flex flex-wrap justify-content-between align-items-center py-1 pl-3 border-left ml-2">
                    <div>
                        <i class="fas fa-clipboard-list mr-1 text-secondary"></i>
                        <strong>{{ $evaluacion->nombre }}</strong>
                        @if ($evaluacion->descripcion)
                            <br><small class="text-muted pl-4">{{ $evaluacion->descripcion }}</small>
                        @endif
                    </div>
                    <div class="mt-1">
                        @if ($evaluacion->bloqueado)
                            <span class="badge badge-danger">Bloqueada</span>
                        @else
                            <span class="badge badge-success">Activa</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted pl-3 mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Sin evaluaciones asignadas en este cliente.
                </p>
                @endforelse
            </div>
            @empty
            <p class="text-muted mb-0">Este usuario no tiene instancias como evaluador.</p>
            @endforelse

            @if ($todasInstancias->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top gap-2">
                <small class="text-muted">
                    Página {{ $todasInstancias->currentPage() }} de {{ $todasInstancias->lastPage() }}
                </small>
                {{ $todasInstancias->links() }}
            </div>
            @endif
        </x-card>
    </div>

</div>
@endsection