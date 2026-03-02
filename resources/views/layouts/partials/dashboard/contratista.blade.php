<div class="rol-section">
    <div class="rol-banner rol-contratista">
        <i class="fas fa-hard-hat"></i> Vista de Contratista
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="stat-box stat-orange">
                <div class="stat-num">{{ $contratista['semana_actual'] }}</div>
                <div class="stat-lbl">Evaluaciones completadas esta semana</div>
                <i class="fas fa-calendar-week stat-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box stat-orange">
                <div class="stat-num">{{ $contratista['mes_actual'] }}</div>
                <div class="stat-lbl">Evaluaciones completadas este mes</div>
                <i class="fas fa-calendar-alt stat-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box stat-orange">
                <div class="stat-num">{{ $contratista['mes_pasado'] }}</div>
                <div class="stat-lbl">Evaluaciones completadas el mes pasado</div>
                <i class="fas fa-history stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="card dash-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-user-clock text-warning mr-2"></i>Colaboradores con evaluaciones no permanentes pendientes</span>
            <span class="badge badge-warning badge-pill">{{ $contratista['colaboradores_pendientes']->total() }}</span>
        </div>
        <div class="card-body p-0">
            @if($contratista['colaboradores_pendientes']->total() > 0)
                <div class="table-responsive-dash">
                    <table class="table dash-table table-sm mb-0 table-borderless">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>RUT</th>
                                <th>Evaluación pendiente</th>
                                <th>Asignada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contratista['colaboradores_pendientes'] as $fila)
                            <tr>
                                <td>{{ $fila['colaborador']->usuario->nombre ?? '—' }}</td>
                                <td class="text-muted">{{ $fila['colaborador']->usuario->rut ?? '—' }}</td>
                                <td>
                                    {{ $fila['evaluacion']->nombre }}
                                    <a href="{{ route('evaluacion.evaluacion.show', $fila['evaluacion']->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                                <td class="text-muted">{{ $fila['app']->fecha?->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2">
                    {{ $contratista['colaboradores_pendientes']->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="empty-msg">
                    <i class="fas fa-check-circle text-success"></i>Todos los colaboradores están al día
                </div>
            @endif
        </div>
    </div>
</div>