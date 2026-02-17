<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Muestra el formulario de login.
     * Si ya está autenticado, redirige al dashboard.
     */
    public function showLoginForm()
    {
        if (Auth::guard('usuario')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Procesa el intento de login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user'     => 'required|string',
            'password' => 'required|string',
        ], [
            'user.required'     => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Buscar usuario para dar mensajes específicos de error
        $usuario = Usuario::where('user', $request->user)->first();

        if (!$usuario) {
            throw ValidationException::withMessages([
                'user' => ['Estas credenciales no coinciden con nuestros registros.'],
            ]);
        }

        if ($usuario->bloqueado) {
            throw ValidationException::withMessages([
                'user' => ['Este usuario se encuentra bloqueado. Contacte al administrador.'],
            ]);
        }

        if ($usuario->vigencia && $usuario->vigencia->isPast()) {
            throw ValidationException::withMessages([
                'user' => ['Su cuenta ha expirado. Contacte al administrador.'],
            ]);
        }

        // Intentar autenticar
        $credentials = [
            'user'      => $request->user,
            'password'  => $request->password,
            'bloqueado' => 0,
        ];

        if (!Auth::guard('usuario')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'user' => ['Estas credenciales no coinciden con nuestros registros.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Cierra la sesión.
     */
    public function logout(Request $request)
    {
        Auth::guard('usuario')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}