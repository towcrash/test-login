<li class="nav-item {{ $menu['menu']->open ? 'menu-is-opening menu-open' : '' }}">
	<a href="#" class="nav-link">
		<p>
			{{ $menu['menu']->nombre }}
			<i class="right fas fa-angle-left"></i>
		</p>
	</a>

	<ul class="nav nav-treeview" style="display: {{ $menu['menu']->open ? 'block' : 'none' }};">
		@foreach ($menu['rutas'] as $ruta)
			<li class="nav-item ml-2">
				<a href="{{ route($ruta->ruta) }}" class="nav-link {{ request()->routeIs($ruta->ruta) ? 'active' : '' }}">
					<i class="nav-icon {{ $ruta->icono }}"></i>
					<p>{{ $ruta->nombre }}</p>
				</a>
			</li>
		@endforeach
		@foreach ($menu['hijos'] as $hijo)
			@include('layouts.partials.navegacionMenu', ['menu' => $hijo])
		@endforeach
	</ul>
</li>