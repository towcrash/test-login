@extends('layouts.app')
@section('tituloPagina', 'Evaluación')
@section('cabecera', 'Detalle de Evaluación')
@section('accionGlobal')
    <a href="{{ route($rutaBase . 'index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
    @sisadmin
        <a href="{{ route($rutaBase . 'edit', $evaluacion) }}" class="btn btn-warning btn-sm ml-1">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
    @endsisadmin
@endsection
@section('contenido')

<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-clipboard-list mr-1"></i> Datos de la Evaluación
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tbody>
                <tr>
                    <th class="pl-3" style="width:35%">Nombre</th>
                    <td>{{ $evaluacion->nombre }}</td>
                </tr>
                @sisadmin
                <tr class="bg-light">
                    <th class="pl-3">SID</th>
                    <td><code>{{ $evaluacion->sid ?? '—' }}</code></td>
                </tr>
                @endsisadmin
                <tr class="{{ auth()->user()->isSisAdmin() ? '' : 'bg-light' }}">
                    <th class="pl-3">Descripción</th>
                    <td>{{ $evaluacion->descripcion ?? '—' }}</td>
                </tr>
                <tr class="{{ auth()->user()->isSisAdmin() ? 'bg-light' : '' }}">
                    <th class="pl-3">Por Evaluador</th>
                    <td>
                        @if ($evaluacion->byEvaluador)
                            <span class="badge badge-info">Sí</span>
                        @else
                            <span class="badge badge-secondary">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="pl-3">Permanente</th>
                    <td>
                        @if ($evaluacion->permanent)
                            <span class="badge badge-info">Sí</span>
                        @else
                            <span class="badge badge-secondary">No</span>
                        @endif
                    </td>
                </tr>
                @sisadmin
                <tr class="bg-light">
                    <th class="pl-3">Estado</th>
                    <td>
                        @if ($evaluacion->bloqueado)
                            <span class="badge badge-danger">Bloqueada</span>
                        @else
                            <span class="badge badge-success">Activa</span>
                        @endif
                    </td>
                </tr>
                @endsisadmin
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-paperclip mr-1"></i> Recursos Disponibles
            <span class="badge badge-light ml-1">{{ $evaluacion->recursos->where('bloqueado', 0)->count() }}</span>
        </span>
        @sisadmin
        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalNuevoRecurso">
            <i class="fas fa-upload mr-1"></i> Subir Recurso
        </button>
        @endsisadmin
    </div>

    <div class="card-body p-0">
        @if ($recursosVisibles->isEmpty())
            <p class="text-muted text-center my-3">
                <i class="fas fa-folder-open mr-1"></i> Esta evaluación no tiene recursos adjuntos.
            </p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            @sisadmin
                            <th style="width:90px">Estado</th>
                            @endsisadmin
                            <th style="width:{{ auth()->user()->isSisAdmin() ? '150px' : '80px' }}" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recursosVisibles as $recurso)
                        <tr class="{{ $recurso->bloqueado ? 'text-muted' : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <i class="fas mr-1 fa-file text-primary"></i>
                                {{ $recurso->nombre }}
                            </td>
                            <td>
                                @if ($recurso->tipoRecurso)
                                    @php $color = $recurso->tipoRecurso->color ?? '6c757d'; @endphp
                                    <span class="badge"
                                          style="background-color:#{{ ltrim($color,'#') }};color:#fff">
                                        {{ $recurso->tipoRecurso->nombre }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $recurso->descripcion ?? '—' }}</td>
                            @sisadmin
                            <td>
                                @if ($recurso->bloqueado)
                                    <span class="badge badge-danger">Bloqueado</span>
                                @else
                                    <span class="badge badge-success">Activo</span>
                                @endif
                            </td>
                            @endsisadmin
                            <td class="text-center text-nowrap">
                                @if (!$recurso->bloqueado)
                                    <a href="{{ route('recurso.recurso.download', $recurso) }}"
                                       class="btn btn-primary btn-xs"
                                       title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                                @sisadmin
                                <button type="button"
                                        class="btn btn-warning btn-xs btnEditarRecurso"
                                        title="Editar nombre y descripción"
                                        data-action="{{ route('recurso.recurso.update', $recurso) }}"
                                        data-nombre="{{ $recurso->nombre }}"
                                        data-descripcion="{{ $recurso->descripcion }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST"
                                      action="{{ route('recurso.recurso.destroy', $recurso) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-xs {{ $recurso->bloqueado ? 'btn-success' : 'btn-danger' }}"
                                            title="{{ $recurso->bloqueado ? 'Desbloquear' : 'Bloquear' }}"
                                            onclick="return confirm('¿Confirma {{ $recurso->bloqueado ? 'desbloquear' : 'bloquear' }} este recurso?')">
                                        <i class="fas {{ $recurso->bloqueado ? 'fa-unlock' : 'fa-ban' }}"></i>
                                    </button>
                                </form>
                                @endsisadmin
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if ($esColaboradorDeEsta && $tokenEncuesta && $evaluacion->sid)
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-poll mr-1"></i> Acceder a la Evaluación
    </div>
    <div class="card-body text-center py-4">
        <p class="mb-3 text-muted">
            Tienes asignada esta evaluación. Haz click en el botón para comenzar.
        </p>
        <a href="https://survey.engineeringpr.cl/index.php/{{ $evaluacion->sid }}?token={{ $tokenEncuesta }}&newtest=Y"
           target="_blank"
           class="btn btn-primary btn-lg">
            <i class="fas fa-external-link-alt mr-2"></i> Ir a la Encuesta
        </a>
    </div>
