<table class="table table-bordered table-hover table-sm">
    <thead class="thead-light">
        <tr>
            @sisadmin
                <th>#</th>
            @endsisadmin
            <th>Nombre</th>
            <th>RUT</th>
            <th>Evaluaciones</th>
            <th>Evaluadores</th>
            <th>Contratistas</th>
            @if ($mostrarEstado)
                <th>Estado</th>
            @endif
            @sisadmin
            <th></th>
            @endsisadmin
        </tr>
    </thead>
    <tbody>
        @forelse ($filas as $cliente)
        <tr>
            @sisadmin
                <td>{{ $cliente->id }}</td>
            @endsisadmin
            <td>
                <strong>{{ $cliente->nombre }}</strong>
                <a href="{{ route($rutaBase . 'show', $cliente) }}" class="btn btn-xs btn-warning" title="Ver">
                    <i class="fas fa-eye"></i>
                </a>
            </td>
            <td>{{ $cliente->rut }}</td>
            <td>
                <span class="badge badge-primary">
                    {{ $cliente->evaluaciones_count }}
                    {{ $cliente->evaluaciones_count == 1 ? 'evaluación' : 'evaluaciones' }}
                </span>
            </td>
            <td>
                <span class="badge badge-primary">
                    {{ $cliente->evaluadores_count }}
                    {{ $cliente->evaluadores_count == 1 ? 'evaluador' : 'evaluadores' }}
                </span>
            </td>
            <td>
                <span class="badge badge-primary">
                    {{ $cliente->contratistas_count }}
                    {{ $cliente->contratistas_count == 1 ? 'contratista' : 'contratistas' }}
                </span>
            </td>
            @if ($mostrarEstado)
                <td>
                    @if ($cliente->bloqueado)
                        <span class="badge badge-danger">Bloqueado</span>
                    @else
                        <span class="badge badge-success">Activo</span>
                    @endif
                </td>
            @endif
            @sisadmin
            <td class="text-center">
                <a href="{{ route($rutaBase . 'edit', $cliente) }}" class="btn btn-xs btn-info" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <form method="POST" action="{{ route($rutaBase . 'destroy', $cliente) }}" class="d-inline"
                    onsubmit="return confirm('¿{{ $cliente->bloqueado ? 'Desbloquear' : 'Bloquear' }} este cliente?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-xs {{ $cliente->bloqueado ? 'btn-success' : 'btn-danger' }}" 
                            title="{{ $cliente->bloqueado ? 'Desbloquear' : 'Bloquear' }}">
                        <i class="fas {{ $cliente->bloqueado ? 'fa-check' : 'fa-ban' }}"></i>
                    </button>
                </form>
            </td>
            @endsisadmin
        </tr>
        @empty
        <tr>
            <td colspan="{{ $mostrarEstado ? 8 : 7 }}" class="text-center text-muted">
                No hay clientes registrados.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>