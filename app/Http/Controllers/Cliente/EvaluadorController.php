<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente\Evaluador;
use App\Models\Cliente\Cliente;
use App\Models\Usuario\Usuario;
use Illuminate\Http\Request;
use App\Services\Facades\SessionService;

class EvaluadorController extends Controller
{
    protected string $rutaBase = 'cliente.evaluador.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);
    }

    /**
     * Listado global de evaluadores (todos los clientes)
     */
    public function index()
    {
        $evaluadores = Evaluador::with(['cliente', 'usuario'])
            ->where('bloqueado', 0)
            ->orderBy('id')
            ->get();

        return view('cliente.evaluador.index', compact('evaluadores'));
    }

    public function create()
    {
        $clientes  = Cliente::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        $usuarios  = Usuario::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        return view('cliente.evaluador.create', compact('clientes', 'usuarios'));
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
            'fecha'      => now(),
            'bloqueado'  => 0,
        ]);

        SessionService::success('Evaluador', 'Evaluador creado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function show(Evaluador $evaluador)
    {
        $evaluador->load(['cliente', 'usuario', 'evaluaciones']);
        return view('cliente.evaluador.show', compact('evaluador'));
    }

    public function edit(Evaluador $evaluador)
    {
        $clientes = Cliente::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        $usuarios = Usuario::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        return view('cliente.evaluador.edit', compact('evaluador', 'clientes', 'usuarios'));
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
        $evaluador->delete();
        SessionService::success('Evaluador', 'Evaluador eliminado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }
}