</div>
@endif

@if (!$esColaboradorDeEsta)
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-question-circle mr-1"></i> Preguntas
            <span class="badge badge-light ml-1">{{ $evaluacion->preguntas->count() }}</span>
        </span>
        @sisadmin
        <button class="btn btn-success btn-sm" id="btnNuevaPregunta">
            <i class="fas fa-plus mr-1"></i> Nueva Pregunta
        </button>
        @endsisadmin
    </div>

    @sisadmin
    <div id="panelNuevaPregunta" class="d-none">
        <div class="border-bottom border-success bg-light px-3 py-3">
            <h6 class="text-success font-weight-bold mb-3">
                <i class="fas fa-plus-circle mr-1"></i> Agregar Nueva Pregunta
            </h6>
            <form method="POST" action="{{ route('evaluacion.pregunta.store') }}">
                @csrf
                <input type="hidden" name="Evaluacion_id" value="{{ $evaluacion->id }}">
                <div class="form-row align-items-end">
                    <div class="form-group col-12 col-md-7 mb-2">
                        <label class="small font-weight-bold">
                            Texto <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="texto"
                               class="form-control form-control-sm @error('texto') is-invalid @enderror"
                               value="{{ old('texto') }}"
                               placeholder="Ingrese el texto de la pregunta…" required>
                        @error('texto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-12 col-md-3 mb-2">
                        <label class="small font-weight-bold">
                            Código <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="codigo"
                               class="form-control form-control-sm @error('codigo') is-invalid @enderror"
                               value="{{ old('codigo') }}"
                               placeholder="P01, P_SEGURIDAD…" required>
                        @error('codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-12 col-md-2 mb-2 d-flex">
                        <button type="submit" class="btn btn-success btn-sm btn-block mr-1">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" id="btnCancelarNuevaPregunta">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endsisadmin

    <div class="card-body p-0">
        @if ($evaluacion->preguntas->isEmpty())
            <p class="text-muted text-center my-3">Esta evaluación no tiene preguntas.</p>
        @else
            @foreach ($evaluacion->preguntas as $pregunta)
            <div class="border-bottom {{ $loop->even ? 'bg-light' : '' }} px-3 py-2">
                <div class="d-flex flex-wrap align-items-start justify-content-between">
                    <div class="mr-2">
                        <span class="font-weight-bold text-secondary mr-2">#{{ $loop->iteration }}</span>
                        {{ $pregunta->texto }}
                        @sisadmin
                            @if ($pregunta->codigo)
                                <code class="ml-2 text-muted small">{{ $pregunta->codigo }}</code>
                            @endif
                        @endsisadmin
                    </div>
                    @sisadmin
                    <div class="d-flex align-items-center text-nowrap mt-1">
                        @if ($pregunta->bloqueado)
                            <span class="badge badge-danger mr-2">Bloqueada</span>
                        @else
                            <span class="badge badge-success mr-2">Activa</span>
                        @endif
                        <a href="{{ route('evaluacion.pregunta.edit', $pregunta) }}"
                           class="btn btn-warning btn-xs"
                           title="Editar pregunta y sus alternativas">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                    @endsisadmin
                </div>

                @if ($pregunta->alternativas->isNotEmpty())
                    <div class="mt-2 ml-3 ml-md-4 table-responsive">
                        <table class="table table-sm table-bordered mb-0 bg-white" style="max-width:600px">
                            <thead class="thead-light">
                                <tr>
                                    @sisadmin
                                    <th style="width:80px">Código</th>
                                    @endsisadmin
                                    <th>Respuesta</th>
                                    @sisadmin
                                    <th style="width:80px">Estado</th>
                                    @endsisadmin
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pregunta->alternativas as $alt)
                                <tr>
                                    @sisadmin
                                    <td><code>{{ $alt->codigo ?? '—' }}</code></td>
                                    @endsisadmin
                                    <td>{{ $alt->texto }}</td>
                                    @sisadmin
                                    <td>
                                        @if ($alt->bloqueado)
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
                @else
                    <p class="text-muted small ml-4 mt-1 mb-0">Sin alternativas.</p>
                @endif
            </div>
            @endforeach
        @endif
    </div>
</div>
@endif

@sisadmin
<div class="modal fade" id="modalNuevoRecurso" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-upload mr-1"></i> Subir Nuevo Recurso
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST"
                  action="{{ route('recurso.recurso.store') }}"
                  enctype="multipart/form-data"
                  id="formNuevoRecurso">
                @csrf
                <input type="hidden" name="Evaluacion_id" value="{{ $evaluacion->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre') }}"
                               placeholder="Ej: Manual de seguridad" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Tipo de Recurso <span class="text-danger">*</span></label>
                        <select name="TipoRecurso_id"
                                class="form-control select2 @error('TipoRecurso_id') is-invalid @enderror"
                                required>
                            <option value="">— Seleccione un tipo —</option>
                            @foreach ($tiposRecurso as $tipo)
                                <option value="{{ $tipo->id }}"
                                        {{ old('TipoRecurso_id') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('TipoRecurso_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Descripción</label>
                        <textarea name="descripcion" rows="2"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Descripción breve (opcional)">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Archivo <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="archivo"
                                   class="custom-file-input @error('archivo') is-invalid @enderror"
                                   id="inputArchivoRecurso" required>
                            <label class="custom-file-label" for="inputArchivoRecurso">
                                Seleccionar archivo…
                            </label>
                            @error('archivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Tamaño máximo: 100 MB</small>
                    </div>
                    <div id="uploadProgress" class="d-none">
                        <div class="progress mb-1" style="height:20px">
                            <div id="uploadProgressBar"
                                 class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar" style="width:0%">0%</div>
                        </div>
                        <small class="text-muted">Subiendo archivo, por favor espere…</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardarRecurso">
                        <i class="fas fa-upload mr-1"></i> Subir Recurso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarRecurso" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-1"></i> Editar Recurso
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" id="formEditarRecurso" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="editNombre"
                               class="form-control" required maxlength="255">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Descripción</label>
                        <textarea name="descripcion" id="editDescripcion"
                                  rows="3" class="form-control"
                                  placeholder="Descripción breve (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsisadmin

@endsection

@push('acciones')
<script>
$(document).ready(function () {

    $('#btnNuevaPregunta').on('click', function () {
        const $panel = $('#panelNuevaPregunta');
        $panel.toggleClass('d-none');
        if (!$panel.hasClass('d-none')) {
            $panel.find('input[name="texto"]').focus();
        }
    });
    $('#btnCancelarNuevaPregunta').on('click', function () {
        $('#panelNuevaPregunta').addClass('d-none');
    });
    @if ($errors->any() && old('Evaluacion_id') && !old('nombre'))
        $('#panelNuevaPregunta').removeClass('d-none');
    @endif

    $('#inputArchivoRecurso').on('change', function () {
        const fileName = this.files[0] ? this.files[0].name : 'Seleccionar archivo…';
        $(this).siblings('.custom-file-label').text(fileName);
    });

    @if ($errors->any() && old('nombre') && !old('texto'))
        $('#modalNuevoRecurso').modal('show');
    @endif

    $('#formNuevoRecurso').on('submit', function () {
        const $btn = $('#btnGuardarRecurso');
        const $bar = $('#uploadProgressBar');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Subiendo…');
        $('#uploadProgress').removeClass('d-none');
        let pct = 0;
        const iv = setInterval(function () {
            pct = Math.min(pct + Math.random() * 15, 90);
            $bar.css('width', pct + '%').text(Math.round(pct) + '%');
        }, 300);
        $(window).on('beforeunload', function () { clearInterval(iv); });
    });

    $('#modalNuevoRecurso').on('shown.bs.modal', function () {
        $(this).find('.select2').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modalNuevoRecurso'),
            width: '100%',
        });
    });

    $(document).on('click', '.btnEditarRecurso', function () {
        const action      = $(this).data('action');
        const nombre      = $(this).data('nombre');
        const descripcion = $(this).data('descripcion') || '';

        $('#formEditarRecurso').attr('action', action);
        $('#editNombre').val(nombre);
        $('#editDescripcion').val(descripcion);

        $('#modalEditarRecurso').modal('show');
    });

    $('#modalEditarRecurso').on('shown.bs.modal', function () {
        $('#editNombre').focus().select();
    });

});
</script>
@endpush