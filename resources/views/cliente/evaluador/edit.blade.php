@extends('layouts.app')
@section('tituloPagina', 'Editar Evaluador')
@section('cabecera', 'Editar Evaluador')
@section('contenido')

<x-form metodo="update" textoRecurso="evaluador" :objeto="$evaluador" flagDelete="true" columnas="6">
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Cliente</label>
        <div class="col"><p class="form-control-plaintext">{{ $evaluador->cliente->nombre }}</p></div>
    </div>
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Usuario</label>
        <div class="col"><p class="form-control-plaintext">{{ $evaluador->usuario->nombre }}</p></div>
    </div>
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Bloqueado</label>
        <div class="col d-flex align-items-center">
            <input type="hidden" name="bloqueado" value="0">
            <input type="checkbox" name="bloqueado" value="1" {{ $evaluador->bloqueado ? 'checked' : '' }}>
        </div>
    </div>
</x-form>
@endsection