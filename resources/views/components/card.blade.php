@props([
	'titulo',
	'pie' => null,
	'colapsed' => false,
])

<div class="card card-primary card-outline {{ $colapsed ? 'collapsed-card' : '' }}">
	<div class="card-header">
		<h3 class="card-title">{{ $titulo }}</h3>

		<div class="card-tools">
			<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
				<i class="fa fa-{{ $colapsed ? 'plus' : 'minus' }}"></i>
			</button>
		</div>
	</div>
	
	<div class="card-body">
		{!! $slot !!}
	</div>
	
	@if ($pie)
		<div class="card-footer">
			{{ $pie }}
		</div>
	@endif
</div>

@push('acciones')
@endpush
