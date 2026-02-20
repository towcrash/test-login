@extends('layouts.app')

@section('tituloPagina', 'Nuevo Usuario')
@section('cabecera', 'Crear Usuario')

@section('contenido')

<x-form metodo="store" textoRecurso="usuario" columnas="7">

    <x-input parametro="user"   label="Usuario"   hint="Nombre de acceso" />
    <x-input parametro="password" label="Contraseña" type="password" />
    <x-input parametro="nombre" label="Nombre completo" />
    <x-input parametro="rut"    label="RUT" hint="12345678-9" />
    <x-input parametro="email"  label="Email" type="email" />
    <x-dateTime parametro="vigencia" label="Vigencia" hint="Dejar vacío = sin límite" />

    {{-- Roles --}}
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Roles</label>
        <div class="col">
            @foreach ($roles as $id => $nombre)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox"
                           name="roles[]" id="rol_{{ $id }}" value="{{ $id }}"
                           {{ in_array($id, old('roles', [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="rol_{{ $id }}">{{ $nombre }}</label>
                </div>
            @endforeach
            @error('roles')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

</x-form>

@endsection