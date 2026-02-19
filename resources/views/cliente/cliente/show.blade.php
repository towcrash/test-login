@extends('layouts.app')

@section('tituloPagina', 'Cliente')
@section('cabecera', $cliente->nombre)

@section('accionGlobal')
    @sisadmin
    <a href="{{ route('cliente.cliente.edit', $cliente) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit mr-1"></i> Editar
    </a>
    @endsisadmin
    <a href="{{ route('cliente.cliente.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
@endsection

@push('estilos')
    <style>
    .select2-below .select2-selection--multiple .select2-selection__rendered {
        display: none !important;
    }
    .select2-below .select2-selection--multiple {
        min-height: 38px !important;
        border-radius: 4px !important;
    }
    .select2-below .select2-selection--multiple .select2-search--inline {
        width: 100% !important;
    }
    .select2-below .select2-selection--multiple .select2-search--inline .select2-search__field {
        width: 100% !important;
        margin-top: 5px;
    }
    .select2-tags-below {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 6px;
        min-height: 0;
    }
    .select2-tags-below .select2-tag-item {
        display: inline-flex;
        align-items: center;
        background-color: #007bff;
        color: #fff;
        border-radius: 3px;
        padding: 2px 8px;
        font-size: 0.82em;
        gap: 5px;
    }
    .select2-tags-below .select2-tag-item .remove-tag {
        cursor: pointer;
        font-weight: bold;
        opacity: 0.8;
        background: none;
        border: none;
        color: #fff;
        padding: 0;
        line-height: 1;
    }
    .select2-tags-below .select2-tag-item .remove-tag:hover { opacity: 1; }
</style>
@endpush

