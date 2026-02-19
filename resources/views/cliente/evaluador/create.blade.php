@extends('layouts.app')
@section('tituloPagina', 'Nuevo Evaluador')
@section('cabecera', 'Asignar Evaluador')
@section('contenido')

<x-form metodo="store" textoRecurso="evaluador" columnas="6" :rutaBase="$rutaBase">
    <x-select label="Cliente"  parametro="Cliente_id" :elementos="$clientes"
              :opciones="['local' => 'Seleccionar cliente']" />
    <x-select label="Usuario"  parametro="Usuario_id" :elementos="$usuarios"
              :opciones="['local' => 'Seleccionar usuario']" />
</x-form>
@endsection