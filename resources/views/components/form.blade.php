@props([
	'metodo',
	'textoRecurso',
	'objeto'     => null,
	'flagDelete' => null,
	'columnas'   => 6,
	'back'       => 'index',
	'rutaBase',
])

@php
	$flagMetodo   = $metodo != 'store';
	$textoMetodo  = $flagMetodo ? 'Actualizar' : 'Crear';

	$cadenaAlerta = ($objeto ? ': ' . $objeto->id : '') . '?';
@endphp

@if ($flagDelete)
	<form name="delete" method="POST" 
	action="{{ route($rutaBase . 'destroy', $objeto) }}">
		@csrf
		@method('DELETE')
	</form>
@endif

<form
	name="{{ $metodo }}"
	method="POST"
	@if ($flagMetodo)
		action="{{ route($rutaBase . $metodo, $objeto) }}"
	@else
		action="{{ route($rutaBase . $metodo) }}"
	@endif
	{{ $attributes->merge(['class' => 'form-horizontal form-label-left']) }}
	enctype="multipart/form-data"
	>
		@csrf
		@if ($flagMetodo)
			@method('PUT')
		@endif

		<div class="row justify-content-center">
			<div class="col-{{ $columnas }} col-lg-8">
				{!! $slot !!}
				<hr>
			</div>
		</div>
		<div class="row justify-content-center">
			<div class="col-{{ $columnas }} col-lg-8">
				<div class="row">
					<div class="col">
						<button id="btnAccion" class="btn btn-block btn-success">{{ $textoMetodo }}</button>
					</div>
					@if ($flagDelete)
						<div class="col">
							<button id="btnEliminar" class="btn btn-block btn-danger">Eliminar</button>
						</div>
					@endif
					<div class="col">
						<button id="btnCancelar" class="btn btn-block btn-primary">Cancelar</button>
					</div>
				</div>
			</div>
		</div>
</form>


@push('acciones')
	<script>
		$('#btnCancelar').on('click', function(e) {
			e.preventDefault();
			@if ($back == 'index')
				document.location.href = "{{ route($rutaBase . $back) }}";
			@else
				document.location.href = "{{ route($rutaBase . $back, $objeto) }}";
			@endif
		});
		@if ($flagDelete)
			$('#btnEliminar').on('click', function(e) {
				e.preventDefault();
				if( confirm('¿Esta seguro de querer eliminar el {{ $textoRecurso }}: ' + {{ $objeto->id ?? ' ' }} + '?') )
					document.delete.submit();
			});
		@endif
		$('#btnAccion').on('click', function(e) {
			e.preventDefault();
			if( confirm('¿Esta seguro de querer {{ strtolower($textoMetodo) }} el {{ $textoRecurso }}' + '{{ $cadenaAlerta }}') )
				document.{{ $metodo }}.submit();
		});
	</script>
@endpush
