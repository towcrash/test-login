@extends('layouts.app')
@section('tituloPagina', 'Editar Contratista')
@section('cabecera', 'Editar Contratista')
@section('contenido')

<div class="row justify-content-center">
<div class="col-12 col-md-9 col-lg-8">

<form name="update" method="POST"
    action="{{ route($rutaBase . 'update', ['contratista' => $contratista]) }}"
    class="form-horizontal form-label-left"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <x-input parametro="nombre" label="Nombre" :valor="$contratista->nombre" />
    <x-input parametro="rut"    label="RUT"    :valor="$contratista->rut" hint="76123456-7" />

    {{-- Clientes --}}
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Clientes</label>
        <div class="col-12 col-sm">
            <select name="clientes[]" id="selectClientes" class="select2 form-control" multiple style="width:100%">
                @foreach ($clientes as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ in_array($id, old('clientes', $clientesAsignados)) ? 'selected' : '' }}>
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
                    <option value="{{ $id }}"
                        {{ in_array($id, old('usuarios', $usuariosAsignados)) ? 'selected' : '' }}>
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
                    <option value="{{ $id }}"
                        {{ in_array($id, old('colaboradores', $colaboradoresAsignados)) ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('colaboradores') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    <hr>
    <div class="row">
        <div class="col-12 col-sm mb-2 mb-sm-0">
            <button id="btnAccion" class="btn btn-block btn-success">Actualizar</button>
        </div>
        <div class="col-12 col-sm">
            <button id="btnCancelar" class="btn btn-block btn-primary">Cancelar</button>
        </div>
    </div>

</form>
</div>
</div>

@endsection

@push('acciones')
<script>
$(document).ready(function () {
    ['#selectClientes', '#selectUsuarios', '#selectColaboradores'].forEach(function(sel) {
        $(sel).select2({ theme: 'bootstrap4', placeholder: '— Buscar y seleccionar —' });
    });

    $('#btnCancelar').on('click', function(e) {
        e.preventDefault();
        document.location.href = "{{ route($rutaBase . 'show', ['contratista' => $contratista]) }}";
    });

    $('#btnAccion').on('click', function(e) {
        e.preventDefault();
        if (confirm('¿Está seguro de querer actualizar el contratista: {{ $contratista->id }}?'))
            document.update.submit();
    });
});
</script>
@endpush