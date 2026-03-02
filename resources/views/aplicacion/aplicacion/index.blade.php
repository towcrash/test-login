@extends('layouts.app')

@section('tituloPagina', 'Aplicaciones Pendientes')
@section('cabecera', 'Aplicaciones Pendientes de Evaluación')

@section('contenido')

{{-- Saludo --}}
<p class="text-muted mb-4">
    <i class="far fa-clock mr-1"></i>
    {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
    &nbsp;·&nbsp;
    <strong>{{ auth()->user()->nombre }}</strong>
</p>

{{-- Indicador de roles activos --}}
@php
    $tieneEvaluador = isset($evaluador);
    $tieneColaborador = isset($colaborador);
    $esMultiple = $tieneEvaluador && $tieneColaborador;
@endphp

{{-- SECCIÓN PARA EVALUADOR --}}
@if($tieneEvaluador)
    {{-- Banner según rol --}}
    <div class="rol-banner rol-evaluador">
        <i class="fas fa-user-check"></i>
        Vista de Evaluador
    </div>

    {{-- Tarjeta de aplicaciones pendientes para evaluador --}}
    <div class="card dash-card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                Aplicaciones pendientes de asignación
                <small class="text-muted ml-2">(Evaluaciones a tu cargo sin evaluador asignado)</small>
            </span>
            <span class="badge badge-warning badge-pill">{{ $evaluador['aplicaciones']->total() }}</span>
        </div>

        <div class="card-body p-0">
            @if($evaluador['aplicaciones']->total() > 0)
                <div class="table-responsive-dash">
                    <table class="table dash-table table-sm mb-0 table-borderless">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>RUT</th>
                                <th>Contratista</th>
                                <th>Evaluación</th>
                                <th>Fecha asignación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluador['aplicaciones'] as $app)
                            <tr>
                                <td>
                                    <strong>{{ $app->colaborador->usuario->nombre ?? '—' }}</strong>
                                </td>
                                <td class="text-muted">{{ $app->colaborador->usuario->rut ?? '—' }}</td>
                                <td>
                                    {{ $app->colaborador->contratista->nombre ?? '—' }}
                                    @if($app->colaborador->contratista)
                                        <small class="info-contratista">
                                            RUT: {{ $app->colaborador->contratista->rut ?? '—' }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $app->evaluacion->nombre ?? '—' }}</td>
                                <td class="text-muted">
                                    {{ $app->fecha ? $app->fecha->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    <a href="{{ route('evaluacion.evaluacion.show', $app->evaluacion) }}" 
                                       class="btn btn-xs btn-warning" 
                                       title="Ver evaluación">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                     <form method="POST"
                                          action="{{ route('aplicacion.aplicacion.asignarme', $app) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Confirmas asignarte como evaluador? El colaborador podrá acceder a la encuesta.')">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success" title="Asignarme como evaluador">
                                            <i class="fas fa-user-plus mr-1"></i> Asignarme
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="px-3 py-3 d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted small mb-2 mb-sm-0">
                        Mostrando {{ $evaluador['aplicaciones']->firstItem() ?? 0 }} - {{ $evaluador['aplicaciones']->lastItem() ?? 0 }} 
                        de {{ $evaluador['aplicaciones']->total() }} aplicaciones
                    </div>
                    <div>
                        {{ $evaluador['aplicaciones']->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="empty-msg">
                    <i class="fas fa-check-circle text-success"></i>
                    No hay aplicaciones pendientes de asignación
                </div>
            @endif
        </div>
    </div>

    @if($tieneColaborador)
        <hr class="my-4" style="border-top: 2px dashed #dee2e6;">
    @endif
@endif

{{-- SECCIÓN PARA COLABORADOR --}}
@if($tieneColaborador)
    {{-- Banner según rol --}}
    <div class="rol-banner rol-colaborador">
        <i class="fas fa-user-tie"></i>
        Vista de Colaborador
    </div>

    {{-- Tarjeta de aplicaciones pendientes para colaborador --}}
    <div class="card dash-card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                Mis evaluaciones pendientes
            </span>
            <span class="badge badge-warning badge-pill">{{ $colaborador['aplicaciones']->total() }}</span>
        </div>

        <div class="card-body p-0">
            @if($colaborador['aplicaciones']->total() > 0)
                <div class="table-responsive-dash">
                    <table class="table dash-table table-sm mb-0 table-borderless">
                        <thead>
                            <tr>
                                <th>Evaluación</th>
                                <th>Descripción</th>
                                <th>Tipo</th>
                                <th>Fecha asignación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($colaborador['aplicaciones'] as $app)
                            <tr>
                                <td>
                                    <strong>{{ $app->evaluacion->nombre ?? '—' }}</strong>
                                </td>
                                <td class="text-muted">
                                    {{ Str::limit($app->evaluacion->descripcion ?? '', 60) }}
                                </td>
                                <td>
                                    @if($app->evaluacion?->permanent)
                                        <span class="badge badge-info">Permanente</span>
                                    @else
                                        <span class="badge badge-secondary">No permanente</span>
                                    @endif
                                </td>
                                <td class="text-muted">
                                    {{ $app->fecha ? $app->fecha->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('evaluacion.evaluacion.show', $app->evaluacion) }}"
                                       class="btn btn-xs btn-warning"
                                       title="Ver evaluación">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($app->Evaluador_id && $app->token && $app->evaluacion?->sid)
                                        <a href="https://survey.engineeringpr.cl/index.php/{{ $app->evaluacion->sid }}?token={{ $app->token }}"
                                           target="_blank"
                                           class="btn btn-xs btn-primary"
                                           title="Ir a la encuesta">
                                            <i class="fas fa-external-link-alt mr-1"></i> Ir a encuesta
                                        </a>
                                    @else
                                        <span class="btn btn-xs btn-secondary disabled"
                                              title="En espera de que un evaluador se asigne">
                                            <i class="fas fa-clock mr-1"></i> Sin asignar
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="px-3 py-3 d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted small mb-2 mb-sm-0">
                        Mostrando {{ $colaborador['aplicaciones']->firstItem() ?? 0 }} - {{ $colaborador['aplicaciones']->lastItem() ?? 0 }} 
                        de {{ $colaborador['aplicaciones']->total() }} aplicaciones
                    </div>
                    <div>
                        {{ $colaborador['aplicaciones']->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="empty-msg">
                    <i class="fas fa-check-circle text-success"></i>
                    No tienes evaluaciones pendientes por responder
                </div>
            @endif
        </div>
    </div>
@endif

{{-- Mensaje si no tiene ninguno de los roles --}}
@if(!$tieneEvaluador && !$tieneColaborador)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        No tienes permisos de Evaluador ni Colaborador para ver esta sección.
    </div>
@endif

{{-- Resumen rápido --}}
@if($tieneEvaluador || $tieneColaborador)
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card dash-card">
            <div class="card-header">
                <i class="fas fa-info-circle text-info mr-2"></i>
                Resumen de pendientes
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @if($tieneEvaluador)
                    <li class="mb-3">
                        <div class="d-flex align-items-center">
                            <div style="width: 40px; height: 40px; background: #e6f9f2; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                <i class="fas fa-user-check" style="color: #0e9f6e;"></i>
                            </div>
                            <div>
                                <strong>Como Evaluador</strong>
                                <div class="text-muted small">
                                    {{ $evaluador['total'] ?? 0 }} aplicaciones pendientes de asignación
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif
                    
                    @if($tieneColaborador)
                    <li class="mb-2">
                        <div class="d-flex align-items-center">
                            <div style="width: 40px; height: 40px; background: #fce7f3; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                <i class="fas fa-user-tie" style="color: #e4008d;"></i>
                            </div>
                            <div>
                                <strong>Como Colaborador</strong>
                                <div class="text-muted small">
                                    {{ $colaborador['total'] ?? 0 }} evaluaciones pendientes por responder
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

@endsection