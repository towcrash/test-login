@extends('layouts.app')
@section('tituloPagina', 'Editar Cliente')
@section('cabecera', 'Editar Cliente: ')
@section('cabecera2', $cliente->nombre)
@section('contenido')

<x-form metodo="update" textoRecurso="cliente" :objeto="$cliente" flagDelete="true" columnas="6">
    <x-input parametro="nombre" label="Nombre" :objeto="$cliente" />
    <x-input parametro="rut"    label="RUT"    :objeto="$cliente" />
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Bloqueado</label>
        <div class="col d-flex align-items-center">
            <input type="hidden"   name="bloqueado" value="0">
            <input type="checkbox" name="bloqueado" value="1" {{ $cliente->bloqueado ? 'checked' : '' }}>
        </div>
    </div>
</x-form>
@endsection