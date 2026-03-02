<div class="rol-section">
    <div class="rol-banner rol-evaluador">
        <i class="fas fa-user-check"></i> Vista de Evaluador
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="stat-box stat-green">
                <div class="stat-num">{{ $evaluador['apps_este_mes']->total() }}</div>
                <div class="stat-lbl">Aplicaciones realizadas este mes</div>
                <i class="fas fa-clipboard-check stat-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box stat-teal">
                <div class="stat-num">{{ $evaluador['evaluaciones']->total() }}</div>
                <div class="stat-lbl">Evaluaciones asignadas</div>
                <i class="fas fa-tasks stat-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box stat-pink">
                <div class="stat-num">{{ $evaluador['apps_sin_evaluador']->total() }}</div>
                <div class="stat-lbl">Aplicaciones sin evaluador asignado</div>
                <i class="fas fa-user-slash stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Evaluaciones asignadas --}}
        <div class="col-md-5">
            <div class="card dash-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list-alt text-success mr-2"></i>Mis evaluaciones asignadas</span>
                    <span class="badge badge-success badge-pill">{{ $evaluador['evaluaciones']->total() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($evaluador['evaluaciones']->total() > 0)
                        <div class="table-responsive-dash">
                            <table class="table dash-table table-sm mb-0 table-borderless">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evaluador['evaluaciones'] as $ev)
                                    <tr>
                                        <td>
                                            {{ $ev->nombre }}
                                            <a href="{{ route('evaluacion.evaluacion.show', $ev) }}" class="btn btn-xs btn-warning">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                        <td class="text-muted">{{ Str::limit($ev->descripcion, 45) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2">
                            {{ $evaluador['evaluaciones']->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="empty-msg">
                            <i class="fas fa-inbox"></i>Sin evaluaciones asignadas
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Aplicaciones este mes --}}
        <div class="col-md-7">
            <div class="card dash-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-calendar-check text-success mr-2"></i>Aplicaciones realizadas — {{ now()->isoFormat('MMMM YYYY') }}</span>
                    <span class="badge badge-success badge-pill">{{ $evaluador['apps_este_mes']->total() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($evaluador['apps_este_mes']->total() > 0)
                        <div class="table-responsive-dash">
                            <table class="table dash-table table-sm mb-0 table-borderless">
                                <thead>
                                    <tr>
                                        <th>Colaborador</th>
                                        <th>Evaluación</th>
                                        <th>Completada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evaluador['apps_este_mes'] as $app)
                                    <tr>
                                        <td>{{ $app->colaborador->usuario->nombre ?? '—' }}</td>
                                        <td>{{ $app->evaluacion->nombre ?? '—' }}
                                            <a href="{{ route('evaluacion.evaluacion.show', $app->evaluacion) }}" class="btn btn-xs btn-warning">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                        <td><span class="badge badge-realizada">{{ $app->submitdate?->format('d/m/Y') }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2">
                            {{ $evaluador['apps_este_mes']->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="empty-msg">
                            <i class="fas fa-calendar-times"></i>Sin aplicaciones este mes
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Aplicaciones sin evaluador --}}
    <div class="card dash-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Aplicaciones faltantes sin evaluador asignado</span>
            <span class="badge badge-warning badge-pill">{{ $evaluador['apps_sin_evaluador']->total() }}</span>
        </div>
        <div class="card-body p-0">
            @if($evaluador['apps_sin_evaluador']->total() > 0)
                <div class="table-responsive-dash">
                    <table class="table dash-table table-sm mb-0 table-borderless">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Evaluación</th>
                                <th>Fecha asignación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluador['apps_sin_evaluador'] as $app)
                            <tr>
                                <td>{{ $app->colaborador->usuario->nombre ?? '—' }}</td>
                                <td>
                                    {{ $app->evaluacion->nombre ?? '—' }}
                                    <a href="{{ route('evaluacion.evaluacion.show', $app->evaluacion) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                                <td class="text-muted">{{ $app->fecha?->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2">
                    {{ $evaluador['apps_sin_evaluador']->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="empty-msg">
                    <i class="fas fa-check-circle text-success"></i>Sin aplicaciones pendientes de asignación
                </div>
            @endif
        </div>
    </div>
</div>