<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Usuario\Usuario;
use App\Models\Usuario\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Facades\SessionService;

class UsuarioController extends Controller
{
    protected string $rutaBase = 'usuario.usuario.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);
    }

    public function index()
    {
        $usuarios = Usuario::with('roles')->orderBy('id')->get();
        return view($this->rutaBase . 'index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Rol::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        return view($this->rutaBase . 'create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user'     => 'required|string|unique:Usuario,user',
            'password' => 'required|string|min:6',
            'nombre'   => 'required|string',
            'rut'      => 'nullable|string|unique:Usuario,rut',
            'email'    => 'nullable|email|unique:Usuario,email',
            'vigencia' => 'nullable|date',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:Rol,id',
        ], [
            'user.required'   => 'El nombre de usuario es obligatorio.',
            'user.unique'     => 'Ese nombre de usuario ya existe.',
            'password.min'    => 'La contraseña debe tener al menos 6 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        $usuario = Usuario::create($request->only('user', 'password', 'nombre', 'rut', 'email', 'vigencia'));

        if ($request->filled('roles')) {
            $pivot = collect($request->roles)->mapWithKeys(fn($id) => [
                $id => ['fecha' => now(), 'bloqueado' => 0]
            ])->all();
            $usuario->roles()->attach($pivot);
        }

        SessionService::success('Usuario', 'Usuario creado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function show(Usuario $usuario)
    {
        $usuario->load(['roles', 'clientes', 'contratistas', 'evaluadores.cliente', 'colaboradores.contratista']);
        return view($this->rutaBase .  'show', compact('usuario'));
    }

    public function edit(Usuario $usuario)
    {
        $usuario->load('roles');
        $roles         = Rol::where('bloqueado', 0)->orderBy('nombre')->pluck('nombre', 'id');
        $rolesAsignados = $usuario->roles->pluck('id')->toArray();
        return view($this->rutaBase . 'edit', compact('usuario', 'roles', 'rolesAsignados'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $request->validate([
            'user'      => 'nullable|string|unique:Usuario,user,' . $usuario->id,
            'nombre'    => 'nullable|string',
            'rut'       => 'nullable|string|unique:Usuario,rut,' . $usuario->id,
            'email'     => 'nullable|email|unique:Usuario,email,' . $usuario->id,
            'vigencia'  => 'nullable|date',
            'bloqueado' => 'nullable|boolean',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:Rol,id',
        ]);

        $data = array_filter([
            'user'      => $request->filled('user')   ? $request->user   : null,
            'nombre'    => $request->filled('nombre') ? $request->nombre : null,
            'rut'       => $request->filled('rut')    ? $request->rut    : null,
            'email'     => $request->filled('email')  ? $request->email  : null,
            'vigencia'  => $request->has('vigencia')  ? $request->vigencia : null,
            'bloqueado' => $request->has('bloqueado') ? ($request->boolean('bloqueado') ? 1 : 0) : null,
        ], fn($v) => $v !== null);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $data['password'] = $request->password;
        }

        $estabaBloqueado = $usuario->bloqueado;
        $usuario->update($data);

        if (!$estabaBloqueado && $usuario->bloqueado) {
            $this->bloquearRelaciones($usuario);
        }

        if ($request->has('roles')) {
            $roles = collect($request->roles ?? [])->mapWithKeys(fn($id) => [
                $id => ['fecha' => now(), 'bloqueado' => 0]
            ])->all();
            $usuario->roles()->sync($roles);
        }

        SessionService::success('Usuario', 'Usuario actualizado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function destroy(Usuario $usuario)
    {
        $nuevoEstado = $usuario->bloqueado ? 0 : 1;
        $mensaje = $nuevoEstado ? 'bloqueado' : 'desbloqueado';
        
        $usuario->update([
            'bloqueado' => $nuevoEstado
        ]);
        
        if ($usuario->bloqueado == 1)
        {
            $this->bloquearRelaciones($usuario);
        }

        SessionService::success('Usuario', "Usuario {$mensaje} correctamente.");
        return redirect()->route($this->rutaBase . 'index');
    }

    
    private function bloquearRelaciones(Usuario $usuario): void
    {
        \DB::table('Cliente_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        \DB::table('Contratista_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        \DB::table('Usuario_Rol')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        \DB::table('Evaluador')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        $evaluadorIds = \DB::table('Evaluador')
            ->where('Usuario_id', $usuario->id)
            ->pluck('id');

        if ($evaluadorIds->isNotEmpty()) {
            \DB::table('Evaluador_Evaluacion')
                ->whereIn('Evaluador_id', $evaluadorIds)
                ->update(['bloqueado' => 1]);
        }

        \DB::table('Colaborador')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        $colaboradorIds = \DB::table('Colaborador')
            ->where('Usuario_id', $usuario->id)
            ->pluck('id');

        if ($colaboradorIds->isNotEmpty()) {
            \DB::table('Colaborador_Evaluacion')
                ->whereIn('Colaborador_id', $colaboradorIds)
                ->update(['bloqueado' => 1]);
        }

        \DB::table('Recurso_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);
    }
}