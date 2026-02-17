@props([
	'label'       => null,
	'hint'        => null,
	'parametro',
	'objeto'      => null,
	'padre'       => null,
	'default'     => null,
	'time'        => null,
])

@php
	if (!$label)
		$label = ucfirst($parametro);
	if (!$hint)
		$hint  = $label;

	$valor     = old($parametro, $objeto->$parametro ?? null);
	$flagError = $errors->has($parametro);

	$formato   = 'yyyy-MM-dd';
	if ($time)
		$formato .= ' 00:00';
@endphp

<div class="row form-group">
	<label for="{{ $parametro }}" class="col-sm-3 col-form-label">{{ $label }}</label>
	<div class="col">
		<input
			type="text"
			id="{{ $parametro }}"
			name="{{ $parametro }}"
			class="form-control {{ $flagError ? 'is-invalid' : '' }}"
			placeholder="{{ $hint }}"
			value="{{ $valor }}"
			{{ $valor || !$padre ? '' : 'disabled' }}
			>

		@if ($flagError)
			<span class='invalid-feedback'>
				<strong> {{ $errors->first($parametro) }} </strong>
			</span>
		@endif
	</div>
</div>

@push('acciones')
<script>
	var  TD_{{ $parametro }};
	$(document).ready(function (){
		TD_{{ $parametro }} = new TempusDominus($('#{{ $parametro }}').get(0), {
			@if ($default)
				defaultDate  : moment().format(),
			@endif
			@if (!$time)
				display: {
					components: {
						clock: false
					}
				},
			@endif
			localization : {
				format              : '{{ $formato }}',
				startOfTheWeek      : 1,
				dayViewHeaderFormat : 'MMMM yyyy',
			},
		});
	});
</script>
@if ($padre)
<script>
	$(document).ready(function (){
		if( $('#{{ $padre }}').val() )
			$('#{{ $parametro }}').removeAttr('disabled');
	});
	$('#{{ $padre }}').on('change', function(){
		$('#{{ $parametro }}').removeAttr('disabled');
	});
</script>
@endif
@endpush