@section('contenido')
<div class="row">

    {{-- ── Columna izquierda: datos + formularios ── --}}
    <div class="col-md-4">

        <x-card titulo="Datos del Cliente">
            <table class="table table-sm table-borderless mb-0">
                <tr><th>Nombre</th><td>{{ $cliente->nombre }}</td></tr>
                <tr><th>RUT</th><td>{{ $cliente->rut }}</td></tr>
                @sisadmin
                <tr>
                    <th>Estado</th>
                    <td>
                        @if ($cliente->bloqueado)
                            <span class="badge badge-danger">Bloqueado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                </tr>
                @endsisadmin
            </table>
        </x-card>

        @sisadmin

        {{-- Asignar Usuarios --}}
        <x-card titulo="Asignar Usuarios">
            <form method="POST" action="{{ route('cliente.cliente.asignarUsuario', $cliente) }}">
                @csrf
                <p class="text-muted small mb-2">
                    <i class="fas fa-info-circle"></i>
                    Si no tienen el rol <strong>Cliente</strong>, se les asignará automáticamente.
                </p>
                <div class="form-group mb-1">
                    <select name="Usuario_id[]" id="selectUsuariosMultiples"
                            class="select2 form-control" multiple style="width:100%">
                        @foreach ($usuariosDisponibles as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-success btn-sm btn-block" type="submit">
                    <i class="fas fa-plus mr-1"></i> Asignar Usuarios
                </button>
            </form>
        </x-card>
        @endsisadmin
            {{-- Usuarios asociados --}}
            <x-card titulo="Usuarios ({{ $cliente->usuarios->count() }})">
                @forelse ($cliente->usuarios as $usuario)
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div>
                        <strong>{{ $usuario->nombre }}</strong><br>
                        <small class="text-muted">{{ $usuario->user }}</small><br>
                        @foreach ($usuario->roles as $rol)
                            <span class="badge badge-primary" style="font-size:.75em">{{ $rol->nombre }}</span>
                        @endforeach
                    </div>
                    @sisadmin
                    <form method="POST"
                        action="{{ route('cliente.cliente.desasignarUsuario', [$cliente, $usuario]) }}"
                        onsubmit="return confirm('¿Quitar a {{ $usuario->nombre }} de este cliente?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-danger" title="Quitar"><i class="fas fa-unlink"></i></button>
                    </form>
                    @endsisadmin
                </div>
                @empty
                <p class="text-muted mb-0">Sin usuarios asociados.</p>
                @endforelse
            </x-card>
        @sisadmin
        {{-- Asignar Contratistas --}}
        <x-card titulo="Asignar Contratistas">
            <form method="POST" action="{{ route('cliente.cliente.asignarContratista', $cliente) }}">
                @csrf
                <div class="form-group mb-1">
                    <select name="Contratista_id[]" id="selectContratistasMultiples"
                            class="select2 form-control" multiple style="width:100%">
                        @foreach ($contratistasDisponibles as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-success btn-sm btn-block" type="submit">
                    <i class="fas fa-plus mr-1"></i> Asignar Contratistas
                </button>
            </form>
        </x-card>
        @endsisadmin

        {{-- Contratistas asociados --}}
        <x-card titulo="Contratistas ({{ $cliente->contratistas->count() }})">
            @forelse ($cliente->contratistas as $contratista)
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div>
                    <strong>{{ $contratista->nombre }}</strong><br>
                    <small class="text-muted">{{ $contratista->rut }}</small><br>
                    <small>Colaboradores: <span class="badge badge-secondary">{{ $contratista->colaboradores->count() }}</span></small>
                </div>
                <form method="POST"
                      action="{{ route('cliente.cliente.desasignarContratista', [$cliente, $contratista]) }}"
                      onsubmit="return confirm('¿Desasociar {{ $contratista->nombre }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger" title="Quitar"><i class="fas fa-unlink"></i></button>
                </form>
            </div>
            @empty
            <p class="text-muted mb-0">Sin contratistas asociados.</p>
            @endforelse
        </x-card>
    </div>

    {{-- ── Columna central: Usuarios + Evaluaciones ── --}}
    <div class="col-md-4">
        
        {{-- Asignar Evaluaciones --}}
        @sisadmin
            <x-card titulo="Asignar Evaluaciones">
                <form method="POST" action="{{ route('cliente.cliente.asignarEvaluacion', $cliente) }}">
                    @csrf
                    <div class="form-group mb-1">
                        <select name="Evaluacion_id[]" id="selectEvaluacionesMultiples"
                                class="select2 form-control" multiple style="width:100%">
                            @foreach ($evaluacionesDisponibles as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-success btn-sm btn-block" type="submit">
                        <i class="fas fa-plus mr-1"></i> Asignar Evaluaciones
                    </button>
                </form>
            </x-card>
        @endsisadmin
        {{-- Evaluaciones del cliente --}}
        <x-card titulo="Evaluaciones ({{ $cliente->evaluaciones->count() }})">
            @forelse ($cliente->evaluaciones as $evaluacion)
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div>
                    <strong>{{ $evaluacion->nombre }}</strong><br>
                    <small class="text-muted">
                        {{ $evaluacion->fecha_inicio ?? '—' }}
                        @if ($evaluacion->fecha_fin) → {{ $evaluacion->fecha_fin }} @endif
                    </small><br>
                    @if ($evaluacion->bloqueado)
                        <span class="badge badge-danger">Bloqueada</span>
                    @else
                        <span class="badge badge-success">Activa</span>
                    @endif
                </div>
                @sisadmin
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('evaluacion.evaluacion.edit', $evaluacion) }}"
                       class="btn btn-xs btn-warning" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST"
                          action="{{ route('cliente.cliente.desasignarEvaluacion', [$cliente, $evaluacion]) }}"
                          onsubmit="return confirm('¿Desasociar {{ $evaluacion->nombre }} de este cliente?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-danger" title="Quitar"><i class="fas fa-unlink"></i></button>
                    </form>
                </div>
                @endsisadmin
            </div>
            @empty
            <p class="text-muted mb-0">Sin evaluaciones registradas.</p>
            @endforelse
        </x-card>

    </div>

    {{-- ── Columna derecha: Evaluadores + relación Evaluador-Evaluación ── --}}
    <div class="col-md-4">
        {{-- Asignar Evaluadores --}}
        @sisadmin
            <x-card titulo="Asignar Evaluadores">
                <form method="POST" action="{{ route('cliente.cliente.asignarEvaluador', $cliente) }}">
                    @csrf
                    <div class="form-group mb-1">
                        <select name="Usuario_id[]" id="selectEvaluadoresMultiples"
                                class="select2 form-control" multiple style="width:100%">
                            @foreach ($usuariosParaEvaluador as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-success btn-sm btn-block" type="submit">
                        <i class="fas fa-plus mr-1"></i> Asignar Evaluadores
                    </button>
                </form>
            </x-card>
        @endsisadmin
        {{-- Evaluadores --}}
        <x-card titulo="Evaluadores ({{ $cliente->evaluadores->count() }})">
            @forelse ($cliente->evaluadores as $evaluador)
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                <div>
                    <strong>{{ $evaluador->usuario->nombre }}</strong><br>
                    <small class="text-muted">{{ $evaluador->usuario->user }}</small>
                </div>
                @sisadmin
                <form method="POST"
                      action="{{ route('cliente.cliente.desasignarEvaluador', [$cliente, $evaluador]) }}"
                      onsubmit="return confirm('¿Quitar evaluador?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger" title="Quitar"><i class="fas fa-unlink"></i></button>
                </form>
                @endsisadmin
            </div>
            @empty
            <p class="text-muted mb-0">Sin evaluadores asignados.</p>
            @endforelse
        </x-card>

        {{-- Relación Evaluador ↔ Evaluación --}}
        <x-card titulo="Evaluador por Evaluación">
            @forelse ($cliente->evaluaciones as $evaluacion)
            <div class="mb-3 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong>{{ $evaluacion->nombre }}</strong>
                    @sisadmin
                    <span class="badge badge-light text-muted">
                        {{ $evaluacion->evaluadores->count() }} evaluador(es)
                    </span>
                    @endsisadmin
                </div>

                {{-- Evaluadores asignados a esta evaluación --}}
                @forelse ($evaluacion->evaluadores as $ev)
                <div class="d-flex justify-content-between align-items-center py-1 pl-2">
                    <small>
                        <i class="fas fa-user-check text-success mr-1"></i>
                        {{ $ev->usuario->nombre }}
                    </small>
                    @sisadmin
                    <form method="POST"
                          action="{{ route('cliente.cliente.desasignarEvaluadorEvaluacion', [$cliente, $evaluacion, $ev]) }}"
                          onsubmit="return confirm('¿Quitar a {{ $ev->usuario->nombre }} de esta evaluación?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-outline-danger" title="Quitar"><i class="fas fa-times"></i></button>
                    </form>
                    @endsisadmin
                </div>
                @empty
                <small class="text-muted pl-2">Sin evaluador asignado.</small>
                @endforelse

                {{-- Asignar evaluador a esta evaluación (solo sisadmin) --}}
                @sisadmin
                @php
                    $evAsignadosIds = $evaluacion->evaluadores->pluck('id');
                    $evDisponibles  = $cliente->evaluadores->whereNotIn('id', $evAsignadosIds);
                @endphp
                @if ($evDisponibles->count())
                <form method="POST"
                      action="{{ route('cliente.cliente.asignarEvaluadorEvaluacion', [$cliente, $evaluacion]) }}"
                      class="d-flex align-items-center mt-1 pl-2">
                    @csrf
                    <select name="Evaluador_id" class="form-control form-control-sm mr-1" required>
                        <option value="" disabled selected>— Añadir —</option>
                        @foreach ($evDisponibles as $ev)
                            <option value="{{ $ev->id }}">{{ $ev->usuario->nombre }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-xs btn-success" type="submit">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
                @endif
                @endsisadmin
            </div>
            @empty
            <p class="text-muted mb-0">Sin evaluaciones para mostrar.</p>
            @endforelse
        </x-card>

    </div>

</div>
@endsection

@push('acciones')
<script>
$(document).ready(function () {

    function initSelectBelow(selector) {
        var $select = $(selector);
        var $container = $('<div class="select2-tags-below"></div>').insertAfter($select.next('.select2'));

        $select.select2({
            theme: 'bootstrap4',
            placeholder: '— Buscar y seleccionar —',
            allowClear: false,
            width: '100%',
            containerCssClass: 'select2-below',
        });

        function renderTags() {
            $container.empty();
            $select.select2('data').forEach(function(item) {
                var $tag = $('<span class="select2-tag-item">' + item.text +
                    '<button type="button" class="remove-tag" data-id="' + item.id + '">&times;</button></span>');
                $container.append($tag);
            });
        }

        $select.on('select2:select select2:unselect', renderTags);

        $container.on('click', '.remove-tag', function() {
            var id = $(this).data('id');
            $select.val($select.val().filter(function(v) { return v != id; })).trigger('change');
            renderTags();
        });

        renderTags();
    }

    initSelectBelow('#selectUsuariosMultiples');
    initSelectBelow('#selectContratistasMultiples');
    initSelectBelow('#selectEvaluadoresMultiples');
    initSelectBelow('#selectEvaluacionesMultiples');
});
</script>
@endpush