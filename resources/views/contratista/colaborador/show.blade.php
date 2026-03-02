@extends('layouts.app')
@section('tituloPagina', 'Colaborador')
@section('cabecera', 'Detalle de Colaborador')
@section('accionGlobal')
    <a href="{{ route($rutaBase . 'index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
    @sisadmin
    <a href="{{ route($rutaBase . 'edit', $colaborador) }}" class="btn btn-warning btn-sm ml-1">
        <i class="fas fa-edit mr-1"></i> Editar
    </a>
    @endsisadmin
@endsection
@section('contenido')

<div class="row">

    <div class="col-12 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-user mr-1"></i> Usuario
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="pl-3" style="width:35%">Usuario</th>
                                <td>{{ $colaborador->usuario->user }}</td>
                            </tr>
                            <tr class="bg-light">
                                <th class="pl-3">Nombre</th>
                                <td>{{ $colaborador->usuario->nombre }}</td>
                            </tr>
                            <tr class="bg-light">
                                <th class="pl-3">Email</th>
                                <td>{{ $colaborador->usuario->email ?? '—' }}</td>
                            </tr>
                            @sisadmin
                                <tr>
                                    <th class="pl-3">Vigencia</th>
                                    <td>
                                        @if ($colaborador->usuario->vigencia)
                                            {{ $colaborador->usuario->vigencia->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">Sin límite</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="bg-light">
                                    <th class="pl-3">Estado colaborador</th>
                                    <td>
                                        @if ($colaborador->bloqueado)
                                            <span class="badge badge-danger">Bloqueado</span>
                                        @else
                                            <span class="badge badge-success">Activo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endsisadmin
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-building mr-1"></i> Contratista
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="pl-3" style="width:35%">Nombre</th>
                                <td>{{ $colaborador->contratista->nombre }}</td>
                            </tr>
                            <tr class="bg-light">
                                <th class="pl-3">RUT</th>
                                <td>{{ $colaborador->contratista->rut ?? '—' }}</td>
                            </tr>
                            @sisadmin
                                <tr class="bg-light">
                                    <th class="pl-3">Estado</th>
                                    <td>
                                        @if ($colaborador->contratista->bloqueado)
                                            <span class="badge badge-danger">Bloqueado</span>
                                        @else
                                            <span class="badge badge-success">Activo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endsisadmin
                            <tr>
                                <th class="pl-3">Es colaborador desde</th>
                                <td>
                                    @if ($colaborador->fecha)
                                        {{ $colaborador->fecha->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-clipboard-list mr-1"></i> Evaluaciones asignadas
        <span class="badge badge-light ml-1">{{ $evaluaciones->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if ($evaluaciones->isEmpty())
            <p class="text-muted text-center my-3">Sin evaluaciones asignadas.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            @sisadmin
                                <th>#</th>
                            @endsisadmin
                            <th>Nombre</th>
                            <th>Descripción</th>
                            @sisadmin
                                <th>Estado</th>
                            @endsisadmin
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($evaluaciones as $eva)
                        <tr>
                            @sisadmin
                                <td>{{ $eva->id }}</td>
                            @endsisadmin
                            <td>
                                {{ $eva->nombre }}
                                <a href="{{ route('evaluacion.evaluacion.show', $eva) }}" class="btn btn-xs btn-warning" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                            <td class="text-muted">{{ $eva->descripcion ?? '—' }}</td>
                            @sisadmin
                                <td>
                                    @if ($eva->bloqueado)
                                        <span class="badge badge-danger">Bloqueada</span>
                                    @else
                                        <span class="badge badge-success">Activa</span>
                                    @endif
                                </td>
                            @endsisadmin
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($evaluaciones->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2 border-top gap-2">
                <small class="text-muted">
                    Página {{ $evaluaciones->currentPage() }} de {{ $evaluaciones->lastPage() }}
                </small>
                {{ $evaluaciones->links() }}
            </div>
            @endif
        @endif
    </div>
</div>

@endsection