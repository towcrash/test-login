@extends('layouts.app')
@section('tituloPagina', 'Nuevo Contratista')
@section('cabecera', 'Crear Contratista')
@section('contenido')

<x-form metodo="store" textoRecurso="contratista" columnas="7">
    <x-input parametro="nombre" label="Nombre" />
    <x-input parametro="rut"    label="RUT" hint="76123456-7" />

    {{-- Clientes --}}
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Clientes</label>
        <div class="col-12 col-sm">
            <select name="clientes[]" id="selectClientes" class="select2 form-control" multiple style="width:100%">
                @foreach ($clientes as $id => $nombre)
                    <option value="{{ $id }}" {{ in_array($id, old('clientes', [])) ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('clientes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Usuarios --}}
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Usuarios</label>
        <div class="col-12 col-sm">
            <select name="usuarios[]" id="selectUsuarios" class="select2 form-control" multiple style="width:100%">
                @foreach ($usuarios as $id => $nombre)
                    <option value="{{ $id }}" {{ in_array($id, old('usuarios', [])) ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('usuarios') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Colaboradores --}}
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">
            Colaboradores
            <small class="d-block text-muted" style="font-weight:normal">Solo usuarios con rol <em>Colaborador</em>.</small>
        </label>
        <div class="col-12 col-sm">
            <select name="colaboradores[]" id="selectColaboradores" class="select2 form-control" multiple style="width:100%">
                @foreach ($colaboradores as $id => $nombre)
                    <option value="{{ $id }}" {{ in_array($id, old('colaboradores', [])) ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('colaboradores') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>
</x-form>

@endsection

@push('acciones')
<script>
$(document).ready(function () {
    ['#selectClientes', '#selectUsuarios', '#selectColaboradores'].forEach(function(sel) {
        $(sel).select2({ theme: 'bootstrap4', placeholder: '— Buscar y seleccionar —' });
    });
});
</script>
@endpush