<?php

namespace App\Http\Controllers\Aplicacion;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Aplicacion\Aplicacion;
use App\Models\Contratista\Colaborador;
use App\Models\Contratista\Contratista;
use App\Models\Evaluacion\Evaluacion;
use App\Services\Facades\SessionService;
use Carbon\Carbon;

class AplicacionController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $usuario = Auth::user();
        $data = [];

        if ($usuario->hasRole('Evaluador')) {
            $evaluadores = $usuario->evaluadores()->get();

            $todasAppsSinEvaluador = collect();

            foreach ($evaluadores as $evaluador) {
                $evaluacionIds = $evaluador->evaluaciones->pluck('id');

                $apps = Aplicacion::query()
                    ->whereNull('Evaluador_id')
                    ->whereNull('submitdate')
                    ->whereIn('Evaluacion_id', $evaluacionIds)
                    ->with([
                        'colaborador.usuario',
                        'colaborador.contratista',
                        'evaluacion',
                    ])
                    ->get();

                $todasAppsSinEvaluador = $todasAppsSinEvaluador->merge($apps);
            }

            $todasAppsSinEvaluador = $todasAppsSinEvaluador->unique('id');

            $data['evaluador'] = [
                'aplicaciones' => $this->paginateCollection($todasAppsSinEvaluador, 'eva_pend'),
                'total'        => $todasAppsSinEvaluador->count(),
            ];
        }

        if ($usuario->hasRole('Colaborador')) {
            $colaboradores  = $usuario->colaboradores()->get();
            $colaboradorIds = $colaboradores->pluck('id');

            $appsColaborador = Aplicacion::query()
                ->whereIn('Colaborador_id', $colaboradorIds)
                ->whereNull('submitdate')
                ->with([
                    'evaluacion',
                    'colaborador.usuario',
                    'colaborador.contratista',
                ])
                ->paginate(self::PER_PAGE);

            $data['colaborador'] = [
                'aplicaciones' => $appsColaborador,
                'total'        => $appsColaborador->total(),
            ];
        }

        return view('aplicacion.aplicacion.index', $data);
    }

    public function documentos(Request $request)
    {
        $usuario = Auth::user();

        // ── Rango de fechas ──────────────────────────────────────────────────────
        $desde = $request->input('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->input('hasta', now()->endOfMonth()->toDateString());

        $desdeCarbon = Carbon::parse($desde)->startOfDay();
        $hastaCarbon = Carbon::parse($hasta)->endOfDay();

        // ── Determinar contratistas accesibles para este usuario ─────────────────
        $contratistasAccesibles = collect();
        $mostrarContratista     = false;

        if ($usuario->hasRole('Contratista')) {
            $contratistasAccesibles = $usuario->contratistas()
                ->where('Contratista.bloqueado', 0)
                ->get();
        }

        if ($usuario->hasRole('Cliente')) {
            $mostrarContratista = true;

            $clientes = $usuario->clientes()->get();
            $contratistaIds = collect();
            foreach ($clientes as $cliente) {
                $contratistaIds = $contratistaIds->merge(
                    $cliente->contratistas()->pluck('Contratista.id')
                );
            }

            $contratistasAccesibles = Contratista::query()
                ->whereIn('id', $contratistaIds->unique())
                ->where('Contratista.bloqueado', 0)
                ->get();
        }

        $contratistaIdsAccesibles = $contratistasAccesibles->pluck('id');

        // ── Colaboradores base (según contratistas accesibles) ───────────────────
        $colaboradorIdsBase = Colaborador::query()
            ->whereIn('Contratista_id', $contratistaIdsAccesibles)
            ->where('bloqueado', 0)
            ->pluck('id');

        $evaluacionIdsAccesibles = collect();

        if ($usuario->hasRole('Cliente')) {
            $clientes = $usuario->clientes()->get();
            foreach ($clientes as $cliente) {
                $evaluacionIdsAccesibles = $evaluacionIdsAccesibles->merge(
                    $cliente->evaluaciones()->pluck('Evaluacion.id')
                );
            }
        }

        if ($usuario->hasRole('Contratista')) {
            $contratistasDelUsuario = $usuario->contratistas()->get();
            foreach ($contratistasDelUsuario as $contratista) {
                $evaluacionIdsAccesibles = $evaluacionIdsAccesibles->merge(
                    $contratista->evaluaciones()->pluck('Evaluacion.id')
                );
            }
        }

        $evaluacionesAccesibles = Evaluacion::query()
            ->whereIn('id', $evaluacionIdsAccesibles->unique())
            ->where('bloqueado', 0)
            ->orderBy('nombre')
            ->get();

        // ── Filtros seleccionados por el usuario ─────────────────────────────────
        $filtroContratistas  = array_filter((array) $request->input('contratistas', []));
        $filtroEvaluaciones  = array_filter((array) $request->input('evaluaciones', []));

        $contratistaIdsFiltro = collect($filtroContratistas)->isNotEmpty()
            ? $contratistaIdsAccesibles->intersect($filtroContratistas)
            : $contratistaIdsAccesibles;

        $colaboradorIdsFiltro = Colaborador::query()
            ->whereIn('Contratista_id', $contratistaIdsFiltro)
            ->where('bloqueado', 0)
            ->pluck('id');

        if (collect($filtroEvaluaciones)->isNotEmpty()) {
            $colaboradoresPorEval = Aplicacion::query()
                ->whereIn('Colaborador_id', $colaboradorIdsFiltro)
                ->whereIn('Evaluacion_id', $filtroEvaluaciones)
                ->where('bloqueado', 0)
                ->pluck('Colaborador_id')
                ->unique();

            $colaboradorIdsFiltro = $colaboradorIdsFiltro->intersect($colaboradoresPorEval);
        }

        // ── Cargar colaboradores con documentos filtrados por fecha ──────────────
        $filas = collect();

        if ($colaboradorIdsFiltro->isNotEmpty()) {
            $colaboradoresConDocs = Colaborador::query()
                ->whereIn('id', $colaboradorIdsFiltro)
                ->with([
                    'usuario',
                    'contratista',
                    'documentos' => function ($q) use ($desdeCarbon, $hastaCarbon) {
                        $q->wherePivot('bloqueado', 0)
                          ->whereBetween('Documento.fecha', [$desdeCarbon, $hastaCarbon])
                          ->orderByDesc('Documento.fecha');
                    },
                ])
                ->get();

            foreach ($colaboradoresConDocs as $col) {
                foreach ($col->documentos as $doc) {
                    $filas->push(['col' => $col, 'doc' => $doc]);
                }
            }

            $filas = $filas->sortByDesc(function ($fila) {
                return $fila['doc']->fecha;
            })->values();
        }

        return view('aplicacion.aplicacion.documentos', [
            'filas'                  => $filas,
            'desde'                  => $desde,
            'hasta'                  => $hasta,
            'mostrarContratista'     => $mostrarContratista,
            'contratistasAccesibles' => $contratistasAccesibles,
            'evaluacionesAccesibles' => $evaluacionesAccesibles,
            'filtroContratistas'     => $filtroContratistas,
            'filtroEvaluaciones'     => $filtroEvaluaciones,
        ]);
    }

    public function asignarEvaluador(Aplicacion $aplicacion)
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('Evaluador')) {
            abort(403);
        }

        if (!is_null($aplicacion->Evaluador_id)) {
            SessionService::warning('Aplicacion', 'Esta aplicación ya tiene un evaluador asignado.');
            return redirect()->back();
        }

        $evaluadorIds = DB::table('Evaluador')
            ->where('Usuario_id', $usuario->id)
            ->where('bloqueado', 0)
            ->pluck('id');

        $evaluadorAsignado = DB::table('Evaluador_Evaluacion')
            ->whereIn('Evaluador_id', $evaluadorIds)
            ->where('Evaluacion_id', $aplicacion->Evaluacion_id)
            ->where('bloqueado', 0)
            ->exists();

        if (!$evaluadorAsignado) {
            abort(403, 'No tienes permiso para asignarte a esta aplicación.');
        }

        $evaluadorId = DB::table('Evaluador_Evaluacion')
            ->whereIn('Evaluador_id', $evaluadorIds)
            ->where('Evaluacion_id', $aplicacion->Evaluacion_id)
            ->where('bloqueado', 0)
            ->value('Evaluador_id');

        $aplicacion->update(['Evaluador_id' => $evaluadorId]);

        $evaluacion = $aplicacion->evaluacion;

        if ($evaluacion && $evaluacion->sid && $aplicacion->token) {
            $usesleft = $evaluacion->permanent ? 9999 : 1;

            $email = DB::table('Colaborador')
                ->join('Usuario', 'Colaborador.Usuario_id', '=', 'Usuario.id')
                ->where('Colaborador.id', $aplicacion->Colaborador_id)
                ->value('Usuario.email') ?? '';

            DB::connection('survey')
                ->table("lime_tokens_{$evaluacion->sid}")
                ->insert([
                    'email'         => $email,
                    'emailstatus'   => 'OK',
                    'token'         => $aplicacion->token,
                    'language'      => 'es',
                    'sent'          => 'N',
                    'remindersent'  => 'N',
                    'remindercount' => 0,
                    'usesleft'      => $usesleft,
                    'attribute_40'  => now()->format('Ymd'),
                ]);
        }

        SessionService::success('Aplicacion', 'Te has asignado correctamente como evaluador. El colaborador ya puede acceder a la evaluación.');
        return redirect()->back();
    }

    private function paginateCollection($collection, string $pageName)
    {
        $page  = (int) request()->input($pageName, 1);
        $items = $collection->forPage($page, self::PER_PAGE);

        $paginator = new LengthAwarePaginator(
            $items->values(),
            $collection->count(),
            self::PER_PAGE,
            $page,
            ['path' => request()->url(), 'pageName' => $pageName]
        );

        return $paginator->appends(request()->except($pageName));
    }
}