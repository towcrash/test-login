@extends('layouts.app')
@section('tituloPagina', 'Nueva Evaluación')
@section('cabecera', 'Nueva Evaluación')
@section('contenido')

<x-form metodo="store" textoRecurso="evaluacion" columnas="6">

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Nombre <span class="text-danger">*</span></label>
        <div class="col-12 col-sm">
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Descripción</label>
        <div class="col-12 col-sm">
            <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">SID</label>
        <div class="col-12 col-sm">
            <input type="text" name="sid" class="form-control" value="{{ old('sid') }}">
        </div>
    </div>

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Por Evaluador</label>
        <div class="col-12 col-sm d-flex align-items-center">
            <input type="hidden" name="byEvaluador" value="0">
            <input type="checkbox" name="byEvaluador" value="1" {{ old('byEvaluador') ? 'checked' : '' }}>
        </div>
    </div>

    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Permanente</label>
        <div class="col-12 col-sm d-flex align-items-center">
            <input type="hidden" name="permanent" value="0">
            <input type="checkbox" name="permanent" value="1" {{ old('permanent') ? 'checked' : '' }}>
        </div>
    </div>

</x-form>

@endsection