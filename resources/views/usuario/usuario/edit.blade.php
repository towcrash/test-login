@extends('layouts.app')

@section('tituloPagina', 'Editar Usuario')
@section('cabecera', 'Editar: ')
@section('cabecera2', $usuario->user)

@section('contenido')

<x-form metodo="update" textoRecurso="usuario" :objeto="$usuario" flagDelete="true" columnas="7" :rutaBase="$rutaBase">

    <x-input parametro="user"   label="Usuario"   :objeto="$usuario" />
    <x-input parametro="password" label="Nueva contraseña" type="password" hint="Dejar vacío para no cambiar" />
    <x-input parametro="nombre" label="Nombre completo" :objeto="$usuario" />
    <x-input parametro="rut"    label="RUT" :objeto="$usuario" />
    <x-input parametro="email"  label="Email" type="email" :objeto="$usuario" />
    <x-dateTime parametro="vigencia" label="Vigencia" :objeto="$usuario" hint="Dejar vacío = sin límite" />

    {{-- Bloqueado --}}
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Bloqueado</label>
        <div class="col d-flex align-items-center">
            <input type="hidden" name="bloqueado" value="0">
            <input type="checkbox" name="bloqueado" value="1"
                   {{ old('bloqueado', $usuario->bloqueado) ? 'checked' : '' }}>
            <small class="text-muted ml-2">Marcar para bloquear acceso al usuario</small>
        </div>
    </div>

    {{-- Roles --}}
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">Roles</label>
        <div class="col">
            @foreach ($roles as $id => $nombre)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox"
                           name="roles[]" id="rol_{{ $id }}" value="{{ $id }}"
                           {{ in_array($id, old('roles', $rolesAsignados)) ? 'checked' : '' }}>
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