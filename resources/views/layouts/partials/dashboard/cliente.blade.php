<div class="rol-section">
    <div class="rol-banner rol-cliente">
        <i class="fas fa-building"></i> Vista de Cliente
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="stat-box stat-blue">
                <div class="stat-num">{{ $cliente['semana_actual'] }}</div>
                <div class="stat-lbl">Evaluaciones completadas esta semana</div>
                <i class="fas fa-calendar-week stat-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box stat-teal">
                <div class="stat-num">{{ $cliente['mes_actual'] }}</div>
                <div class="stat-lbl">Evaluaciones completadas este mes</div>
                <i class="fas fa-calendar-alt stat-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box stat-grape">
                <div class="stat-num">{{ $cliente['mes_pasado'] }}</div>
                <div class="stat-lbl">Evaluaciones completadas el mes pasado</div>
                <i class="fas fa-history stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="card dash-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-exclamation-circle text-warning mr-2"></i>Contratistas con evaluaciones no permanentes pendientes</span>
            <span class="badge badge-warning badge-pill">{{ $cliente['contratistas_pendientes']->total() }}</span>
        </div>
        <div class="card-body p-0">
            @if($cliente['contratistas_pendientes']->total() > 0)
                <div class="table-responsive-dash">
                    <table class="table dash-table table-sm mb-0 table-borderless">
                        <thead>
                            <tr>
                                <th>Contratista</th>
                                <th>RUT Contratista</th>
                                <th>Evaluación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente['contratistas_pendientes'] as $fila)
                            <tr>
                                <td>{{ $fila['contratista']->nombre }}</td>
                                <td class="text-muted">{{ $fila['contratista']->rut }}</td>
                                <td>{{ $fila['evaluacion']->nombre }}
                                    <a href="{{ route('evaluacion.evaluacion.show', $fila['evaluacion']->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2">
                    {{ $cliente['contratistas_pendientes']->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="empty-msg">
                    <i class="fas fa-check-circle text-success"></i>
                    Todos los contratistas están al día
                </div>
            @endif
        </div>
    </div>
</div>