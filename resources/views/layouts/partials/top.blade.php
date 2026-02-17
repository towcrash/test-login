<nav class="main-header navbar navbar-expand navbar-white navbar-light">
	<ul class="navbar-nav">
		<li class="nav-item">
			<a class="nav-link" data-widget="pushmenu" data-toggle="collapse" href="javascript:toggleNav()"><i class="fa fa-bars fa-inverse"></i></a>
		</li>
	</ul>

	<ul class="navbar-nav ml-auto">
		@if (Auth::user()->isSisAdmin())
			<li class="nav-item">
				<a class="nav-link" href="#" role="button">
					<i class="fa fa-home fa-inverse"></i>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#" role="button">
					<i class="fa fa-user fa-inverse"></i>
				</a>
			</li>
		@endif
		@if (Auth::user()->isSisAdmin())
			{{-- <li class="nav-item">
				<a class="nav-link" href="{{ route('admin.usuario.usuario.index') }}" role="button">
					<i class="fa fa-users fa-inverse"></i>
				</a>
			</li> --}}
			<li class="nav-item">
				<a class="nav-link" data-widget="fullscreen" href="#" role="button">
					<i class="fa fa-expand fa-inverse"></i>
				</a>
			</li>
		@endif
		<li class="nav-item dropdown">
			<a class="nav-link" data-toggle="dropdown" href="#">
				<span class="usuario">{{ Auth::user()->nombre }}</span>
			</a>
			<div class="dropdown-menu dropdown-menu-right" style="left: inherit; right: 0px;">
				{{-- 
				<a href="{{ route('dashboard') }}" class="dropdown-item">
					<i class="fa fa-home icono"></i>Inicio
				</a>
				 --}}
				{{-- 
				<div class="dropdown-divider"></div>
				<a href="{{ route('usuarios.index') }}" class="dropdown-item">
					<i class="fa fa-user icono"></i>Perfil
				</a>
				 --}}
				{{-- <div class="dropdown-divider"></div> --}}
				<form method="POST" action="{{ route('auth.logout') }}" name="formLogout">
					{{ csrf_field() }}
					<button class="dropdown-item"><i class="fa fa-times icono"></i> Cerrar Sesión</button>
				</form>
			</div>
		</li>
		@if (Session::has('impersonator_id'))
			<li class="nav-item">
				<form method="POST" action="{{ route('impersonation.destroy') }}">
					{{ csrf_field() }} {{ method_field('DELETE') }}
					<button type="submit" class="btn btn-danger">Dejar de personificar</button>
				</form>
			</li>
		@endif
	</ul>
</nav>
