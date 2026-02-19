<?php

namespace App\Http\Controllers\Contratista;

use App\Http\Controllers\Controller;
use App\Models\Contratista\Contratista;
use App\Models\Contratista\Colaborador;
use App\Models\Usuario\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Facades\SessionService;

class ContratistaController extends Controller
{
    protected string $rutaBase = 'contratista.contratista.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);
    }

    public function index()
    {
        $contratistas = Contratista::withCount(['colaboradores', 'clientes', 'evaluaciones'])
            ->orderBy('id')
            ->get();
        return view('contratista.contratista.index', compact('contratistas'));
    }

    public function create()
    {
        return view('contratista.contratista.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'rut'    => 'required|string|unique:Contratista,rut',
        ]);

        Contratista::create($request->only('nombre', 'rut'));

        SessionService::success('Contratista', 'Contratista creado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function show(Contratista $contratista)
    {
        $contratista->load([
            'clientes',
            'colaboradores.usuario',
            'evaluaciones',
        ]);

        $colaboradoresActuales = $contratista->colaboradores->pluck('Usuario_id');
        $usuariosDisponibles = Usuario::where('bloqueado', 0)
            ->whereNotIn('id', $colaboradoresActuales)
            ->orderBy('id')
            ->pluck('nombre', 'id');

        return view('contratista.contratista.show', compact('contratista', 'usuariosDisponibles'));
    }

    public function edit(Contratista $contratista)
    {
        return view('contratista.contratista.edit', compact('contratista'));
    }

    public function update(Request $request, Contratista $contratista)
    {
        $request->validate([
            'nombre'    => 'required|string',
            'rut'       => 'required|string|unique:Contratista,rut,' . $contratista->id,
            'bloqueado' => 'nullable|boolean',
        ]);

        $contratista->update([
            'nombre'    => $request->nombre,
            'rut'       => $request->rut,
            'bloqueado' => $request->boolean('bloqueado') ? 1 : 0,
        ]);

        SessionService::success('Contratista', 'Contratista actualizado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function destroy(Contratista $contratista)
    {
        if ($contratista->colaboradores()->count() > 0) {
            SessionService::delete('Contratista');
            return redirect()->route($this->rutaBase . 'index');
        }

        $contratista->delete();
        SessionService::success('Contratista', 'Contratista eliminado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    // ── Gestión de Colaboradores ───────────────────────────────────────
    public function asignarColaborador(Request $request, Contratista $contratista)
    {
        $request->validate([
            'Usuario_id' => 'required|exists:Usuario,id',
        ]);

        $ya = Colaborador::where('Contratista_id', $contratista->id)
            ->where('Usuario_id', $request->Usuario_id)
            ->exists();

        if ($ya) {
            SessionService::warning('Colaborador', 'Ese usuario ya es colaborador de este contratista.');
            return redirect()->route($this->rutaBase . 'show', $contratista);
        }

        Colaborador::create([
            'Contratista_id' => $contratista->id,
            'Usuario_id'     => $request->Usuario_id,
        ]);

        SessionService::success('Colaborador', 'Colaborador asignado correctamente.');
        return redirect()->route($this->rutaBase . 'show', $contratista);
    }

    public function desasignarColaborador(Contratista $contratista, Colaborador $colaborador)
    {
        $colaborador->delete();
        SessionService::success('Colaborador', 'Colaborador removido correctamente.');
        return redirect()->route($this->rutaBase . 'show', $contratista);
    }
}