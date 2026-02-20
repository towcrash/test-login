<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use App\Services\Services\SessionService as SessionServiceImpl;
use App\Services\Services\LogService as LogServiceImpl;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('sessionService', function () {
            return new SessionServiceImpl();
        });

        $this->app->singleton('logService', function () {
            return new LogServiceImpl();
        });
    }

    public function boot(): void
    {
        Paginator::useBootstrapFour();
        
        Blade::directive('sisadmin', function () {
            return "<?php if(Auth::guard('usuario')->check() && Auth::guard('usuario')->user()->isSisAdmin()): ?>";
        });
        Blade::directive('endsisadmin', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('role', function ($expression) {
            return "<?php if(Auth::guard('usuario')->check() && Auth::guard('usuario')->user()->hasRole($expression)): ?>";
        });
        Blade::directive('endrole', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('anyrole', function ($expression) {
            return "<?php if(Auth::guard('usuario')->check() && Auth::guard('usuario')->user()->hasAnyRole([$expression])): ?>";
        });
        Blade::directive('endanyrole', function () {
            return '<?php endif; ?>';
        });
    }
}