@extends('layouts.app')
@section('tituloPagina', 'Editar Evaluación')
@section('cabecera', 'Editar Evaluación')
@section('contenido')

<x-form metodo="update" textoRecurso="evaluacion" :objeto="$evaluacion" columnas="6">

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Nombre <span class="text-danger">*</span></label>
        <div class="col-12 col-sm">
            <input type="text" name="nombre" class="form-control"
                   value="{{ old('nombre', $evaluacion->nombre) }}" required>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Descripción</label>
        <div class="col-12 col-sm">
            <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $evaluacion->descripcion) }}</textarea>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">SID</label>
        <div class="col-12 col-sm">
            <input type="text" name="sid" class="form-control"
                   value="{{ old('sid', $evaluacion->sid) }}">
        </div>
    </div>

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Por Evaluador</label>
        <div class="col-12 col-sm d-flex align-items-center">
            <input type="hidden" name="byEvaluador" value="0">
            <input type="checkbox" name="byEvaluador" value="1"
                   {{ old('byEvaluador', $evaluacion->byEvaluador) ? 'checked' : '' }}>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Permanente</label>
        <div class="col-12 col-sm d-flex align-items-center">
            <input type="hidden" name="permanent" value="0">
            <input type="checkbox" name="permanent" value="1"
                   {{ old('permanent', $evaluacion->permanent) ? 'checked' : '' }}>
        </div>
    </div>

    @sisadmin
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Bloqueada</label>
        <div class="col-12 col-sm d-flex align-items-center">
            <input type="hidden" name="bloqueado" value="0">
            <input type="checkbox" name="bloqueado" value="1"
                   {{ old('bloqueado', $evaluacion->bloqueado) ? 'checked' : '' }}>
        </div>
    </div>
    @endsisadmin

</x-form>

@endsection