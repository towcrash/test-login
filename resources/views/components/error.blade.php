@props(['metodo'])

@php
	$metodo     = strtoupper($metodo);
	$flagMetodo = in_array($metodo, ['PUT', 'PATCH', 'DELETE']);
@endphp

<form
	method="{{ $flagMetodo ? 'POST' : $metodo }}"
	{{ $attributes->merge(['class' => 'form-horizontal form-label-left']) }}>

	@unless(in_array($metodo, ['HEAD', 'GET', 'OPTIONS']))
		@csrf
	@endunless

	@if($flagMetodo)
		@method($metodo)
	@endif

	{!! $slot !!}
</form>