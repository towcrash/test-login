<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente\Evaluador;
use App\Models\Cliente\Cliente;
use App\Models\Usuario\Usuario;
use Illuminate\Http\Request;
use App\Services\Facades\SessionService;
use Illuminate\Support\Facades\Auth;


class EvaluadorController extends Controller
{
    protected string $rutaBase = 'cliente.evaluador.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);

        $this->middleware(function ($request, $next) {
            if (!Auth::guard('usuario')->check() || !Auth::guard('usuario')->user()->isSisAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Listado global de evaluadores
     */
    public function index()
    {
        $evaluadores = Evaluador::with(['cliente', 'usuario'])
            ->orderBy('id')
            ->get();

        return view($this->rutaBase . 'index', compact('evaluadores'));
    }

    public function create()
    {
        $clientes  = Cliente::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        
        $usuarios = Usuario::where('bloqueado', 0)
        ->where(function ($q) {
            $q->whereNull('vigencia')
              ->orWhere('vigencia', '>', now());
        })
        ->whereHas('roles', fn($q) => $q->where('nombre', 'Evaluador'))
        ->orderBy('nombre')
        ->pluck('nombre', 'id');

        return view($this->rutaBase . 'create', compact('clientes', 'usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Cliente_id'  => 'required|exists:Cliente,id',
            'Usuario_id'  => 'required|exists:Usuario,id',
        ]);

        $ya = Evaluador::where('Cliente_id', $request->Cliente_id)
            ->where('Usuario_id', $request->Usuario_id)
            ->exists();

        if ($ya) {
            SessionService::warning('Evaluador', 'Ese usuario ya es evaluador de ese cliente.');
            return redirect()->route($this->rutaBase . 'index');
        }

        Evaluador::create([
            'Cliente_id' => $request->Cliente_id,
            'Usuario_id' => $request->Usuario_id,
        ]);

        SessionService::success('Evaluador', 'Evaluador creado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function show(Evaluador $evaluador)
    {
        $evaluador->load([
            'cliente',
            'usuario',
            'evaluaciones',
        ]);

        $todasInstancias = Evaluador::with(['cliente', 'evaluaciones'])
            ->where('Usuario_id', $evaluador->Usuario_id)
            ->get();

        return view($this->rutaBase . 'show', compact('evaluador', 'todasInstancias'));
    }

    public function edit(Evaluador $evaluador)
    {
        $clientes = Cliente::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        
        $usuarios = Usuario::where('bloqueado', 0)
        ->where(function ($q) {
            $q->whereNull('vigencia')
              ->orWhere('vigencia', '>', now());
        })
        ->whereHas('roles', fn($q) => $q->where('nombre', 'Evaluador'))
        ->orderBy('nombre')
        ->pluck('nombre', 'id');

        return view($this->rutaBase . 'edit', compact('evaluador', 'clientes', 'usuarios'));
    }

    public function update(Request $request, Evaluador $evaluador)
    {
        $request->validate([
            'bloqueado' => 'nullable|boolean',
        ]);

        $evaluador->update([
            'bloqueado' => $request->boolean('bloqueado') ? 1 : 0,
        ]);

        SessionService::success('Evaluador', 'Evaluador actualizado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function destroy(Evaluador $evaluador)
    {
        $nuevoEstado = $evaluador->bloqueado ? 0 : 1;
        $mensaje = $nuevoEstado ? 'bloqueado' : 'desbloqueado';
        
        $evaluador->update([
            'bloqueado' => $nuevoEstado
        ]);
        
        SessionService::success('Evaluador', "Evaluador {$mensaje} correctamente.");
        return redirect()->route($this->rutaBase . 'index');
    }
}