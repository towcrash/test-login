<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aplicar auth:usuario a todas las rutas web
        $middleware->appendToGroup('web', 'auth:usuario');

        // Verificar en cada request que el usuario sigue activo
        $middleware->appendToGroup('web', \App\Http\Middleware\VerificarUsuarioActivo::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (
            \Illuminate\Session\TokenMismatchException $e,
            \Illuminate\Http\Request $request
        ) {
            return redirect()->route('login')
                ->withErrors(['user' => 'Tu sesión ha expirado. Por favor inicia sesión nuevamente.']);
        });
    })
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->create();