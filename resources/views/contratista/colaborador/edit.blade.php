@extends('layouts.app')
@section('tituloPagina', 'Editar Colaborador')
@section('cabecera', 'Editar Colaborador')
@section('contenido')

<x-form metodo="update" textoRecurso="colaborador" :objeto="$colaborador" flagDelete="true" columnas="6" :rutaBase="$rutaBase">
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Contratista</label>
        <div class="col"><p class="form-control-plaintext">{{ $colaborador->contratista->nombre }}</p></div>
    </div>
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Usuario</label>
        <div class="col"><p class="form-control-plaintext">{{ $colaborador->usuario->nombre }}</p></div>
    </div>
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Bloqueado</label>
        <div class="col d-flex align-items-center">
            <input type="hidden" name="bloqueado" value="0">
            <input type="checkbox" name="bloqueado" value="1" {{ $colaborador->bloqueado ? 'checked' : '' }}>
        </div>
    </div>
</x-form>
@endsection