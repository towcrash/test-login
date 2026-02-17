<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="{{ Storage::disk('logos')->url('swdd_isotipo.svg') }}">

	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'SWDD') }}</title>

	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
	<link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
	<link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
	<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

	@stack('estilos')

	@vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini">
	<div class="wrapper">
		@include('layouts.partials.top')
		@include('layouts.partials.navegacion')

		<div class="content-wrapper">
			<div class="content-header">
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-8">
							<h1 class="m-0">@yield('tituloPagina')</h1>
						</div>
						<div class="col-2">
							@yield('accionGlobal')
						</div>
					</div>
				</div>
			</div>
			<div class="content">
				<div class="container-fluid">
					<div class="row">
						<div class="col">
							<div class="card card-primary card-outline">
								<div class="card-header">
									<h3 class="card-title">@yield('cabecera')<strong>@yield('cabecera2')</strong></h3>

									<div class="card-tools">
										<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
											<i class="fa fa-minus"></i>
										</button>
									</div>
								</div>
								
								<div class="card-body">
									@yield('contenido')
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		@include('layouts.partials.footer')
	</div>

	<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
	<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
	<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
	<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
	<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

	@include('layouts.partials.acciones')
	@stack('acciones')
</body>
</html>