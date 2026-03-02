<?php

namespace App\Http\Controllers\Evaluacion;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Cliente\Cliente;
use App\Models\Recurso\TipoRecurso;
use App\Models\Contratista\Contratista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Facades\SessionService;

class EvaluacionController extends Controller
{
    protected string $rutaBase = 'evaluacion.evaluacion.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);
    }

    public function index(Request $request)
    {
        $usuario = Auth::user();

        if ($usuario->isSisAdmin()) {
            $query = Evaluacion::orderBy('nombre');

            if ($request->filled('buscar')) {
                $query->where('nombre', 'like', '%' . $request->buscar . '%');
            }

            $evaluaciones = $query->paginate(20)->withQueryString();

            return view($this->rutaBase . 'index', [
                'esAdmin'      => true,
                'secciones'    => collect(),
                'evaluaciones' => $evaluaciones,
            ]);
        }

        $buscar   = $request->filled('buscar') ? $request->buscar : null;
        $secciones = collect();

        if ($usuario->hasRole('Cliente')) {
            $clientes = $usuario->clientes()->orderBy('nombre')->get();

            foreach ($clientes as $cliente) {
                $query = Evaluacion::whereHas('clientes', fn($q) => $q->where('Cliente.id', $cliente->id))
                    ->where('bloqueado', 0)
                    ->orderBy('nombre');

                if ($buscar) $query->where('nombre', 'like', "%{$buscar}%");

                $evaluaciones = $query->get();

                if ($evaluaciones->isNotEmpty()) {
                    $secciones->push(['titulo' => $cliente->nombre, 'subtitulo' => 'Cliente', 'evaluaciones' => $evaluaciones]);
                }
            }
        }

        if ($usuario->hasRole('Contratista')) {
            $contratistas = $usuario->contratistas()->orderBy('nombre')->get();

            foreach ($contratistas as $contratista) {
                $query = Evaluacion::whereHas('contratistas', fn($q) => $q->where('Contratista.id', $contratista->id))
                    ->where('bloqueado', 0)
                    ->orderBy('nombre');

                if ($buscar) $query->where('nombre', 'like', "%{$buscar}%");

                $evaluaciones = $query->get();

                if ($evaluaciones->isNotEmpty()) {
                    $secciones->push(['titulo' => $contratista->nombre, 'subtitulo' => 'Contratista', 'evaluaciones' => $evaluaciones]);
                }
            }
        }

        if ($usuario->hasRole('Evaluador')) {
            $evaluadores = DB::table('Evaluador')
                ->where('Usuario_id', $usuario->id)
                ->where('bloqueado', 0)
                ->get();

            foreach ($evaluadores as $evaluador) {
                $cliente = Cliente::find($evaluador->Cliente_id);

                $evaluacionIds = DB::table('Evaluador_Evaluacion')
                    ->where('Evaluador_id', $evaluador->id)
                    ->where('bloqueado', 0)
                    ->pluck('Evaluacion_id');

                $query = Evaluacion::whereIn('id', $evaluacionIds)->where('bloqueado', 0)->orderBy('nombre');
                if ($buscar) $query->where('nombre', 'like', "%{$buscar}%");
                $evaluaciones = $query->get();

                if ($evaluaciones->isNotEmpty()) {
                    $secciones->push([
                        'titulo'       => $cliente ? $cliente->nombre : "Cliente #{$evaluador->Cliente_id}",
                        'subtitulo'    => 'Evaluador',
                        'evaluaciones' => $evaluaciones,
                    ]);
                }
            }
        }

        if ($usuario->hasRole('Colaborador')) {
            $colaboradores = DB::table('Colaborador')
                ->where('Usuario_id', $usuario->id)
                ->where('bloqueado', 0)
                ->get();

            foreach ($colaboradores as $colaborador) {
                $contratista = Contratista::find($colaborador->Contratista_id);

                // IDs por relación directa Colaborador_Evaluacion
                $idsPivot = DB::table('Colaborador_Evaluacion')
                    ->where('Colaborador_id', $colaborador->id)
                    ->where('bloqueado', 0)
                    ->pluck('Evaluacion_id');

                // IDs por Aplicacion
                $idsAplicacion = DB::table('Aplicacion')
                    ->where('Colaborador_id', $colaborador->id)
                    ->where('bloqueado', 0)
                    ->pluck('Evaluacion_id');

                $evaluacionIds = $idsPivot->merge($idsAplicacion)->unique();

                $query = Evaluacion::whereIn('id', $evaluacionIds)->where('bloqueado', 0)->orderBy('nombre');
                if ($buscar) $query->where('nombre', 'like', "%{$buscar}%");
                $evaluaciones = $query->get();

                if ($evaluaciones->isNotEmpty()) {
                    $secciones->push([
                        'titulo'       => $contratista ? $contratista->nombre : "Contratista #{$colaborador->Contratista_id}",
                        'subtitulo'    => 'Colaborador',
                        'evaluaciones' => $evaluaciones,
                    ]);
                }
            }
        }

        return view($this->rutaBase . 'index', [
            'esAdmin'      => false,
            'secciones'    => $secciones,
            'evaluaciones' => collect(),
            'buscar'       => $buscar,
        ]);
    }

    public function create()
    {
        return view($this->rutaBase . 'create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'sid'         => 'nullable|string|max:255',
            'byEvaluador' => 'nullable|boolean',
            'permanent'   => 'nullable|boolean',
        ]);

        Evaluacion::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'sid'         => $request->sid,
            'byEvaluador' => $request->boolean('byEvaluador') ? 1 : 0,
            'permanent'   => $request->boolean('permanent') ? 1 : 0,
            'fecha'       => now(),
            'bloqueado'   => 0,
        ]);

        SessionService::success('Evaluacion', 'Evaluación creada correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function show(Evaluacion $evaluacion)
    {
        $this->autorizarAcceso($evaluacion);

        $usuario    = Auth::user();
        $esSisAdmin = $usuario->isSisAdmin();

        $evaluacion->load([
            'recursos'           => fn($q) => $q->orderBy('id'),
            'recursos.tipoRecurso',
            'recursos.documento.disco',
        ]);

        $esColaboradorDeEsta  = false;
        $tokenEncuesta        = null;
        if (!$esSisAdmin && $usuario->hasRole('Colaborador')) {
            $colaboradorIds = DB::table('Colaborador')
                ->where('Usuario_id', $usuario->id)
                ->where('bloqueado', 0)
                ->pluck('id');

            $pivotRow = DB::table('Colaborador_Evaluacion')
                ->whereIn('Colaborador_id', $colaboradorIds)
                ->where('Evaluacion_id', $evaluacion->id)
                ->where('bloqueado', 0)
                ->first();

            $porPivot = !is_null($pivotRow);

            $aplicacionRow = DB::table('Aplicacion')
                ->whereIn('Colaborador_id', $colaboradorIds)
                ->where('Evaluacion_id', $evaluacion->id)
                ->where('bloqueado', 0)
                ->whereNotNull('Evaluador_id')
                ->whereNotNull('token')
                ->whereNull('submitdate')           
                ->orderByDesc('fecha')
                ->first();

            $porAplicacion = !is_null($aplicacionRow);

            $esColaboradorDeEsta = $porPivot || $porAplicacion
                || DB::table('Aplicacion')
                    ->whereIn('Colaborador_id', $colaboradorIds)
                    ->where('Evaluacion_id', $evaluacion->id)
                    ->where('bloqueado', 0)
                    ->exists();

            if ($porPivot) {
                $tokenEncuesta = $pivotRow->token;
            } elseif ($porAplicacion) {
                $tokenEncuesta = $aplicacionRow->token;
            }
        }
        $preguntas = (!$esColaboradorDeEsta)
            ? $evaluacion->preguntas()
                ->when(!$esSisAdmin, fn($q) => $q->where('bloqueado', 0))
                ->orderBy('id')
                ->with(['alternativas' => fn($q) => $q->when(!$esSisAdmin, fn($q) => $q->where('bloqueado', 0))->orderBy('id')])
                ->paginate(10, ['*'], 'page_preguntas')
                ->withQueryString()
            : collect();

        $tiposRecurso = $esSisAdmin
            ? TipoRecurso::where('bloqueado', 0)->orderBy('nombre')->get()
            : collect();

        $recursosVisibles = $esSisAdmin
            ? $evaluacion->recursos
            : $evaluacion->recursos->where('bloqueado', 0);

        return view($this->rutaBase . 'show', compact('evaluacion', 'tiposRecurso', 'preguntas', 'esColaboradorDeEsta', 'tokenEncuesta', 'recursosVisibles'));
    }

    public function edit(Evaluacion $evaluacion)
    {
        $this->autorizarEdicion($evaluacion);
        return view($this->rutaBase . 'edit', compact('evaluacion'));
    }

    public function update(Request $request, Evaluacion $evaluacion)
    {
        $this->autorizarEdicion($evaluacion);

        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'sid'         => 'nullable|string|max:255',
            'byEvaluador' => 'nullable|boolean',
            'permanent'   => 'nullable|boolean',
            'bloqueado'   => 'nullable|boolean',
        ]);

        $evaluacion->update([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'sid'         => $request->sid,
            'byEvaluador' => $request->boolean('byEvaluador') ? 1 : 0,
            'permanent'   => $request->boolean('permanent') ? 1 : 0,
            'bloqueado'   => $request->boolean('bloqueado') ? 1 : 0,
        ]);

        SessionService::success('Evaluacion', 'Evaluación actualizada correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function destroy(Evaluacion $evaluacion)
    {
        if (!Auth::user()->isSisAdmin()) abort(403);

        $nuevoEstado = $evaluacion->bloqueado ? 0 : 1;
        $mensaje     = $nuevoEstado ? 'bloqueada' : 'desbloqueada';

        $evaluacion->update(['bloqueado' => $nuevoEstado]);

        SessionService::success('Evaluacion', "Evaluación {$mensaje} correctamente.");
        return redirect()->route($this->rutaBase . 'index');
    }

    private function autorizarAcceso(Evaluacion $evaluacion): void
    {
        $usuario = Auth::user();
        if ($usuario->isSisAdmin()) return;
        if ($evaluacion->bloqueado) abort(403);
        if ($this->usuarioTieneAcceso($usuario, $evaluacion)) return;
        abort(403, 'No tiene permiso para ver esta evaluación.');
    }

    private function autorizarEdicion(Evaluacion $evaluacion): void
    {
        if (!Auth::user()->isSisAdmin()) abort(403, 'No tiene permiso para editar esta evaluación.');
    }

    private function usuarioTieneAcceso($usuario, Evaluacion $evaluacion): bool
    {
        if ($usuario->hasRole('Cliente')) {
            $clienteIds = $usuario->clientes()->pluck('Cliente.id');
            if (DB::table('Cliente_Evaluacion')->whereIn('Cliente_id', $clienteIds)->where('Evaluacion_id', $evaluacion->id)->where('bloqueado', 0)->exists()) return true;
        }

        if ($usuario->hasRole('Contratista')) {
            $contratistaIds = $usuario->contratistas()->pluck('Contratista.id');
            if (DB::table('Contratista_Evaluacion')->whereIn('Contratista_id', $contratistaIds)->where('Evaluacion_id', $evaluacion->id)->where('bloqueado', 0)->exists()) return true;
        }

        if ($usuario->hasRole('Evaluador')) {
            $evaluadorIds = DB::table('Evaluador')->where('Usuario_id', $usuario->id)->where('bloqueado', 0)->pluck('id');
            if (DB::table('Evaluador_Evaluacion')->whereIn('Evaluador_id', $evaluadorIds)->where('Evaluacion_id', $evaluacion->id)->where('bloqueado', 0)->exists()) return true;
        }

        if ($usuario->hasRole('Colaborador')) {
            $colaboradorIds = DB::table('Colaborador')->where('Usuario_id', $usuario->id)->where('bloqueado', 0)->pluck('id');
            if (DB::table('Colaborador_Evaluacion')->whereIn('Colaborador_id', $colaboradorIds)->where('Evaluacion_id', $evaluacion->id)->where('bloqueado', 0)->exists()) return true;
            if (DB::table('Aplicacion')->whereIn('Colaborador_id', $colaboradorIds)->where('Evaluacion_id', $evaluacion->id)->where('bloqueado', 0)->exists()) return true;
        }

        return false;
    }
}