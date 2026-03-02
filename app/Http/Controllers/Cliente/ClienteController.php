<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente\Cliente;
use App\Models\Contratista\Contratista;
use App\Models\Cliente\Evaluador;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Usuario\Usuario;
use App\Models\Usuario\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Facades\SessionService;

class ClienteController extends Controller
{
    protected string $rutaBase = 'cliente.cliente.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);
    }

    public function index(Request $request)
    {
        $usuario = Auth::guard('usuario')->user();

        if ($usuario->isSisAdmin()) {
            $query = Cliente::withCount(['contratistas', 'evaluadores', 'evaluaciones'])
                ->orderBy('id');

            if ($request->filled('buscar')) {
                $query->where('nombre', 'like', '%' . $request->buscar . '%');
            }

            $clientes = $query->paginate(20)->withQueryString();

            return view($this->rutaBase . 'index', compact('clientes'));
        }

        // Clientes donde el usuario está en Cliente_Usuario
        $queryUsuario = Cliente::withCount(['contratistas', 'evaluadores', 'evaluaciones'])
            ->whereIn('id', $usuario->clientes->pluck('id'))
            ->orderBy('id');

        if ($request->filled('buscar')) {
            $queryUsuario->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $clientesComoUsuario = $queryUsuario->paginate(20, ['*'], 'page_usuario')->withQueryString();

        // Clientes donde el usuario es evaluador
        $queryEvaluador = Cliente::withCount(['contratistas', 'evaluadores', 'evaluaciones'])
            ->whereIn('id',
                $usuario->evaluadores()->where('bloqueado', 0)->pluck('Cliente_id')
            )
            ->whereNotIn('id', $usuario->clientes->pluck('id'))
            ->orderBy('id');

        if ($request->filled('buscar')) {
            $queryEvaluador->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $clientesComoEvaluador = $queryEvaluador->paginate(20, ['*'], 'page_evaluador')->withQueryString();

        return view($this->rutaBase . 'index', compact('clientesComoUsuario', 'clientesComoEvaluador'));
    }

    public function create()
    {
        $usuarios = Usuario::where('bloqueado', 0)
            ->where(function ($q) {
                $q->whereNull('vigencia')
                ->orWhere('vigencia', '>', now());
            })
            ->orderBy('id')
            ->pluck('nombre', 'id');

        return view($this->rutaBase . 'create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string',
            'rut'        => 'required|string|unique:Cliente,rut',
            'usuarios'   => 'nullable|array',
            'usuarios.*' => 'exists:Usuario,id',
        ]);

        $cliente = Cliente::create($request->only('nombre', 'rut'));

        if ($request->filled('usuarios')) {
            $this->sincronizarUsuarios($cliente, $request->usuarios, agregarRol: true);
        }

        SessionService::success('Cliente', 'Cliente creado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'usuarios',
            'contratistas.colaboradores.usuario',
            'evaluadores.usuario',
            'evaluaciones',
        ]);

        $usuariosDisponibles = Usuario::where('bloqueado', 0)
            ->where(function ($q) {
                $q->whereNull('vigencia')
                ->orWhere('vigencia', '>', now());
            })
            ->whereNotIn('id', $cliente->usuarios->pluck('id'))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $contratistasDisponibles = Contratista::where('bloqueado', 0)
            ->whereNotIn('id', $cliente->contratistas->pluck('id'))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $evaluadoresActuales = $cliente->evaluadores->pluck('Usuario_id');
        $usuariosParaEvaluador = Usuario::where('bloqueado', 0)
            ->where(function ($q) {
                $q->whereNull('vigencia')
                ->orWhere('vigencia', '>', now());
            })
            ->whereHas('roles', fn($q) => $q->where('nombre', 'Evaluador'))
            ->whereNotIn('id', $evaluadoresActuales)
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $evaluacionesDisponibles = Evaluacion::where('bloqueado', 0)
            ->whereNotIn('id', $cliente->evaluaciones->pluck('id'))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $contratistas  = $cliente->contratistas()->paginate(10, ['*'], 'page_contratistas');
        $evaluadores   = $cliente->evaluadores()->with('usuario')->paginate(10, ['*'], 'page_evaluadores');
        $evaluaciones  = $cliente->evaluaciones()->paginate(10, ['*'], 'page_evaluaciones');
        $usuarios      = $cliente->usuarios()->paginate(10, ['*'], 'page_usuarios');

        return view($this->rutaBase .  'show', compact(
            'cliente',
            'contratistasDisponibles',
            'usuariosParaEvaluador',
            'usuariosDisponibles',
            'evaluacionesDisponibles',
            'contratistas',
            'evaluadores',
            'evaluaciones',
            'usuarios'
        ));
    }

    public function edit(Cliente $cliente)
    {
        return view($this->rutaBase .  'edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre'    => 'nullable|string',
            'rut'       => 'nullable|string|unique:Cliente,rut,' . $cliente->id,
            'bloqueado' => 'nullable|boolean',
        ]);

        $data = array_filter([
            'nombre'    => $request->filled('nombre')    ? $request->nombre : null,
            'rut'       => $request->filled('rut')       ? $request->rut    : null,
            'bloqueado' => $request->has('bloqueado')    ? ($request->boolean('bloqueado') ? 1 : 0) : null,
        ], fn($v) => $v !== null);

        $cliente->update($data);

        SessionService::success('Cliente', 'Cliente actualizado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function asignarUsuario(Request $request, Cliente $cliente)
    {
        $request->validate([
            'Usuario_id'   => 'required|array|min:1',
            'Usuario_id.*' => 'exists:Usuario,id',
        ]);

        $asignados = 0;
        $yaAsociados = 0;

        foreach ($request->Usuario_id as $uid) {
            $ya = $cliente->usuarios()->where('Usuario_id', $uid)->exists();
            if ($ya) { $yaAsociados++; continue; }

            $cliente->usuarios()->attach($uid, ['fecha' => now(), 'bloqueado' => 0]);

            $usuario = Usuario::find($uid);
            if ($usuario) $this->asegurarRolCliente($usuario);
            $asignados++;
        }

        $mensaje = "";
        if ($asignados > 0) {
            $mensaje .= $asignados === 1 ? "1 usuario asociado al cliente. " : "{$asignados} usuarios asociados al cliente. ";
        }
        if ($yaAsociados > 0) {
            $mensaje .= $yaAsociados === 1 ? "1 usuario ya estaba asociado." : "{$yaAsociados} usuarios ya estaban asociados.";
        }

        if ($asignados > 0) {
            SessionService::success('Usuario', trim($mensaje));
        } else {
            SessionService::warning('Usuario', 'Todos los usuarios seleccionados ya estaban asociados.');
        }

        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    public function desasignarUsuario(Cliente $cliente, Usuario $usuario)
    {
        $cliente->usuarios()->detach($usuario->id);
        SessionService::success('Usuario', "Usuario {$usuario->nombre} desasociado del cliente.");
        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    public function asignarContratista(Request $request, Cliente $cliente)
    {
        $request->validate([
            'Contratista_id'   => 'required|array|min:1',
            'Contratista_id.*' => 'exists:Contratista,id',
        ]);

        $asignados = 0;
        $yaAsociados = 0;

        foreach ($request->Contratista_id as $cid) {
            $ya = $cliente->contratistas()->where('Contratista_id', $cid)->exists();
            if ($ya) { $yaAsociados++; continue; }

            $cliente->contratistas()->attach($cid, [
                'Usuario_id' => Auth::guard('usuario')->id(),
                'fecha'      => now(),
                'bloqueado'  => 0,
            ]);
            $asignados++;
        }

        $mensaje = "";
        if ($asignados > 0) {
            $mensaje .= $asignados === 1 ? "1 contratista asignado. " : "{$asignados} contratistas asignados. ";
        }
        if ($yaAsociados > 0) {
            $mensaje .= $yaAsociados === 1 ? "1 contratista ya estaba asociado." : "{$yaAsociados} contratistas ya estaban asociados.";
        }

        if ($asignados > 0) {
            SessionService::success('Contratista', trim($mensaje));
        } else {
            SessionService::warning('Contratista', 'Todos los contratistas seleccionados ya estaban asociados.');
        }

        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    public function desasignarContratista(Request $request, Cliente $cliente, Contratista $contratista)
    {
        $cliente->contratistas()->detach($contratista->id);
        SessionService::success('Contratista', 'Contratista desasociado correctamente.');
        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    public function asignarEvaluador(Request $request, Cliente $cliente)
    {
        $request->validate([
            'Usuario_id'   => 'required|array|min:1',
            'Usuario_id.*' => 'exists:Usuario,id',
        ]);

        $asignados = 0;
        $yaAsociados = 0;

        foreach ($request->Usuario_id as $uid) {
            $ya = Evaluador::where('Cliente_id', $cliente->id)->where('Usuario_id', $uid)->exists();
            if ($ya) { $yaAsociados++; continue; }

            Evaluador::create(['Cliente_id' => $cliente->id, 'Usuario_id' => $uid, 'fecha' => now(), 'bloqueado' => 0]);
            $asignados++;
        }

        $mensaje = "";
        if ($asignados > 0) {
            $mensaje .= $asignados === 1 ? "1 evaluador asignado. " : "{$asignados} evaluadores asignados. ";
        }
        if ($yaAsociados > 0) {
            $mensaje .= $yaAsociados === 1 ? "1 usuario ya era evaluador." : "{$yaAsociados} usuarios ya eran evaluadores.";
        }

        if ($asignados > 0) {
            SessionService::success('Evaluador', trim($mensaje));
        } else {
            SessionService::warning('Evaluador', 'Todos los usuarios seleccionados ya eran evaluadores.');
        }

        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    public function desasignarEvaluador(Cliente $cliente, Evaluador $evaluador)
    {
        $evaluador->delete();
        SessionService::success('Evaluador', 'Evaluador removido correctamente.');
        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    private function sincronizarUsuarios(Cliente $cliente, array $usuarioIds, bool $agregarRol = false): void
    {
        $pivot = collect($usuarioIds)->mapWithKeys(fn($id) => [
            $id => ['fecha' => now(), 'bloqueado' => 0]
        ])->all();

        $cliente->usuarios()->sync($pivot);

        if ($agregarRol) {
            Usuario::whereIn('id', $usuarioIds)->each(fn($u) => $this->asegurarRolCliente($u));
        }
    }

    private function asegurarRolCliente(Usuario $usuario): void
    {
        if (!$usuario->hasRole('Cliente')) {
            $rol = Rol::where('nombre', 'Cliente')->where('bloqueado', 0)->first();
            if ($rol) {
                $usuario->roles()->attach($rol->id, ['fecha' => now(), 'bloqueado' => 0]);
            }
        }
    }

    public function asignarEvaluacion(Request $request, Cliente $cliente)
    {
        $request->validate([
            'Evaluacion_id'   => 'required|array|min:1',
            'Evaluacion_id.*' => 'exists:Evaluacion,id',
        ]);

        $asignados = 0;
        foreach ($request->Evaluacion_id as $eid) {
            $ya = $cliente->evaluaciones()->where('Evaluacion_id', $eid)->exists();
            if ($ya) continue;

            $cliente->evaluaciones()->attach($eid, [
                'Usuario_id' => Auth::guard('usuario')->id(),
                'fecha'      => now(),
                'bloqueado'  => 0,
            ]);
            $asignados++;
        }

        if ($asignados > 0) {
            SessionService::success('Evaluacion', $asignados === 1
                ? 'Evaluación asignada al cliente.'
                : "{$asignados} evaluaciones asignadas al cliente.");
        } else {
            SessionService::warning('Evaluacion', 'Las evaluaciones seleccionadas ya estaban asignadas.');
        }

        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    public function desasignarEvaluacion(Cliente $cliente, Evaluacion $evaluacion)
    {
        $cliente->evaluaciones()->detach($evaluacion->id);
        SessionService::success('Evaluacion', 'Evaluación desasociada del cliente.');
        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    public function asignarEvaluadorEvaluacion(Request $request, Cliente $cliente, Evaluacion $evaluacion)
    {
        $request->validate(['Evaluador_id' => 'required|exists:Evaluador,id']);

        $ya = $evaluacion->evaluadores()->where('Evaluador_id', $request->Evaluador_id)->exists();
        if ($ya) {
            SessionService::warning('Evaluador', 'Ese evaluador ya está asignado a esta evaluación.');
            return redirect()->route($this->rutaBase . 'show', $cliente);
        }

        $evaluacion->evaluadores()->attach($request->Evaluador_id, [
            'Usuario_id' => Auth::guard('usuario')->id(),
            'fecha'      => now(),
            'bloqueado'  => 0,
        ]);

        SessionService::success('Evaluador', 'Evaluador asignado a la evaluación correctamente.');
        return redirect()->route($this->rutaBase . 'show', $cliente);
    }

    public function desasignarEvaluadorEvaluacion(Cliente $cliente, Evaluacion $evaluacion, Evaluador $evaluador)
    {
        $evaluacion->evaluadores()->detach($evaluador->id);
        SessionService::success('Evaluador', 'Evaluador removido de la evaluación.');
        return redirect()->route($this->rutaBase . 'show', $cliente);
    }
}