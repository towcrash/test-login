@extends('layouts.app')
@section('tituloPagina', 'Editar Contratista')
@section('cabecera', 'Editar: ')
@section('cabecera2', $contratista->nombre)
@section('contenido')

<x-form metodo="update" textoRecurso="contratista" :objeto="$contratista" flagDelete="true" columnas="6" :rutaBase="$rutaBase">
    <x-input parametro="nombre" label="Nombre" :objeto="$contratista" />
    <x-input parametro="rut"    label="RUT"    :objeto="$contratista" />
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Bloqueado</label>
        <div class="col d-flex align-items-center">
            <input type="hidden" name="bloqueado" value="0">
            <input type="checkbox" name="bloqueado" value="1" {{ $contratista->bloqueado ? 'checked' : '' }}>
        </div>
    </div>
</x-form>
@endsection