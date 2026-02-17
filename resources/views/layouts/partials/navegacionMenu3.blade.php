<li class="nav-header">{{ $menu['menu']->nombre }}</li>
@foreach ($menu['rutas'] as $ruta)
	<li class="nav-item ml-2">
		<a href="{{ route($ruta->ruta) }}" class="nav-link {{ true ? 'active' : '' }}">
			<i class="nav-icon {{ $ruta->icono }}"></i>
			<p>{{ $ruta->nombre }}</p>
		</a>
	</li>
@endforeach

@if ($menu['hijos'])
	<li class="nav-item">
		<a href="#" class="nav-link">
			<i class="fas fa-circle nav-icon"></i>
			<p>Level 1</p>
		</a>
	</li>

	<li class="nav-item menu-is-opening menu-open">
		<a href="#" class="nav-link">
			<i class="nav-icon fas fa-circle"></i>
			<p>Level 1 <i class="right fas fa-angle-left"></i></p>
		</a>
		<ul class="nav nav-treeview" style="display: block;">
			<li class="nav-item">
				<a href="#" class="nav-link">
					<i class="far fa-circle nav-icon"></i>
					<p>Level 2</p>
				</a>
			</li>
			<li class="nav-item menu-is-opening menu-open">
				<a href="#" class="nav-link">
					<i class="far fa-circle nav-icon"></i>
					<p>
						Level 2
						<i class="right fas fa-angle-left"></i>
					</p>
				</a>
				<ul class="nav nav-treeview" style="display: block;">
					<li class="nav-item">
						<a href="#" class="nav-link">
							<i class="far fa-dot-circle nav-icon"></i>
							<p>Level 3</p>
						</a>
					</li>
					<li class="nav-item">
						<a href="#" class="nav-link">
							<i class="far fa-dot-circle nav-icon"></i>
							<p>Level 3</p>
						</a>
					</li>
					<li class="nav-item">
						<a href="#" class="nav-link">
							<i class="far fa-dot-circle nav-icon"></i>
							<p>Level 3</p>
						</a>
					</li>
				</ul>
			</li>
			<li class="nav-item">
				<a href="#" class="nav-link">
					<i class="far fa-circle nav-icon"></i>
					<p>Level 2</p>
				</a>
			</li>
		</ul>
	</li>

	/**
	 * 
	 */
	

	<li class="nav-item menu-is-opening menu-open">
		<a href="#" class="nav-link">
			<i class="nav-icon fas fa-circle"></i>
			<p>
				Level 1
				<i class="right fas fa-angle-left"></i>
			</p>
		</a>
		<ul class="nav nav-treeview" style="display: block;">
			<li class="nav-item">
				<a href="#" class="nav-link">
					<i class="far fa-circle nav-icon"></i>
					<p>Level 2</p>
				</a>
			</li>
			<li class="nav-item">
				<a href="#" class="nav-link">
					<i class="far fa-circle nav-icon"></i>
					<p>
						Level 2
						<i class="right fas fa-angle-left"></i>
					</p>
				</a>
				<ul class="nav nav-treeview">
					<li class="nav-item">
						<a href="#" class="nav-link">
							<i class="far fa-dot-circle nav-icon"></i>
							<p>Level 3</p>
						</a>
					</li>
					<li class="nav-item">
						<a href="#" class="nav-link">
							<i class="far fa-dot-circle nav-icon"></i>
							<p>Level 3</p>
						</a>
					</li>
					<li class="nav-item">
						<a href="#" class="nav-link">
							<i class="far fa-dot-circle nav-icon"></i>
							<p>Level 3</p>
						</a>
					</li>
				</ul>
			</li>
			<li class="nav-item">
				<a href="#" class="nav-link">
					<i class="far fa-circle nav-icon"></i>
					<p>Level 2</p>
				</a>
			</li>
		</ul>
	</li>
@endif