<table class="table table-bordered table-hover table-sm">
    <thead class="thead-dark">
        <tr>
            @sisadmin
                <th>#</th>
            @endsisadmin
            <th>Nombre</th>
            <th>RUT</th>
            <th>Evaluaciones</th>
            <th>Colaboradores</th>
            @if ($mostrarEstado)
                <th>Estado</th>
            @endif
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($filas as $contratista)
        <tr>
            @sisadmin
                <td>{{ $contratista->id }}</td>
            @endsisadmin
            <td><strong>{{ $contratista->nombre }}</strong></td>
            <td>{{ $contratista->rut ?? '—' }}</td>
            <td>
                <span class="badge badge-primary">
                    {{ $contratista->evaluaciones_count }}
                    {{ $contratista->evaluaciones_count == 1 ? 'evaluación' : 'evaluaciones' }}
                </span>
            </td>
            <td>
                <span class="badge badge-info">
                    {{ $contratista->colaboradores_count }}
                    {{ $contratista->colaboradores_count == 1 ? 'colaborador' : 'colaboradores' }}
                </span>
            </td>
            @if ($mostrarEstado)
                <td>
                    @if ($contratista->bloqueado)
                        <span class="badge badge-danger">Bloqueado</span>
                    @else
                        <span class="badge badge-success">Activo</span>
                    @endif
                </td>
            @endif
            <td class="text-center">
                <a href="{{ route($rutaBase .'show', $contratista) }}" class="btn btn-xs btn-info" title="Ver">
                    <i class="fas fa-eye"></i>
                </a>
                @sisadmin
                    <a href="{{ route($rutaBase .'edit', $contratista) }}" class="btn btn-xs btn-warning" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route($rutaBase . 'destroy', $contratista) }}" class="d-inline"
                        onsubmit="return confirm('¿{{ $contratista->bloqueado ? 'Desbloquear' : 'Bloquear' }} este contratista?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-xs {{ $contratista->bloqueado ? 'btn-success' : 'btn-danger' }}" 
                                title="{{ $contratista->bloqueado ? 'Desbloquear' : 'Bloquear' }}">
                            <i class="fas {{ $contratista->bloqueado ? 'fa-check' : 'fa-ban' }}"></i>
                        </button>
                    </form>
                @endsisadmin
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="{{ $mostrarEstado ? 8 : 7 }}" class="text-center text-muted">
                No hay contratistas registrados.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>