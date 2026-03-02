<?php

namespace App\Http\Controllers\Contratista;

use App\Http\Controllers\Controller;
use App\Models\Contratista\Contratista;
use App\Models\Contratista\Colaborador;
use App\Models\Aplicacion\Aplicacion;
use App\Models\Cliente\Cliente;
use App\Models\Usuario\Usuario;
use App\Models\Usuario\Rol;
use App\Models\Evaluacion\Evaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Surveys\Token;
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

    public function index(Request $request)
    {
        $usuario = $this->usuario();

        if ($usuario->isSisAdmin()) {
            $query = Contratista::withCount(['usuarios', 'colaboradores', 'evaluaciones'])->orderBy('id');

            if ($request->filled('buscar')) {
                $query->where('nombre', 'like', '%' . $request->buscar . '%');
            }

            $contratistas = $query->paginate(20)->withQueryString();

            return view($this->rutaBase . 'index', compact('contratistas'));
        }

        $idsComoUsuario = DB::table('Contratista_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->where('bloqueado', 0)
            ->pluck('Contratista_id');

        $queryComoUsuario = Contratista::withCount(['usuarios', 'colaboradores', 'evaluaciones'])
            ->whereIn('id', $idsComoUsuario)
            ->orderBy('id');

        if ($request->filled('buscar')) {
            $queryComoUsuario->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $contratistasComoUsuario = $queryComoUsuario->paginate(20, ['*'], 'page_usuario')->withQueryString();

        $clienteIds = DB::table('Cliente_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->where('bloqueado', 0)
            ->pluck('Cliente_id');

        $queryComoCliente = Contratista::withCount(['usuarios', 'colaboradores', 'evaluaciones'])
            ->whereIn('id', function ($q) use ($clienteIds) {
                $q->select('Contratista_id')
                  ->from('Cliente_Contratista')
                  ->whereIn('Cliente_id', $clienteIds)
                  ->where('bloqueado', 0);
            })
            ->whereNotIn('id', $idsComoUsuario)
            ->orderBy('id');

        if ($request->filled('buscar')) {
            $queryComoCliente->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $contratistasComoCliente = $queryComoCliente->paginate(20, ['*'], 'page_cliente')->withQueryString();

        $evaluadorIds = DB::table('Evaluador')
            ->where('Usuario_id', $usuario->id)
            ->where('bloqueado', 0)
            ->pluck('id');

        $evaluacionIdsComoEvaluador = DB::table('Evaluador_Evaluacion')
            ->whereIn('Evaluador_id', $evaluadorIds)
            ->where('bloqueado', 0)
            ->pluck('Evaluacion_id');

        $queryComoEvaluador = Contratista::withCount(['usuarios', 'colaboradores', 'evaluaciones'])
            ->whereIn('id', function ($q) use ($evaluacionIdsComoEvaluador) {
                $q->select('Contratista_id')
                  ->from('Contratista_Evaluacion')
                  ->whereIn('Evaluacion_id', $evaluacionIdsComoEvaluador)
                  ->where('bloqueado', 0);
            })
            ->whereNotIn('id', $idsComoUsuario)
            ->orderBy('id');

        if ($request->filled('buscar')) {
            $queryComoEvaluador->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $contratistasComoEvaluador = $queryComoEvaluador->paginate(20, ['*'], 'page_evaluador')->withQueryString();

        return view($this->rutaBase . 'index', compact(
            'contratistasComoUsuario',
            'contratistasComoCliente',
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

                Usuario::whereIn('id', $request->usuarios)->each(fn($u) => $this->asegurarRolContratista($u));
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
            $tieneAccesoComoUsuario = DB::table('Contratista_Usuario')
                ->where('Contratista_id', $contratista->id)
                ->where('Usuario_id', $usuario->id)
                ->where('bloqueado', 0)
                ->exists();

            $evaluadorIds = DB::table('Evaluador')->where('Usuario_id', $usuario->id)->where('bloqueado', 0)->pluck('id');
            $evaluacionIds = DB::table('Evaluador_Evaluacion')->whereIn('Evaluador_id', $evaluadorIds)->where('bloqueado', 0)->pluck('Evaluacion_id');

            $tieneAccesoComoEvaluador = DB::table('Contratista_Evaluacion')
                ->where('Contratista_id', $contratista->id)
                ->whereIn('Evaluacion_id', $evaluacionIds)
                ->where('bloqueado', 0)
                ->exists();

            $tieneAccesoComoCliente = DB::table('Cliente_Contratista')
                ->where('Contratista_id', $contratista->id)
                ->whereIn('Cliente_id', function ($q) use ($usuario) {
                    $q->select('Cliente_id')->from('Cliente_Usuario')->where('Usuario_id', $usuario->id)->where('bloqueado', 0);
                })
                ->where('bloqueado', 0)
                ->exists();

            if (!$tieneAccesoComoUsuario && !$tieneAccesoComoEvaluador && !$tieneAccesoComoCliente) abort(403);
        }

        $contratista->load(['clientes', 'usuarios']);

        if ($esSisAdmin) {
            $contratista->load([
                'colaboradores.usuario',
                'colaboradores.evaluaciones'                          => fn($q) => $q->wherePivot('bloqueado', 0),
                'colaboradores.aplicaciones'                          => fn($q) => $q->where('bloqueado', 0),
                'colaboradores.aplicaciones.evaluacion',
                'evaluaciones',
            ]);
        } else {
            $contratista->load([
                'colaboradores'                                       => fn($q) => $q->where('bloqueado', 0),
                'colaboradores.usuario',
                'colaboradores.evaluaciones'                          => fn($q) => $q->wherePivot('bloqueado', 0),
                'colaboradores.aplicaciones'                          => fn($q) => $q->where('bloqueado', 0),
                'colaboradores.aplicaciones.evaluacion',
                'evaluaciones'                                        => fn($q) => $q->wherePivot('bloqueado', 0),
            ]);
        }

        $contratista->colaboradores->each(function ($col) {
            $evalPorAplicacion = $col->aplicaciones
                ->map(fn($ap) => $ap->evaluacion)
                ->filter();

            $col->setRelation(
                'evaluaciones',
                $col->evaluaciones->merge($evalPorAplicacion)->unique('id')
            );
        });

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

        $evaluacionesContratista = $contratista->evaluaciones->sortBy('nombre');

        return view($this->rutaBase . 'show', compact(
            'contratista',
            'esSisAdmin',
            'evaluacionesDisponibles',
            'colaboradoresDisponibles',
            'evaluacionesContratista'
        ));
    }

    public function edit(Contratista $contratista)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $clientes = Cliente::where('bloqueado', 0)->orderBy('nombre')->pluck('nombre', 'id');
        $usuarios = Usuario::where('bloqueado', 0)->where(fn($q) => $q->whereNull('vigencia')->orWhere('vigencia', '>', now()))->orderBy('nombre')->pluck('nombre', 'id');
        $colaboradores = Usuario::where('bloqueado', 0)->where(fn($q) => $q->whereNull('vigencia')->orWhere('vigencia', '>', now()))->whereHas('roles', fn($q) => $q->where('nombre', 'Colaborador'))->orderBy('nombre')->pluck('nombre', 'id');

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

            if (!empty($data)) $contratista->update($data);

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
                    Usuario::whereIn('id', $request->usuarios)->each(fn($u) => $this->asegurarRolContratista($u));
                }
            }

            if ($request->has('colaboradores')) {
                $nuevosIds   = collect($request->colaboradores ?? []);
                $actualesIds = $contratista->colaboradores->pluck('Usuario_id');

                $contratista->colaboradores()->whereIn('Usuario_id', $actualesIds->diff($nuevosIds))->delete();

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
        $contratista->update(['bloqueado' => $nuevoEstado]);

        SessionService::success('Contratista', "Contratista " . ($nuevoEstado ? 'bloqueado' : 'desbloqueado') . " correctamente.");
        return redirect()->route($this->rutaBase . 'index');
    }

    public function asignarEvaluacion(Request $request, Contratista $contratista)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $request->validate(['Evaluacion_id' => 'required|array|min:1', 'Evaluacion_id.*' => 'exists:Evaluacion,id']);

        $asignados = $yaAsociados = 0;

        foreach ($request->Evaluacion_id as $eid) {
            if ($contratista->evaluaciones()->where('Evaluacion_id', $eid)->exists()) { $yaAsociados++; continue; }
            $contratista->evaluaciones()->attach($eid, ['Usuario_id' => Auth::guard('usuario')->id(), 'fecha' => now(), 'bloqueado' => 0]);
            $asignados++;
        }

        if ($asignados > 0) {
            SessionService::success('Evaluacion', $asignados === 1 ? 'Evaluación asignada al contratista.' : "{$asignados} evaluaciones asignadas al contratista.");
        } else {
            SessionService::warning('Evaluacion', 'Las evaluaciones seleccionadas ya estaban asignadas.');
        }

        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function desasignarEvaluacion(Contratista $contratista, Evaluacion $evaluacion)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        DB::table('Contratista_Evaluacion')
            ->where('Contratista_id', $contratista->id)
            ->where('Evaluacion_id', $evaluacion->id)
            ->update(['bloqueado' => 1]);

        SessionService::success('Evaluacion', 'Evaluación desasociada correctamente.');
        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function asignarColaborador(Request $request, Contratista $contratista)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $request->validate(['Usuario_id' => 'required|array|min:1', 'Usuario_id.*' => 'exists:Usuario,id']);

        $asignados = $yaAsociados = 0;

        foreach ($request->Usuario_id as $uid) {
            if ($contratista->colaboradores()->where('Usuario_id', $uid)->exists()) { $yaAsociados++; continue; }
            Colaborador::create(['Contratista_id' => $contratista->id, 'Usuario_id' => $uid, 'fecha' => now(), 'bloqueado' => 0]);
            $asignados++;
        }

        if ($asignados > 0) {
            SessionService::success('Colaborador', $asignados === 1 ? 'Colaborador asignado correctamente.' : "{$asignados} colaboradores asignados correctamente.");
        } else {
            SessionService::warning('Colaborador', 'Todos los colaboradores seleccionados ya estaban asignados.');
        }

        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function desasignarColaborador(Contratista $contratista, Colaborador $colaborador)
    {
        if (!$this->usuario()->isSisAdmin()) abort(403);

        $colaborador->update(['bloqueado' => 1]);

        SessionService::success('Colaborador', 'Colaborador removido correctamente.');
        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function asignarEvaluacionColaborador(Request $request, Contratista $contratista, Colaborador $colaborador)
    {
        $usuario = $this->usuario();

        if (!$usuario->isSisAdmin()) {
            $tieneAcceso = DB::table('Contratista_Usuario')
                ->where('Contratista_id', $contratista->id)
                ->where('Usuario_id', $usuario->id)
                ->where('bloqueado', 0)
                ->exists();
            if (!$tieneAcceso) abort(403);
        }

        if ($colaborador->Contratista_id !== $contratista->id) abort(403);

        $request->validate([
            'Evaluacion_id'   => 'required|array|min:1',
            'Evaluacion_id.*' => 'exists:Evaluacion,id',
        ]);

        $asignados = $yaAsociados = 0;

        foreach ($request->Evaluacion_id as $eid) {
            if (!$contratista->evaluaciones()->where('Evaluacion_id', $eid)->exists()) continue;

            $evaluacion = Evaluacion::find($eid);

            $sid      = $evaluacion->sid;
            $email    = $colaborador->usuario->email ?? '';
            $usesleft = $evaluacion->permanent ? 9999 : 1;

            if ($evaluacion->byEvaluador) {
                $yaExiste = Aplicacion::where('Colaborador_id', $colaborador->id)
                    ->where('Evaluacion_id', $eid)
                    ->where('bloqueado', 0)
                    ->exists();

                if ($yaExiste) { $yaAsociados++; continue; }

                $token = Str::random(rand(10, 15));

                Aplicacion::create([
                    'Evaluador_id'   => null,
                    'Evaluacion_id'  => $eid,
                    'Colaborador_id' => $colaborador->id,
                    'token'          => $token,
                    'fecha'          => now(),
                    'submitdate'     => null,
                    'bloqueado'      => 0,
                ]);

                (new Token())
                    ->setTable('lime_tokens_' . $sid)
                    ->newQuery()
                    ->create([
                        'email'         => $email,
                        'emailstatus'   => 'OK',
                        'token'         => $token,
                        'language'      => 'es',
                        'sent'          => 'N',
                        'remindersent'  => 'N',
                        'remindercount' => 0,
                        'usesleft'      => $usesleft,
                        'attribute_40'  => now()->format('Ymd'),
                    ]);
            } else {
                if ($colaborador->evaluaciones()->where('Evaluacion_id', $eid)->exists()) {
                    $yaAsociados++; continue;
                }

                $token = Str::random(rand(10, 15));

                $colaborador->evaluaciones()->attach($eid, [
                    'token'     => $token,
                    'fecha'     => now(),
                    'bloqueado' => 0,
                ]);

                (new Token())
                    ->setTable('lime_tokens_' . $sid)
                    ->newQuery()
                    ->create([
                        'email'         => $email,
                        'emailstatus'   => 'OK',
                        'token'         => $token,
                        'language'      => 'es',
                        'sent'          => 'N',
                        'remindersent'  => 'N',
                        'remindercount' => 0,
                        'usesleft'      => $usesleft,
                        'attribute_40'  => now()->format('Ymd'),
                    ]);
            }

            $asignados++;
        }

        if ($asignados > 0) {
            SessionService::success('Evaluacion', $asignados === 1
                ? 'Evaluación asignada al colaborador.'
                : "{$asignados} evaluaciones asignadas al colaborador.");
        } else {
            SessionService::warning('Evaluacion', 'Las evaluaciones seleccionadas ya estaban asignadas o no pertenecen al contratista.');
        }

        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    public function desasignarEvaluacionColaborador(Contratista $contratista, Colaborador $colaborador, Evaluacion $evaluacion)
    {
        $usuario = $this->usuario();

        if (!$usuario->isSisAdmin()) {
            $tieneAcceso = DB::table('Contratista_Usuario')
                ->where('Contratista_id', $contratista->id)
                ->where('Usuario_id', $usuario->id)
                ->where('bloqueado', 0)
                ->exists();
            if (!$tieneAcceso) abort(403);
        }

        if ($colaborador->Contratista_id !== $contratista->id) abort(403);

        if ($evaluacion->byEvaluador) {
            Aplicacion::where('Colaborador_id', $colaborador->id)
                ->where('Evaluacion_id', $evaluacion->id)
                ->where('bloqueado', 0)
                ->update(['bloqueado' => 1]);
        } else {
            DB::table('Colaborador_Evaluacion')
                ->where('Colaborador_id', $colaborador->id)
                ->where('Evaluacion_id', $evaluacion->id)
                ->update(['bloqueado' => 1]);
        }

        SessionService::success('Evaluacion', 'Evaluación desasignada del colaborador.');
        return redirect()->route($this->rutaBase . 'show', ['contratista' => $contratista]);
    }

    private function asegurarRolContratista(Usuario $usuario): void
    {
        if (!$usuario->hasRole('Contratista')) {
            $rol = Rol::where('nombre', 'Contratista')->where('bloqueado', 0)->first();
            if ($rol) {
                $usuario->roles()->attach($rol->id, ['fecha' => now(), 'bloqueado' => 0]);
            }
        }
    }
}