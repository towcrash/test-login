<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Middleware que verifica en cada request que el usuario autenticado
 * Si ya no está activo, lo desloguea y redirige al login con mensaje.
 */
class VerificarUsuarioActivo
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('usuario');

        if ($guard->check()) {
            $usuario = $guard->user();

            // Verificar si fue bloqueado después de autenticarse
            if ($usuario->bloqueado) {
                $guard->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['user' => 'Tu cuenta ha sido bloqueada. Contacta al administrador.']);
            }

            // Verificar si la vigencia expiró después de autenticarse
            if ($usuario->vigencia && Carbon::parse($usuario->vigencia)->isPast()) {
                $guard->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['user' => 'Tu cuenta ha expirado. Contacta al administrador.']);
            }
        }

        return $next($request);
    }
}