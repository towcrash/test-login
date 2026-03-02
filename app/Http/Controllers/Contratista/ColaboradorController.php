<?php

namespace App\Http\Controllers\Contratista;

use App\Http\Controllers\Controller;
use App\Models\Contratista\Colaborador;
use App\Models\Contratista\Contratista;
use App\Models\Usuario\Usuario;
use App\Models\Evaluacion\Evaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Facades\SessionService;

class ColaboradorController extends Controller
{
    protected string $rutaBase = 'contratista.colaborador.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);
    }

    public function index(Request $request)
    {
        $usuario = Auth::user();

        if ($usuario->hasRole('Colaborador') && !$usuario->hasAnyRole(['SisAdmin', 'Cliente', 'Contratista', 'Evaluador'])) {
            abort(403, 'No tiene permiso para acceder a este apartado.');
        }

        $buscar = $request->filled('buscar') ? $request->buscar : null;

        // SisAdmin: todos los colaboradores agrupados por contratista
        if ($usuario->isSisAdmin()) {
            $query = Contratista::with(['colaboradores.usuario'])
                ->where('bloqueado', 0)
                ->orderBy('nombre');

            if ($buscar) {
                $query->whereHas('colaboradores.usuario', fn($q) => $q->where('nombre', 'like', "%{$buscar}%"));
            }

            $seccionContratista = $query->get()
                ->map(function ($contratista) use ($buscar) {
                    $cols = $contratista->colaboradores->sortBy('id');
                    if ($buscar) {
                        $cols = $cols->filter(fn($c) => str_contains(strtolower($c->usuario->nombre ?? ''), strtolower($buscar)));
                    }
                    $contratista->colaboradoresFiltrados = $cols;
                    return $contratista;
                })
                ->filter(fn($c) => $c->colaboradoresFiltrados->isNotEmpty())
                ->values();

            return view($this->rutaBase . 'index', [
                'seccionContratista' => $seccionContratista,
                'seccionEvaluador'   => collect(),
                'buscar'             => $buscar,
            ]);
        }

        $seccionContratista = collect();
        $seccionEvaluador   = collect();

        if ($usuario->hasRole('Cliente')) {
            $clienteIds = $usuario->clientes()->pluck('Cliente.id');

            $contratistasIds = DB::table('Cliente_Contratista')
                ->whereIn('Cliente_id', $clienteIds)
                ->where('bloqueado', 0)
                ->pluck('Contratista_id')
                ->unique();

            $seccionContratista = Contratista::with(['colaboradores' => fn($q) => $q->where('Colaborador.bloqueado', 0), 'colaboradores.usuario'])
                ->whereIn('id', $contratistasIds)
                ->where('bloqueado', 0)
                ->orderBy('nombre')
                ->get()
                ->map(function ($contratista) use ($buscar) {
                    $cols = $contratista->colaboradores->sortBy('id');
                    if ($buscar) {
                        $cols = $cols->filter(fn($c) => str_contains(strtolower($c->usuario->nombre ?? ''), strtolower($buscar)));
                    }
                    $contratista->colaboradoresFiltrados = $cols;
                    return $contratista;
                })
                ->filter(fn($c) => $c->colaboradoresFiltrados->isNotEmpty())
                ->values();
        }

        if ($usuario->hasRole('Contratista')) {
            $contratistasIds = $usuario->contratistas()->pluck('Contratista.id');

            $porContratista = Contratista::with(['colaboradores' => fn($q) => $q->where('Colaborador.bloqueado', 0), 'colaboradores.usuario'])
                ->whereIn('id', $contratistasIds)
                ->where('bloqueado', 0)
                ->orderBy('nombre')
                ->get()
                ->map(function ($contratista) use ($buscar) {
                    $cols = $contratista->colaboradores->sortBy('id');
                    if ($buscar) {
                        $cols = $cols->filter(fn($c) => str_contains(strtolower($c->usuario->nombre ?? ''), strtolower($buscar)));
                    }
                    $contratista->colaboradoresFiltrados = $cols;
                    return $contratista;
                })
                ->filter(fn($c) => $c->colaboradoresFiltrados->isNotEmpty())
                ->values();

            $existentes         = $seccionContratista->pluck('id')->toArray();
            $nuevos             = $porContratista->filter(fn($c) => !in_array($c->id, $existentes));
            $seccionContratista = $seccionContratista->concat($nuevos)->sortBy('nombre')->values();
        }

        if ($usuario->hasRole('Evaluador')) {
            $evaluadorIds = DB::table('Evaluador')
                ->where('Usuario_id', $usuario->id)
                ->where('bloqueado', 0)
                ->pluck('id');

            $evaluacionIds = DB::table('Evaluador_Evaluacion')
                ->whereIn('Evaluador_id', $evaluadorIds)
                ->where('bloqueado', 0)
                ->pluck('Evaluacion_id')
                ->unique();

            $seccionEvaluador = Evaluacion::with([
                    'colaboradores' => fn($q) => $q->where('Colaborador.bloqueado', 0),
                    'colaboradores.usuario',
                    'colaboradores.contratista',
                ])
                ->whereIn('id', $evaluacionIds)
                ->where('bloqueado', 0)
                ->orderBy('nombre')
                ->get()
                ->map(function ($evaluacion) use ($buscar) {
                    $cols = $evaluacion->colaboradores->sortBy('id');
                    if ($buscar) {
                        $cols = $cols->filter(fn($c) => str_contains(strtolower($c->usuario->nombre ?? ''), strtolower($buscar)));
                    }
                    $evaluacion->colaboradoresFiltrados = $cols;
                    return $evaluacion;
                })
                ->filter(fn($e) => $e->colaboradoresFiltrados->isNotEmpty())
                ->values();
        }

        return view($this->rutaBase . 'index', compact('seccionContratista', 'seccionEvaluador', 'buscar'));
    }

    public function create()
    {
        $contratistas = Contratista::where('bloqueado', 0)->orderBy('nombre')->pluck('nombre', 'id');

        $usuarios = Usuario::where('bloqueado', 0)
            ->where(function ($q) {
                $q->whereNull('vigencia')
                  ->orWhere('vigencia', '>', now());
            })
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

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
        $usuario = Auth::user();

        if (!$usuario->isSisAdmin()) {
            if ($colaborador->bloqueado) abort(403, 'No tiene permiso para ver este colaborador.');

            $tieneAcceso = false;

            if (!$tieneAcceso && $usuario->hasRole('Cliente')) {
                $clienteIds = $usuario->clientes()->pluck('Cliente.id');
                $tieneAcceso = DB::table('Cliente_Contratista')
                    ->whereIn('Cliente_id', $clienteIds)
                    ->where('Contratista_id', $colaborador->Contratista_id)
                    ->where('bloqueado', 0)
                    ->exists();
            }

            if (!$tieneAcceso && $usuario->hasRole('Contratista')) {
                $tieneAcceso = $usuario->contratistas()->where('Contratista.id', $colaborador->Contratista_id)->exists();
            }

            if (!$tieneAcceso && $usuario->hasRole('Evaluador')) {
                $evaluadorIds = DB::table('Evaluador')
                    ->where('Usuario_id', $usuario->id)
                    ->where('bloqueado', 0)
                    ->pluck('id');

                $evaluacionIds = DB::table('Evaluador_Evaluacion')
                    ->whereIn('Evaluador_id', $evaluadorIds)
                    ->where('bloqueado', 0)
                    ->pluck('Evaluacion_id');

                $tieneAcceso = DB::table('Colaborador_Evaluacion')
                    ->whereIn('Evaluacion_id', $evaluacionIds)
                    ->where('Colaborador_id', $colaborador->id)
                    ->where('bloqueado', 0)
                    ->exists();
            }

            if (!$tieneAcceso) abort(403, 'No tiene permiso para ver este colaborador.');
        }

        $colaborador->load(['contratista', 'usuario']);
        $evaluaciones = $colaborador->evaluaciones()->paginate(10)->withQueryString();

        return view($this->rutaBase . 'show', compact('colaborador', 'evaluaciones'));
    }

    public function edit(Colaborador $colaborador)
    {
        $contratistas = Contratista::where('bloqueado', 0)->orderBy('nombre')->pluck('nombre', 'id');

        $usuarios = Usuario::where('bloqueado', 0)
            ->where(function ($q) {
                $q->whereNull('vigencia')
                  ->orWhere('vigencia', '>', now());
            })
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        return view($this->rutaBase . 'edit', compact('colaborador', 'contratistas', 'usuarios'));
    }

    public function update(Request $request, Colaborador $colaborador)
    {
        $request->validate([
            'Contratista_id' => 'required|exists:Contratista,id',
            'Usuario_id'     => 'required|exists:Usuario,id',
            'bloqueado'      => 'nullable|boolean',
        ]);

        $contratistaId = $request->Contratista_id;
        $usuarioId     = $request->Usuario_id;

        if ($contratistaId != $colaborador->Contratista_id || $usuarioId != $colaborador->Usuario_id) {
            $ya = Colaborador::where('Contratista_id', $contratistaId)
                ->where('Usuario_id', $usuarioId)
                ->where('id', '!=', $colaborador->id)
                ->exists();

            if ($ya) {
                SessionService::warning('Colaborador', 'Ese usuario ya es colaborador de ese contratista.');
                return redirect()->route($this->rutaBase . 'edit', $colaborador);
            }
        }

        $colaborador->update([
            'Contratista_id' => $contratistaId,
            'Usuario_id'     => $usuarioId,
            'bloqueado'      => $request->boolean('bloqueado') ? 1 : 0,
        ]);

        SessionService::success('Colaborador', 'Colaborador actualizado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function destroy(Colaborador $colaborador)
    {
        $nuevoEstado = $colaborador->bloqueado ? 0 : 1;
        $mensaje     = $nuevoEstado ? 'bloqueado' : 'desbloqueado';

        $colaborador->update(['bloqueado' => $nuevoEstado]);

        SessionService::success('Colaborador', "Colaborador {$mensaje} correctamente.");
        return redirect()->route($this->rutaBase . 'index');
    }
}