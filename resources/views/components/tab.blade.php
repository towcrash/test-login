@props([
	'name',
	'head',
	'active' => false,
])

@push('headTab')
	<li class="nav-item">
		<a class="nav-link {{ $active ? 'active' : '' }}" id="custom-tabs-three-{{ $name }}-tab" data-toggle="pill" href="#custom-tabs-three-{{ $name }}" role="tab" aria-controls="custom-tabs-three-{{ $name }}" aria-selected="true">{{ $head }}</a>
	</li>
@endpush

@push('bodyTab')
	<div class="tab-pane fade {{ $active ? 'active show' : '' }}" id="custom-tabs-three-{{ $name }}" role="tabpanel" aria-labelledby="custom-tabs-three-{{ $name }}-tab">
		{!! $slot !!}
	</div>
@endpush
