@extends('layouts.app')
@section('tituloPagina', $contratista->nombre)
@section('cabecera', $contratista->nombre)

@section('accionGlobal')
    @sisadmin
    <a href="{{ route($rutaBase . 'edit', ['contratista' => $contratista]) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit mr-1"></i> Editar
    </a>
    @endsisadmin
    <a href="{{ route($rutaBase . 'index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
@endsection

@push('estilos')
<style>
.select2-below .select2-selection--multiple .select2-selection__rendered { display: none !important; }
.select2-below .select2-selection--multiple { min-height: 38px !important; border-radius: 4px !important; }
.select2-below .select2-selection--multiple .select2-search--inline { width: 100% !important; }
.select2-below .select2-selection--multiple .select2-search--inline .select2-search__field { width: 100% !important; margin-top: 5px; }
.select2-tags-below { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; min-height: 0; }
.select2-tags-below .select2-tag-item { display: inline-flex; align-items: center; background-color: #007bff; color: #fff; border-radius: 3px; padding: 2px 8px; font-size: 0.82em; gap: 5px; }
.select2-tags-below .select2-tag-item .remove-tag { cursor: pointer; font-weight: bold; opacity: 0.8; background: none; border: none; color: #fff; padding: 0; line-height: 1; }
.select2-tags-below .select2-tag-item .remove-tag:hover { opacity: 1; }
</style>
@endpush

@section('contenido')
<div class="row">

    {{-- ── Columna izquierda ── --}}
    <div class="col-12 col-md-4 mb-4">

        <x-card titulo="Datos del Contratista">
            <table class="table table-sm table-borderless mb-0">
                <tr><th>Nombre</th><td>{{ $contratista->nombre }}</td></tr>
                <tr><th>RUT</th><td>{{ $contratista->rut ?? '—' }}</td></tr>
                @if ($esSisAdmin)
                <tr>
                    <th>Estado</th>
                    <td>
                        @if ($contratista->bloqueado)
                            <span class="badge badge-danger">Bloqueado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                </tr>
                @endif
            </table>
        </x-card>

        {{-- Clientes asociados --}}
        <x-card titulo="Clientes ({{ $contratista->clientes->count() }})">
            @forelse ($contratista->clientes as $cliente)
            <div class="mb-2 p-2 border rounded">
                <strong>{{ $cliente->nombre }}</strong><br>
                <small class="text-muted">{{ $cliente->rut }}</small>
            </div>
            @empty
            <p class="text-muted mb-0">Sin clientes asociados.</p>
            @endforelse
        </x-card>

        {{-- Usuarios asociados --}}
        <x-card titulo="Usuarios ({{ $contratista->usuarios->count() }})">
            @forelse ($contratista->usuarios as $u)
            <div class="mb-2 p-2 border rounded">
                <strong>{{ $u->nombre }}</strong><br>
                <small class="text-muted">{{ $u->user ?? $u->email }}</small>
            </div>
            @empty
            <p class="text-muted mb-0">Sin usuarios asociados.</p>
            @endforelse
        </x-card>

    </div>

    {{-- ── Columna central: Colaboradores ── --}}
    <div class="col-12 col-md-4 mb-4">

        @sisadmin
        <x-card titulo="Asignar Colaboradores">
            <form method="POST" action="{{ route($rutaBase . 'asignarColaborador', ['contratista' => $contratista]) }}">
                @csrf
                <p class="text-muted small mb-2">
                    <i class="fas fa-info-circle"></i> Solo usuarios con rol <strong>Colaborador</strong>.
                </p>
                <div class="form-group mb-1">
                    <select name="Usuario_id[]" id="selectColaboradoresMultiples"
                            class="select2 form-control" multiple style="width:100%">
                        @foreach ($colaboradoresDisponibles as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-success btn-sm btn-block" type="submit">
                    <i class="fas fa-plus mr-1"></i> Asignar Colaboradores
                </button>
            </form>
        </x-card>
        @endsisadmin

        <x-card titulo="Colaboradores ({{ $contratista->colaboradores->count() }})">
            @forelse ($contratista->colaboradores as $col)
            <div class="mb-3 p-2 border rounded">

                {{-- Cabecera del colaborador --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <strong>{{ $col->usuario?->nombre ?? '—' }}</strong><br>
                        <small class="text-muted">{{ $col->usuario?->user ?? $col->usuario?->email }}</small>
                    </div>
                    @sisadmin
                    <form method="POST"
                        action="{{ route($rutaBase . 'desasignarColaborador', ['contratista' => $contratista, 'colaborador' => $col]) }}"
                        onsubmit="return confirm('¿Quitar a {{ $col->usuario?->nombre }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-danger" title="Quitar colaborador"><i class="fas fa-unlink"></i></button>
                    </form>
                    @endsisadmin
                </div>

                {{-- Evaluaciones del colaborador --}}
                @if ($col->evaluaciones->isNotEmpty())
                <div class="mt-2">
                    @foreach ($col->evaluaciones as $eva)
                    <div class="d-flex flex-wrap justify-content-between align-items-center py-1 border-top">
                        <small>
                            <i class="fas fa-clipboard-list text-primary mr-1"></i>
                            {{ $eva->nombre }}
                        </small>
                        @if ($esSisAdmin || auth()->user()?->contratistas->contains('id', $contratista->id))
                        <form method="POST"
                            action="{{ route($rutaBase . 'desasignarEvaluacionColaborador', ['contratista' => $contratista, 'colaborador' => $col, 'evaluacion' => $eva]) }}"
                            onsubmit="return confirm('¿Quitar evaluación {{ $eva->nombre }} de {{ $col->usuario?->nombre }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" title="Quitar evaluación">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Asignar evaluación al colaborador --}}
                @if ($esSisAdmin || auth()->user()?->contratistas->contains('id', $contratista->id))
                @php
                    $evaluacionesDisponiblesColaborador = $evaluacionesContratista
                        ->whereNotIn('id', $col->evaluaciones->pluck('id'));
                @endphp
                @if ($evaluacionesDisponiblesColaborador->isNotEmpty())
                <form method="POST" class="mt-2"
                    action="{{ route($rutaBase . 'asignarEvaluacionColaborador', ['contratista' => $contratista, 'colaborador' => $col]) }}">
                    @csrf
                    <select name="Evaluacion_id[]" id="selectEvaluacionCol_{{ $col->id }}"
                            class="form-control select2-evaluacion-col" multiple style="width:100%">
                        @foreach ($evaluacionesDisponiblesColaborador as $eva)
                            <option value="{{ $eva->id }}">{{ $eva->nombre }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-xs btn-outline-success btn-block mt-1" type="submit">
                        <i class="fas fa-plus mr-1"></i> Asignar evaluación
                    </button>
                </form>
                @else
                <p class="text-muted small mt-2 mb-0">
                    <i class="fas fa-check-circle text-success mr-1"></i> Tiene todas las evaluaciones asignadas.
                </p>
                @endif
                @endif

            </div>
            @empty
            <p class="text-muted mb-0">Sin colaboradores registrados.</p>
            @endforelse
        </x-card>

    </div>

    {{-- ── Columna derecha: Evaluaciones ── --}}
    <div class="col-12 col-md-4 mb-4">

        @sisadmin
        <x-card titulo="Asignar Evaluaciones">
            <form method="POST" action="{{ route($rutaBase . 'asignarEvaluacion', ['contratista' => $contratista]) }}">
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

        <x-card titulo="Evaluaciones ({{ $contratista->evaluaciones->count() }})">
            @forelse ($contratista->evaluaciones as $ev)
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 p-2 border rounded gap-2">
                <div>
                    <strong>{{ $ev->nombre ?? '—' }}</strong><br>
                    <small class="text-muted">{{ $ev->created_at?->format('d/m/Y') }}</small>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('evaluacion.evaluacion.show', $ev) }}" class="btn btn-xs btn-warning">
                        <i class="fas fa-eye"></i>
                    </a>
                    @sisadmin
                    <form method="POST"
                        action="{{ route($rutaBase . 'desasignarEvaluacion', ['contratista' => $contratista, 'evaluacion' => $ev]) }}"
                        onsubmit="return confirm('¿Desasociar {{ $ev->nombre }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-danger" title="Quitar"><i class="fas fa-unlink"></i></button>
                    </form>
                    @endsisadmin
                </div>
            </div>
            @empty
            <p class="text-muted mb-0">Sin evaluaciones registradas.</p>
            @endforelse
        </x-card>

    </div>

</div>
@endsection

@push('acciones')
<script>
$(document).ready(function () {
    function initSelectBelow($select) {
        if ($select.hasClass('select2-hidden-accessible')) return;

        $select.select2({
            theme: 'bootstrap4',
            placeholder: '— Buscar y seleccionar —',
            allowClear: false,
            width: '100%',
            containerCssClass: 'select2-below',
        });

        var $wrapper = $select.parent().find('.select2.select2-container');
        var $container = $('<div class="select2-tags-below"></div>').insertAfter($wrapper);

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

    initSelectBelow($('#selectColaboradoresMultiples'));
    initSelectBelow($('#selectEvaluacionesMultiples'));
    $('[id^="selectEvaluacionCol_"]').each(function () {
        initSelectBelow($(this));
    });
});
</script>
@endpush