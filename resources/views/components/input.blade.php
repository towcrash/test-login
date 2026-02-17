@props([
	'label'       => null,
	'hint'        => null,
	'parametro',
	'objeto'      => null,
	'padre'       => null,
	'type'        => 'text',
])

@php
	if (!$label)
		$label       = ucfirst($parametro);
	if (!$hint)
		$hint = $label;

	$valor     = null;
	if ($parametro != 'password')
		$valor = old($parametro, $objeto->$parametro ?? null);
	$flagError = $errors->has($parametro);
@endphp

<div class="row form-group">
	<label for="{{ $parametro }}" class="col-sm-3 col-form-label">{{ $label }}</label>
	<div class="col">
		@if ($type == 'file')
			<input type="file" name="{{ $parametro }}" id="{{ $parametro }}" class="form-control {{ $flagError ? 'is-invalid' : '' }}">
		@else
			<input
				type="{{ $type }}"
				id="{{ $parametro }}"
				name="{{ $parametro }}"
				class="form-control {{ $flagError ? 'is-invalid' : '' }}"
				placeholder="{{ $hint }}"
				value="{{ $valor }}"
				{{ $valor || !$padre ? '' : 'disabled' }}
				>
		@endif

		@if ($flagError)
			<span class='invalid-feedback'>
				<strong> {{ $errors->first($parametro) }} </strong>
			</span>
		@endif
	</div>
</div>

@push('acciones')
@if ($padre)
<script>
	$(document).ready(function (){
		if( $('#{{ $padre }}').val() )
			$('#{{ $parametro }}').removeAttr('disabled');

		$('#{{ $padre }}').on('change', function(){
			$('#{{ $parametro }}').removeAttr('disabled');
		});
	});
</script>
@endif
@endpush
