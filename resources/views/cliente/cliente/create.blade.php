@extends('layouts.app')
@section('tituloPagina', 'Nuevo Cliente')
@section('cabecera', 'Crear Cliente')
@section('contenido')

<x-form metodo="store" textoRecurso="cliente" columnas="7">
    <x-input parametro="nombre" label="Nombre" />
    <x-input parametro="rut"    label="RUT" hint="76123456-7" />

    {{-- Usuarios asociados --}}
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">
            Usuarios
            <small class="d-block text-muted" style="font-weight:normal">
                Se asignará rol <em>Cliente</em> automáticamente si no lo tienen.
            </small>
        </label>
        <div class="col-12 col-sm">
            <select name="usuarios[]" id="usuarios" class="select2 form-control" multiple style="width:100%">
                @foreach ($usuarios as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ in_array($id, old('usuarios', [])) ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('usuarios')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
</x-form>
@endsection

@push('acciones')
<script>
    $(document).ready(function () {
        $('#usuarios').select2({ theme: 'bootstrap4', placeholder: '— Seleccionar usuarios —' });
    });
</script>
@endpush