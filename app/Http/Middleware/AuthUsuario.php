<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthUsuario
{
    /**
     * Verifica que el usuario esté autenticado con el guard 'usuario'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('usuario')->check()) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder.');
        }

        return $next($request);
    }
}