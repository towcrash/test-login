<?php

namespace App\Providers;

use App\Models\Alerta\AlertaFalla;
use App\Services\Services\LogService;
use App\Services\Services\SessionService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		/**
		 * Binds para Facades
		 */
		app()->bind('sessionService'       , SessionService::class);
		app()->bind('logService'           , LogService::class);

		/**
		 * Directivas de Blade
		 */
		Blade::directive('markError', function ($cadena) {
			return "
				<?php if (\$errors->has({$cadena})) : ?>
					is-invalid
				<?php endif; ?>";
		});

		Blade::directive('msgError', function ($cadena) {
			return "
			<?php if (\$errors->has({$cadena})) : ?>
				<span class='invalid-feedback'>
					<strong> {{ \$errors->first({$cadena}) }} </strong>
				</span>
			<?php endif; ?>";
		});
		
		// Blade::if('role', function($role) {
		// 	return auth()->user() and auth()->user()->hasRole($role);
		// });

		// Blade::if('anyrole', function(...$roles) {
		// 	return auth()->user() and auth()->user()->hasAnyRole(...$roles);
		// });

		// Blade::if('allroles', function(...$roles) {
		// 	return auth()->user() and auth()->user()->hasAllRoles(...$roles);
		// });
	}

	public function boot(): void
	{
		Paginator::useBootstrapFive();
		
		Validator::extend('existsValid', function ($attribute, $value, $parameters, $validator) {
			return DB::table($parameters[0])->where('bloqueado', 0)->where($parameters[1], $value)->exists();
		}, 'El campo :attribute no contiene un valor válido.');
	}
}
