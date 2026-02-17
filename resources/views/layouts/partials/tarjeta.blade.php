<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">@yield('titulo')</h3>

		<div class="card-tools">
			<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
				<i class="fa fa-minus"></i>
			</button>
		</div>
	</div>
	
	<div class="card-body p-4">
		@yield('contenido')
	</div>
</div>
@include('layouts.partials.accionesSession')
