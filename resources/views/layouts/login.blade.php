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

	@vite(['resources/sass/app.scss', 'resources/js/app.js'])

	<style>
		.login-page:before {
			content: ' ';
			display: block;
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			opacity: 0.6;
			background-image: url({{ Storage::disk('recursos')->url('fondoLogin.jpg') }});
			background-repeat: no-repeat;
			background-position: 50% 0;
			background-size: cover;
		}
	</style>

</head>
<body class="hold-transition login-page">
	@yield('content')
	@stack('acciones')

	<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
	<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
	<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
	<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

	@include('layouts.partials.acciones')
</body>
</html>
