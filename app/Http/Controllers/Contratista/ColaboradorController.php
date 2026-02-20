<?php

namespace App\Http\Controllers\Contratista;

use App\Http\Controllers\Controller;
use App\Models\Contratista\Colaborador;
use App\Models\Contratista\Contratista;
use App\Models\Usuario\Usuario;
use Illuminate\Http\Request;
use App\Services\Facades\SessionService;

class ColaboradorController extends Controller
{
    protected string $rutaBase = 'contratista.colaborador.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);
    }

    public function index()
    {
        $colaboradores = Colaborador::with(['contratista', 'usuario'])
            ->where('bloqueado', 0)
            ->orderBy('id')
            ->get();

        return view($this->rutaBase . 'index', compact('colaboradores'));
    }

    public function create()
    {
        $contratistas = Contratista::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        $usuarios     = Usuario::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        return view($this->rutaBase . 'create', compact('contratistas', 'usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Contratista_id' => 'required|exists:Contratista,id',
            'Usuario_id'     => 'required|exists:Usuario,id',
        ]);

        $ya = Colaborador::where('Contratista_id', $request->Contratista_id)
            ->where('Usuario_id', $request->Usuario_id)
            ->exists();

        if ($ya) {
            SessionService::warning('Colaborador', 'Ese usuario ya es colaborador de ese contratista.');
            return redirect()->route($this->rutaBase . 'index');
        }

        Colaborador::create([
            'Contratista_id' => $request->Contratista_id,
            'Usuario_id'     => $request->Usuario_id,
            'fecha'          => now(),
            'bloqueado'      => 0,
        ]);

        SessionService::success('Colaborador', 'Colaborador creado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function show(Colaborador $colaborador)
    {
        $colaborador->load(['contratista', 'usuario', 'evaluaciones']);
        return view($this->rutaBase . 'show', compact('colaborador'));
    }

    public function edit(Colaborador $colaborador)
    {
        $contratistas = Contratista::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        $usuarios     = Usuario::where('bloqueado', 0)->orderBy('id')->pluck('nombre', 'id');
        return view($this->rutaBase . 'edit', compact('colaborador', 'contratistas', 'usuarios'));
    }

    public function update(Request $request, Colaborador $colaborador)
    {
        $request->validate([
            'bloqueado' => 'nullable|boolean',
        ]);

        $colaborador->update([
            'bloqueado' => $request->boolean('bloqueado') ? 1 : 0,
        ]);

        SessionService::success('Colaborador', 'Colaborador actualizado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function destroy(Colaborador $colaborador)
    {
        $nuevoEstado = $colaborador->bloqueado ? 0 : 1;
        $mensaje = $nuevoEstado ? 'bloqueado' : 'desbloqueado';
        
        $colaborador->update([
            'bloqueado' => $nuevoEstado
        ]);
        
        SessionService::success('Colaborador', "Colaborador {$mensaje} correctamente.");
        return redirect()->route($this->rutaBase . 'index');
    }
}