@extends('layouts.app')
@section('tituloPagina', 'Editar Pregunta')
@section('cabecera', 'Editar Pregunta ')
@section('cabecera2', 'Evaluación: ' . $pregunta->evaluacion->nombre)

@section('accionGlobal')
    <a href="{{ route('evaluacion.evaluacion.show', $pregunta->evaluacion) }}"
       class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver a la Evaluación
    </a>
@endsection

@section('contenido')

<div class="card mb-4">
    <div class="card-header bg-warning">
        <i class="fas fa-edit mr-1"></i> Datos de la Pregunta
    </div>
    <div class="card-body">
        <form method="POST"
              action="{{ route('evaluacion.pregunta.update', $pregunta) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="font-weight-bold">
                    Texto <span class="text-danger">*</span>
                </label>
                <input type="text" name="texto" class="form-control @error('texto') is-invalid @enderror"
                       value="{{ old('texto', $pregunta->texto) }}" required>
                @error('texto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label class="font-weight-bold">
                        Código <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror"
                           value="{{ old('codigo', $pregunta->codigo) }}"
                           placeholder="Ej: P01, P_SEGURIDAD…" required>
                    @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group col-12 col-md-6">
                    <label class="font-weight-bold">Estado</label>
                    <div class="d-flex align-items-center mt-2">
                        <input type="hidden" name="bloqueado" value="0">
                        <input type="checkbox" name="bloqueado" value="1" id="chkBloqueado"
                               {{ old('bloqueado', $pregunta->bloqueado) ? 'checked' : '' }}>
                        <label class="mb-0 ml-2" for="chkBloqueado">Bloqueada</label>
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-warning mr-2">
                    <i class="fas fa-save mr-1"></i> Guardar Pregunta
                </button>
                <a href="{{ route('evaluacion.evaluacion.show', $pregunta->evaluacion) }}"
                   class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-list-ul mr-1"></i> Alternativas
            <span class="badge badge-light ml-1">{{ $pregunta->alternativas->count() }}</span>
        </span>
    </div>

    <div class="card-body p-0">
        @if ($pregunta->alternativas->isEmpty())
            <p class="text-muted text-center my-3">Esta pregunta no tiene alternativas aún.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3" style="width:100px">Código</th>
                            <th>Texto</th>
                            <th style="width:90px">Estado</th>
                            <th style="width:130px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pregunta->alternativas as $alt)
                        <tr class="{{ $alt->bloqueado ? 'table-danger' : '' }}">
                            <td class="pl-3">
                                <code>{{ $alt->codigo ?? '—' }}</code>
                            </td>
                            <td>{{ $alt->texto }}</td>
                            <td>
                                @if ($alt->bloqueado)
                                    <span class="badge badge-danger">Bloqueada</span>
                                @else
                                    <span class="badge badge-success">Activa</span>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                        class="btn btn-warning btn-xs btn-editar-alt"
                                        data-id="{{ $alt->id }}"
                                        data-texto="{{ $alt->texto }}"
                                        data-codigo="{{ $alt->codigo ?? '' }}"
                                        data-bloqueado="{{ $alt->bloqueado }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form method="POST"
                                      action="{{ route('evaluacion.alternativa.destroy', $alt) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-xs {{ $alt->bloqueado ? 'btn-success' : 'btn-danger' }}"
                                            title="{{ $alt->bloqueado ? 'Desbloquear' : 'Bloquear' }}">
                                        <i class="fas {{ $alt->bloqueado ? 'fa-unlock' : 'fa-lock' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <tr class="d-none table-warning" id="fila-edit-alt-{{ $alt->id }}">
                            <td colspan="4" class="px-3 py-2">
                                <form method="POST"
                                      action="{{ route('evaluacion.alternativa.update', $alt) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-row align-items-end">
                                        <div class="form-group col-12 col-md-5 mb-1">
                                            <label class="small font-weight-bold">
                                                Texto <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="texto"
                                                   class="form-control form-control-sm"
                                                   value="{{ $alt->texto }}" required>
                                        </div>
                                        <div class="form-group col-12 col-md-3 mb-1">
                                            <label class="small font-weight-bold">
                                                Código <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="codigo"
                                                   class="form-control form-control-sm"
                                                   value="{{ $alt->codigo ?? '' }}"
                                                   placeholder="A, B, R01…" required>
                                        </div>
                                        <div class="form-group col-12 col-md-2 mb-1">
                                            <label class="small font-weight-bold">Estado</label>
                                            <div class="d-flex align-items-center mt-1">
                                                <input type="hidden" name="bloqueado" value="0">
                                                <input type="checkbox" name="bloqueado" value="1"
                                                       {{ $alt->bloqueado ? 'checked' : '' }}>
                                                <small class="ml-1">Bloqueada</small>
                                            </div>
                                        </div>
                                        <div class="form-group col-12 col-md-2 mb-1 d-flex">
                                            <button type="submit" class="btn btn-warning btn-sm btn-block mr-1">
                                                <i class="fas fa-save mr-1"></i> Guardar
                                            </button>
                                            <button type="button"
                                                    class="btn btn-secondary btn-sm btn-cancelar-edit-alt"
                                                    data-id="{{ $alt->id }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fas fa-plus-circle mr-1"></i> Agregar Nueva Alternativa
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('evaluacion.alternativa.store') }}">
            @csrf
            <input type="hidden" name="Pregunta_id" value="{{ $pregunta->id }}">

            <div class="form-row align-items-end">
                <div class="form-group col-12 col-md-7 mb-2 mb-md-0">
                    <label class="font-weight-bold">
                        Texto <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="texto"
                           class="form-control @error('texto') is-invalid @enderror"
                           value="{{ old('texto') }}"
                           placeholder="Texto de la alternativa…" required>
                    @error('texto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-12 col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold">
                        Código <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="codigo"
                           class="form-control @error('codigo') is-invalid @enderror"
                           value="{{ old('codigo') }}"
                           placeholder="A, B, R01…" required>
                    @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-12 col-md-2 mb-0">
                    <label class="d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-plus mr-1"></i> Agregar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('acciones')
<script>
$(document).ready(function () {

    $(document).on('click', '.btn-editar-alt', function () {
        const id = $(this).data('id');
        $('[id^="fila-edit-alt-"]').addClass('d-none');
        $('#fila-edit-alt-' + id).removeClass('d-none');
        $('#fila-edit-alt-' + id + ' input[name="texto"]').focus();
    });

    $(document).on('click', '.btn-cancelar-edit-alt', function () {
        const id = $(this).data('id');
        $('#fila-edit-alt-' + id).addClass('d-none');
    });

});
</script>
@endpush