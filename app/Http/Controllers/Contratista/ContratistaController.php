<?php

namespace App\Http\Controllers\Contratista;

use App\Http\Controllers\Controller;
use App\Models\Contratista\Contratista;
use App\Models\Contratista\Colaborador;
use App\Models\Cliente\Cliente;
use App\Models\Usuario\Usuario;
use App\Models\Usuario\Rol;
use App\Models\Evaluacion\Evaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Facades\SessionService;

class ContratistaController extends Controller
{
    protected string $rutaBase = 'contratista.contratista.';

    public function __construct()
    {
        view()->share('rutaBase', $this->rutaBase);
    }

    private function usuario()
    {
        return Auth::guard('usuario')->user();
    }

    public function index()
    {
        $usuario = $this->usuario();

        if ($usuario->isSisAdmin()) {
            $contratistas = Contratista::withCount(['usuarios', 'colaboradores', 'evaluaciones'])
                ->orderBy('id')
                ->get();

            return view($this->rutaBase . 'index', compact('contratistas'));
        }

        $idsComoUsuario = DB::table('Contratista_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->where('bloqueado', 0)
            ->pluck('Contratista_id');

        $contratistasComoUsuario = Contratista::withCount(['usuarios', 'colaboradores', 'evaluaciones'])
            ->whereIn('id', $idsComoUsuario)
            ->orderBy('id')
            ->get();

        $clienteIdsEvaluador = DB::table('Evaluador')
            ->where('Usuario_id', $usuario->id)
            ->where('bloqueado', 0)
            ->pluck('Cliente_id');

        $contratistasComoEvaluador = Contratista::withCount(['usuarios', 'colaboradores', 'evaluaciones'])
            ->whereIn('id', function ($q) use ($clienteIdsEvaluador) {
                $q->select('Contratista_id')
                  ->from('Cliente_Contratista')
                  ->whereIn('Cliente_id', $clienteIdsEvaluador);
            })
            ->whereNotIn('id', $idsComoUsuario)
            ->orderBy('id')
            ->get();

        return view($this->rutaBase . 'index', compact(
            'contratistasComoUsuario',
            'contratistasComoEvaluador'
        ));
    }

    public function create()
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $clientes = Cliente::where('bloqueado', 0)->orderBy('nombre')->pluck('nombre', 'id');

        $usuarios = Usuario::where('bloqueado', 0)
            ->where(fn($q) => $q->whereNull('vigencia')->orWhere('vigencia', '>', now()))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $colaboradores = Usuario::where('bloqueado', 0)
            ->where(fn($q) => $q->whereNull('vigencia')->orWhere('vigencia', '>', now()))
            ->whereHas('roles', fn($q) => $q->where('nombre', 'Colaborador'))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        return view($this->rutaBase . 'create', compact('clientes', 'usuarios', 'colaboradores'));
    }

    public function store(Request $request)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $request->validate([
            'nombre'          => 'required|string|max:255',
            'rut'             => 'nullable|string|max:20',
            'clientes'        => 'nullable|array',
            'clientes.*'      => 'exists:Cliente,id',
            'usuarios'        => 'nullable|array',
            'usuarios.*'      => 'exists:Usuario,id',
            'colaboradores'   => 'nullable|array',
            'colaboradores.*' => 'exists:Usuario,id',
        ]);

        DB::transaction(function () use ($request) {
            $contratista = Contratista::create([
                'nombre'    => $request->nombre,
                'rut'       => $request->rut,
                'bloqueado' => 0,
            ]);

            if ($request->filled('clientes')) {
                $pivot = collect($request->clientes)->mapWithKeys(fn($id) => [
                    $id => ['Usuario_id' => Auth::guard('usuario')->id(), 'fecha' => now(), 'bloqueado' => 0]
                ])->all();
                $contratista->clientes()->sync($pivot);
            }

            if ($request->filled('usuarios')) {
                $pivot = collect($request->usuarios)->mapWithKeys(fn($id) => [
                    $id => ['fecha' => now(), 'bloqueado' => 0]
                ])->all();
                $contratista->usuarios()->sync($pivot);

                Usuario::whereIn('id', $request->usuarios)->each(
                    fn($u) => $this->asegurarRolContratista($u)
                );
            }

            if ($request->filled('colaboradores')) {
                foreach ($request->colaboradores as $uid) {
                    Colaborador::create([
                        'Contratista_id' => $contratista->id,
                        'Usuario_id'     => $uid,
                        'fecha'          => now(),
                        'bloqueado'      => 0,
                    ]);
                }
            }
        });

