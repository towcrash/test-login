<div class="rol-section">
    <div class="rol-banner rol-colaborador">
        <i class="fas fa-user-tie"></i> Vista de Colaborador
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="stat-box stat-pink">
                <div class="stat-num">{{ $colaborador['pendientes_no_permanentes']->total() }}</div>
                <div class="stat-lbl">Evaluaciones no permanentes pendientes</div>
                <i class="fas fa-exclamation-circle stat-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box stat-grape">
                <div class="stat-num">{{ $colaborador['pendientes_permanentes']->total() }}</div>
                <div class="stat-lbl">Evaluaciones permanentes pendientes</div>
                <i class="fas fa-infinity stat-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box stat-green">
                <div class="stat-num">{{ $colaborador['realizadas']->total() }}</div>
                <div class="stat-lbl">Evaluaciones completadas</div>
                <i class="fas fa-check-double stat-icon"></i>
            </div>
        </div>
    </div>

    {{-- Pendientes NO permanentes --}}
    <div class="card dash-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-exclamation-circle text-danger mr-2"></i>Evaluaciones no permanentes pendientes</span>
            <span class="badge badge-danger badge-pill">{{ $colaborador['pendientes_no_permanentes']->total() }}</span>
        </div>
        <div class="card-body p-0">
            @if($colaborador['pendientes_no_permanentes']->total() > 0)
                <div class="table-responsive-dash">
                    <table class="table dash-table table-sm mb-0 table-borderless">
                        <thead>
                            <tr>
                                <th>Evaluación</th>
                                <th>Descripción</th>
                                <th>Asignada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($colaborador['pendientes_no_permanentes'] as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['evaluacion']->nombre ?? '—' }}</strong>
                                    <a href="{{ route('evaluacion.evaluacion.show', $item['evaluacion']->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                                </td>
                                <td class="text-muted">{{ Str::limit($item['evaluacion']->descripcion ?? '', 50) }}</td>
                                <td class="text-muted">
                                    {{ $item['fecha'] ? \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2">
                    {{ $colaborador['pendientes_no_permanentes']->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="empty-msg">
                    <i class="fas fa-check-circle text-success"></i>Sin evaluaciones no permanentes pendientes
                </div>
            @endif
        </div>
    </div>

    {{-- Pendientes permanentes --}}
    @if($colaborador['pendientes_permanentes']->total() > 0)
    <div class="card dash-card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-infinity text-info mr-2"></i>Evaluaciones permanentes pendientes</span>
            <span class="badge badge-info badge-pill">{{ $colaborador['pendientes_permanentes']->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive-dash">
                <table class="table dash-table table-sm mb-0 table-borderless">
                    <thead>
                        <tr>
                            <th>Evaluación</th>
                            <th>Descripción</th>
                            <th>Asignada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($colaborador['pendientes_permanentes'] as $item)
                        <tr>
                            <td>
                                {{ $item['evaluacion']->nombre ?? '—' }}
                                <a href="{{ route('evaluacion.evaluacion.show', $item['evaluacion']->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                            <td class="text-muted">{{ Str::limit($item['evaluacion']->descripcion ?? '', 50) }}</td>
                            <td class="text-muted">
                                {{ $item['fecha'] ? \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">
                {{ $colaborador['pendientes_permanentes']->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
    @endif

    {{-- Realizadas --}}
    <div class="card dash-card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-check-double text-success mr-2"></i>Evaluaciones completadas</span>
            <span class="badge badge-success badge-pill">{{ $colaborador['realizadas']->total() }}</span>
        </div>
        <div class="card-body p-0">
            @if($colaborador['realizadas']->total() > 0)
                <div class="table-responsive-dash">
                    <table class="table dash-table table-sm mb-0 table-borderless">
                        <thead>
                            <tr>
                                <th>Evaluación</th>
                                <th>Descripción</th>
                                <th>Tipo</th>
                                <th>Completada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($colaborador['realizadas'] as $app)
                            <tr>
                                <td>{{ $app->evaluacion->nombre ?? '—' }}
                                    <a href="{{ route('evaluacion.evaluacion.show', $app->evaluacion) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                                <td class="text-muted">{{ Str::limit($app->evaluacion->descripcion ?? '', 50) }}</td>
                                <td>
                                    @if($app->evaluacion?->permanent)
                                        <span class="badge badge-permanente">Permanente</span>
                                    @else
                                        <span class="badge badge-secondary">No permanente</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-realizada">{{ $app->submitdate?->format('d/m/Y') }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2">
                    {{ $colaborador['realizadas']->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="empty-msg">
                    <i class="fas fa-inbox"></i>Aún no has completado ninguna evaluación
                </div>
            @endif
        </div>
    </div>
</div>