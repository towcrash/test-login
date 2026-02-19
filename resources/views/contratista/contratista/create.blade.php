@extends('layouts.app')
@section('tituloPagina', 'Nuevo Contratista')
@section('cabecera', 'Crear Contratista')
@section('contenido')

<x-form metodo="store" textoRecurso="contratista" columnas="6" :rutaBase="$rutaBase">
    <x-input parametro="nombre" label="Nombre" />
    <x-input parametro="rut"    label="RUT" hint="77123456-7" />
</x-form>
@endsection