        SessionService::success('Contratista', 'Contratista creado correctamente.');
        return redirect()->route($this->rutaBase . 'index');
    }

    public function show(Contratista $contratista)
    {
        $usuario    = $this->usuario();
        $esSisAdmin = $usuario->isSisAdmin();

        if (!$esSisAdmin) {
            $tieneAcceso =
                DB::table('Contratista_Usuario')
                    ->where('Contratista_id', $contratista->id)
                    ->where('Usuario_id', $usuario->id)
                    ->where('bloqueado', 0)
                    ->exists()
                ||
                DB::table('Evaluador')
                    ->where('Usuario_id', $usuario->id)
                    ->where('bloqueado', 0)
                    ->whereIn('Cliente_id', function ($q) use ($contratista) {
                        $q->select('Cliente_id')
                        ->from('Cliente_Contratista')
                        ->where('Contratista_id', $contratista->id);
                    })
                    ->exists();

            if (!$tieneAcceso) abort(403);
        }

        if ($esSisAdmin) {
            $contratista->load(['clientes', 'usuarios', 'colaboradores.usuario', 'evaluaciones']);
        } else {
            $contratista->load([
                'clientes',
                'usuarios'      => fn($q) => $q->wherePivot('bloqueado', 0),
                'colaboradores' => fn($q) => $q->where('bloqueado', 0),
                'colaboradores.usuario',
                'evaluaciones'  => fn($q) => $q->wherePivot('bloqueado', 0),
            ]);
        }

        $evaluacionesDisponibles = Evaluacion::where('bloqueado', 0)
            ->whereNotIn('id', $contratista->evaluaciones->pluck('id'))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $colaboradoresAsignados = $contratista->colaboradores->pluck('Usuario_id');
        $colaboradoresDisponibles = Usuario::where('bloqueado', 0)
            ->where(fn($q) => $q->whereNull('vigencia')->orWhere('vigencia', '>', now()))
            ->whereHas('roles', fn($q) => $q->where('nombre', 'Colaborador'))
            ->whereNotIn('id', $colaboradoresAsignados)
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        return view($this->rutaBase . 'show', compact(
            'contratista',
            'esSisAdmin',
            'evaluacionesDisponibles',
            'colaboradoresDisponibles'
        ));
    }

    public function edit(Contratista $contratista)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $clientes = Cliente::where('bloqueado', 0)->orderBy('nombre')->pluck('nombre', 'id');

        $usuarios = Usuario::where('bloqueado', 0)
            ->where(fn($q) => $q->whereNull('vigencia')->orWhere('vigencia', '>', now()))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $colaboradores = Usuario::where('bloqueado', 0)
            ->where(fn($q) => $q->whereNull('vigencia')->orWhere('vigencia', '>', now()))
            ->whereHas('roles', fn($q) => $q->where('nombre', 'Colaborador'))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $clientesAsignados      = $contratista->clientes->pluck('id')->toArray();
        $usuariosAsignados      = $contratista->usuarios->pluck('id')->toArray();
        $colaboradoresAsignados = $contratista->colaboradores->pluck('Usuario_id')->toArray();

        return view($this->rutaBase . 'edit', compact(
            'contratista',
            'clientes',      'clientesAsignados',
            'usuarios',      'usuariosAsignados',
            'colaboradores', 'colaboradoresAsignados'
        ));
    }

    public function update(Request $request, Contratista $contratista)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $request->validate([
            'nombre'          => 'nullable|string|max:255',
            'rut'             => 'nullable|string|max:20',
            'clientes'        => 'nullable|array',
            'clientes.*'      => 'exists:Cliente,id',
            'usuarios'        => 'nullable|array',
            'usuarios.*'      => 'exists:Usuario,id',
            'colaboradores'   => 'nullable|array',
            'colaboradores.*' => 'exists:Usuario,id',
        ]);

        DB::transaction(function () use ($request, $contratista) {

            $data = array_filter([
                'nombre' => $request->filled('nombre') ? $request->nombre : null,
                'rut'    => $request->filled('rut')    ? $request->rut    : null,
            ], fn($v) => $v !== null);

            if (!empty($data)) {
                $contratista->update($data);
            }

            if ($request->has('clientes')) {
                $pivotClientes = collect($request->clientes ?? [])->mapWithKeys(fn($id) => [
                    $id => ['Usuario_id' => Auth::guard('usuario')->id(), 'fecha' => now(), 'bloqueado' => 0]
                ])->all();
                $contratista->clientes()->sync($pivotClientes);
            }

            if ($request->has('usuarios')) {
                $pivotUsuarios = collect($request->usuarios ?? [])->mapWithKeys(fn($id) => [
                    $id => ['fecha' => now(), 'bloqueado' => 0]
                ])->all();
                $contratista->usuarios()->sync($pivotUsuarios);

                if (!empty($request->usuarios)) {
                    Usuario::whereIn('id', $request->usuarios)->each(
                        fn($u) => $this->asegurarRolContratista($u)
                    );
                }
            }

            if ($request->has('colaboradores')) {
                $nuevosIds   = collect($request->colaboradores ?? []);
                $actualesIds = $contratista->colaboradores->pluck('Usuario_id');

                $contratista->colaboradores()
                    ->whereIn('Usuario_id', $actualesIds->diff($nuevosIds))
                    ->delete();

                foreach ($nuevosIds->diff($actualesIds) as $uid) {
                    Colaborador::create([
                        'Contratista_id' => $contratista->id,
                        'Usuario_id'     => $uid,
                        'fecha'          => now(),
                        'bloqueado'      => 0,
                    ]);
                }
            }
        });

        SessionService::success('Contratista', 'Contratista actualizado correctamente.');
        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function destroy(Contratista $contratista)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $nuevoEstado = $contratista->bloqueado ? 0 : 1;
        $mensaje     = $nuevoEstado ? 'bloqueado' : 'desbloqueado';

        $contratista->update(['bloqueado' => $nuevoEstado]);

        SessionService::success('Contratista', "Contratista {$mensaje} correctamente.");
        return redirect()->route($this->rutaBase . 'index');
    }

    public function asignarEvaluacion(Request $request, Contratista $contratista)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $request->validate([
            'Evaluacion_id'   => 'required|array|min:1',
            'Evaluacion_id.*' => 'exists:Evaluacion,id',
        ]);

        $asignados = $yaAsociados = 0;

        foreach ($request->Evaluacion_id as $eid) {
            if ($contratista->evaluaciones()->where('Evaluacion_id', $eid)->exists()) {
                $yaAsociados++;
                continue;
            }
            $contratista->evaluaciones()->attach($eid, [
                'Usuario_id' => Auth::guard('usuario')->id(),
                'fecha'      => now(),
                'bloqueado'  => 0,
            ]);
            $asignados++;
        }

        if ($asignados > 0) {
            SessionService::success('Evaluacion', $asignados === 1
                ? 'Evaluación asignada al contratista.'
                : "{$asignados} evaluaciones asignadas al contratista.");
        } else {
            SessionService::warning('Evaluacion', 'Las evaluaciones seleccionadas ya estaban asignadas.');
        }

        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function desasignarEvaluacion(Contratista $contratista, Evaluacion $evaluacion)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $contratista->evaluaciones()->detach($evaluacion->id);

        SessionService::success('Evaluacion', 'Evaluación desasociada correctamente.');
        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function asignarColaborador(Request $request, Contratista $contratista)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $request->validate([
            'Usuario_id'   => 'required|array|min:1',
            'Usuario_id.*' => 'exists:Usuario,id',
        ]);

        $asignados = $yaAsociados = 0;

        foreach ($request->Usuario_id as $uid) {
            if ($contratista->colaboradores()->where('Usuario_id', $uid)->exists()) {
                $yaAsociados++;
                continue;
            }
            Colaborador::create([
                'Contratista_id' => $contratista->id,
                'Usuario_id'     => $uid,
                'fecha'          => now(),
                'bloqueado'      => 0,
            ]);
            $asignados++;
        }

        if ($asignados > 0) {
            SessionService::success('Colaborador', $asignados === 1
                ? 'Colaborador asignado correctamente.'
                : "{$asignados} colaboradores asignados correctamente.");
        } else {
            SessionService::warning('Colaborador', 'Todos los colaboradores seleccionados ya estaban asignados.');
        }

        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function desasignarColaborador(Contratista $contratista, Colaborador $colaborador)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $colaborador->delete();

        SessionService::success('Colaborador', 'Colaborador removido correctamente.');
        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }
    private function asegurarRolContratista(Usuario $usuario): void
    {
        if (!$usuario->hasRole('Contratista')) {
            $rol = Rol::where('nombre', 'Contratista')->where('bloqueado', 0)->first();
            if ($rol) {
                $usuario->roles()->attach($rol->id, [
                    'fecha'     => now(),
                    'bloqueado' => 0,
                ]);
            }
        }
    }
}