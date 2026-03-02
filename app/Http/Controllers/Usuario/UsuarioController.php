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

    public function index(Request $request)
    {
        $query = Usuario::with('roles')->orderBy('id');

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $usuarios = $query->paginate(20)->withQueryString();

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
        return view($this->rutaBase . 'show', compact('usuario'));
    }

    public function edit(Usuario $usuario)
    {
        $usuario->load('roles');
        $roles          = Rol::where('bloqueado', 0)->orderBy('nombre')->pluck('nombre', 'id');
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

        $data = [];

        if ($request->filled('user'))    $data['user']    = $request->user;
        if ($request->filled('nombre'))  $data['nombre']  = $request->nombre;
        if ($request->filled('rut'))     $data['rut']     = $request->rut;
        if ($request->filled('email'))   $data['email']   = $request->email;
        if ($request->has('vigencia'))   $data['vigencia'] = $request->vigencia ?: null;
        if ($request->has('bloqueado'))  $data['bloqueado'] = $request->boolean('bloqueado') ? 1 : 0;

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $data['password'] = $request->password;
        }

        if (!empty($data)) {
            $usuario->update($data);
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

        SessionService::success('Usuario', "Usuario {$mensaje} correctamente.");
        return redirect()->route($this->rutaBase . 'index');
    }
}