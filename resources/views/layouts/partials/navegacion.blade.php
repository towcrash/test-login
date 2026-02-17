<style>
	.menu-open > .nav-link {
		background-color: #40464B !important;
	}
	.nav-header {
		/*margin: 8px 0px 0px 0px;*/
	}
	.nav-link {
		/*margin: -4px 12px !important;*/
	}
</style>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
	<a href="#" class="brand-link" style="background-color: #FFF;">
		<img class='brand-logo' src='{{ Storage::disk('logos')->url('EngineeringPR.jpeg') }}' style='width:100%;'>
	</a>

	<div class="sidebar">
		<nav class="mt-3">
			<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
				<li class="nav-header">MENU PRINCIPAL</li>
				@foreach (MenuService::getMenusNavegacion() as $menu)
					@include('layouts.partials.navegacionMenu')
				@endforeach
			</ul>
		</nav>
	</div>
</aside>
