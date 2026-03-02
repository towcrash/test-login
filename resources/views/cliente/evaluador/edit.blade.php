@extends('layouts.app')
@section('tituloPagina', 'Editar Evaluador')
@section('cabecera', 'Editar Evaluador')
@section('contenido')

<div class="row justify-content-center">
<div class="col-12 col-md-8 col-lg-7">

<form name="update" method="POST"
    action="{{ route($rutaBase . 'update', ['evaluador' => $evaluador]) }}"
    class="form-horizontal form-label-left"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Cliente --}}
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label" for="selectCliente">Cliente</label>
        <div class="col-12 col-sm">
            <select name="Cliente_id" id="selectCliente" class="select2 form-control" style="width:100%">
                @foreach ($clientes as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ old('Cliente_id', $evaluador->Cliente_id) == $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('Cliente_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Usuario --}}
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label" for="selectUsuario">Usuario</label>
        <div class="col-12 col-sm">
            <select name="Usuario_id" id="selectUsuario" class="select2 form-control" style="width:100%">
                @foreach ($usuarios as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ old('Usuario_id', $evaluador->Usuario_id) == $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('Usuario_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Bloqueado --}}
    <div class="row form-group">
        <label class="col-12 col-sm-3 col-form-label">Bloqueado</label>
        <div class="col-12 col-sm d-flex align-items-center">
            <input type="hidden" name="bloqueado" value="0">
            <input type="checkbox" name="bloqueado" value="1"
                   {{ old('bloqueado', $evaluador->bloqueado) ? 'checked' : '' }}>
            <small class="text-muted ml-2">Marcar para bloquear este evaluador</small>
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
    ['#selectCliente', '#selectUsuario'].forEach(function(sel) {
        $(sel).select2({ theme: 'bootstrap4', placeholder: '— Seleccionar —' });
    });

    $('#btnCancelar').on('click', function(e) {
        e.preventDefault();
        document.location.href = "{{ route($rutaBase . 'index') }}";
    });

    $('#btnAccion').on('click', function(e) {
        e.preventDefault();
        if (confirm('¿Está seguro de querer actualizar este evaluador?'))
            document.update.submit();
    });
});
</script>
@endpush