@php
	if( $errors->any() ) SessionService::form();
	// if( $errors->any() ) dd($errors);
@endphp

{{-- Mensajes de Session --}}
<script>
	toastr.options = {
		closeButton  : true,
		progressBar  : true,
		showDuration : 500,
		hideDuration : 1000,
		timeOut      : {{ SessionService::getTiempos() }},
		showMethod   : 'fadeIn',
		hideMethod   : 'fadeOut',
	};

	@foreach(SessionService::getMensajes() as $tipo => $mensajes)
		@foreach ($mensajes as $mensaje)
			toastr['{{ $tipo }}']('{!! $mensaje['mensaje'] !!}', '{{ $mensaje['titulo'] }}')
		@endforeach
	@endforeach
</script>
{{ SessionService::forget() }}
