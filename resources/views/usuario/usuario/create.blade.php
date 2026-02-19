@extends('layouts.app')
@section('tituloPagina', 'Nuevo Cliente')
@section('cabecera', 'Crear Cliente')
@section('contenido')

@push('estilos')
<style>
    /* Mejorar la apariencia de los tags seleccionados */
    .select2-selection__choice {
        background-color: #007bff !important;
        color: white !important;
        border: none !important;
        border-radius: 4px !important;
        padding: 4px 10px !important;
        margin: 4px !important;
        font-size: 0.9em !important;
    }
    
    .select2-selection__choice__remove {
        color: white !important;
        margin-right: 5px !important;
        font-weight: bold !important;
    }
    
    .select2-selection__choice__remove:hover {
        color: #ffc107 !important;
    }
    
    /* Mejorar el campo de búsqueda */
    .select2-search__field {
        padding: 6px !important;
        font-size: 0.9em !important;
    }
    
    /* Placeholder */
    .select2-selection__placeholder {
        color: #6c757d !important;
        font-size: 0.9em !important;
    }
</style>
@endpush

<x-form metodo="store" textoRecurso="cliente" columnas="7" :rutaBase="$rutaBase">
    <x-input parametro="nombre" label="Nombre" />
    <x-input parametro="rut"    label="RUT" hint="76123456-7" />

    {{-- Usuarios asociados --}}
    <div class="row form-group">
        <label class="col-sm-3 col-form-label">
            Usuarios
            <small class="d-block text-muted" style="font-weight:normal">
                Se asignará rol <em>Cliente</em> automáticamente si no lo tienen.
            </small>
        </label>
        <div class="col">
            <select name="usuarios[]" id="usuarios" class="select2 form-control" multiple style="width:100%">
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}"
                        {{ in_array($usuario->id, old('usuarios', [])) ? 'selected' : '' }}>
                        {{ $usuario->nombre }}
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
        $('#usuarios').select2({ 
            theme: 'bootstrap4', 
            placeholder: '— Seleccionar usuarios —',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush