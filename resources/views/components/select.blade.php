@props([
	'label',
	'parametro',
	'elementos' => [],
	'opciones',
	'ajax'   => null,

	'objeto' => null,
])

@php
	$flagError = $errors->has($parametro);
	$valPrevio = old($parametro, $objeto->$parametro ?? 'false');

	if ($parametro == 'Sondaje_id') {
		// dd($elementos ? 'Y' : 'N', $elementos);
	}
@endphp

<div class="row form-group">
	<label for="{{ $parametro }}" class="col-sm-3 col-form-label">{{ $label }}</label>
	<div class="col">
		<select
			id="{{ $parametro }}"
			name="{{ $parametro }}"
			class="select2 form-control {{ $flagError ? 'is-invalid' : '' }}"
			{{ $ajax && !$objeto ? 'disabled' : '' }}
			style="width: 100%;"
			>
				@if (isset($opciones['padre']))
					<option disabled selected>- {{ $opciones['padre'] }} -</option>
				@elseif (isset($opciones['local']))
					<option disabled selected>- {{ $opciones['local'] }} -</option>
				@endif

				@foreach ($elementos as $key => $value)
					<option value="{{ $key }}" {{ ($key == $valPrevio) ? 'selected' : '' }}>
						{{ $value }}
					</option>
				@endforeach
		</select>

		@if ($flagError)
			<span class='invalid-feedback'>
				<strong> {{ $errors->first($parametro) }} </strong>
			</span>
		@endif
	</div>
</div>

@push('acciones')
<script>
$(document).ready(function() {
	$('#{{ $parametro }}').select2({theme: 'bootstrap4'});

	@if ($ajax)
		var singleton{{ $parametro }} = true;
		$('#{{ $ajax['padre'] }}').on('change', function(){
			@if ($parametro == 'Sondaje_id')
				console.log('a')
			@endif
			var id        = this.value;
			var varSelect = $('#{{ $parametro }}');

			varSelect.attr({'disabled': 'disabled'});
			if (id == 0) {
				varSelect.empty();
				varSelect.append("<option disabled selected>- {{ $opciones['padre'] }} -</option>");
			} else {
				var url = '{{ route($ajax['ruta'], ":id") }}';
				url     = url.replace(':id', id);
				$.get(url, function(data) {
					varSelect.empty();
					if (Object.keys(data).length == 0) {
						varSelect.append("<option disabled selected>- {{ $opciones['zero'] }} -</option>");
					} else {
						varSelect.removeAttr('disabled');
						varSelect.append("<option disabled selected>- {{ $opciones['local'] }} -</option>");
						$.each(data, function(index, valor){
							varSelect.append('<option value="'+ index + '">' + valor + '</option>');
						});

						if (singleton{{ $parametro }} && {{ $valPrevio }}) {
							singleton{{ $parametro }} = false;
							varSelect.val({{ $valPrevio }});
						}
					}
				});
			}
		});

		$('#{{ $ajax['padre'] }}').change();
	@elseif ( old($parametro) )
		$('#{{ $parametro }}').val({{ old($parametro) }}).change();
	@endif
});
</script>
@endpush
