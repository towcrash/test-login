@extends('layouts.app')
@section('tituloPagina', 'Nuevo Colaborador')
@section('cabecera', 'Asignar Colaborador')
@section('contenido')

<x-form metodo="store" textoRecurso="colaborador" columnas="6">
    <x-select label="Contratista" parametro="Contratista_id" :elementos="$contratistas"
              :opciones="['local' => 'Seleccionar contratista']" />
    <x-select label="Usuario"     parametro="Usuario_id"     :elementos="$usuarios"
              :opciones="['local' => 'Seleccionar usuario']" />
</x-form>
@endsection