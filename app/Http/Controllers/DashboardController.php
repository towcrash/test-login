<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Aplicacion\Aplicacion;

class DashboardController extends Controller
{
    private const PER_PAGE = 10;

    private function paginateCollection($collection, string $pageName): LengthAwarePaginator
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

    public function index()
    {
        $usuario = Auth::user();
        $data    = [];

        if ($usuario->hasRole('Cliente')) {
            $clientes = $usuario->clientes()->get();
            
            $todosLosContratistasPendientes = collect();
            $todasLasAppsTotales = collect();

            foreach ($clientes as $cliente) {
                $appsCliente = Aplicacion::query()
                    ->whereNotNull('submitdate')
                    ->whereHas('colaborador.contratista.clientes', fn($q) =>
                        $q->where('Cliente.id', $cliente->id)
                    )
                    ->get();
                
                $todasLasAppsTotales = $todasLasAppsTotales->merge($appsCliente);

                $contratistasPendientes = $cliente->contratistas()
                    ->with([
                        'colaboradores.usuario',
                        'colaboradores.aplicaciones' => fn($q) =>
                            $q->whereNull('submitdate')->with('evaluacion'),
                    ])
                    ->get()
                    ->flatMap(function ($contratista) {
                        $filas = collect();
                        foreach ($contratista->colaboradores as $colaborador) {
                            foreach ($colaborador->aplicaciones as $app) {
                                if ($app->evaluacion && $app->evaluacion->permanent == 0) {
                                    $filas->push([
                                        'contratista' => $contratista,
                                        'colaborador' => $colaborador,
                                        'app'         => $app,
                                        'evaluacion'  => $app->evaluacion,
                                    ]);
                                }
                            }
                        }
                        return $filas;
                    });
                
                $todosLosContratistasPendientes = $todosLosContratistasPendientes->merge($contratistasPendientes);
            }

            $data['cliente'] = [
                'total_clientes'           => $clientes->count(),
                'semana_actual'            => $todasLasAppsTotales->filter(fn($a) => $a->submitdate?->isCurrentWeek())->count(),
                'mes_actual'               => $todasLasAppsTotales->filter(fn($a) => $a->submitdate?->isCurrentMonth())->count(),
                'mes_pasado'                => $todasLasAppsTotales->filter(fn($a) => $a->submitdate?->isLastMonth())->count(),
                'contratistas_pendientes'  => $this->paginateCollection($todosLosContratistasPendientes->unique('app.id'), 'cli_pend'),
            ];
        }

        if ($usuario->hasRole('Evaluador')) {
            $evaluadores = $usuario->evaluadores()->get();
            
            $todasEvaluaciones = collect();
            $todasAppsEsteMes = collect();
            $todasAppsSinEvaluador = collect();

            foreach ($evaluadores as $evaluador) {
                $todasEvaluaciones = $todasEvaluaciones->merge($evaluador->evaluaciones);
                
                $appsEsteMes = $evaluador->aplicaciones()
                    ->whereNotNull('submitdate')
                    ->whereMonth('submitdate', now()->month)
                    ->whereYear('submitdate', now()->year)
                    ->with('colaborador.usuario', 'evaluacion')
                    ->get();
                $todasAppsEsteMes = $todasAppsEsteMes->merge($appsEsteMes);
                
                $evaluacionIds = $evaluador->evaluaciones->pluck('id');
                
                $appsSinEvaluador = Aplicacion::query()
                    ->whereNull('Evaluador_id')
                    ->whereNull('submitdate')
                    ->whereIn('Evaluacion_id', $evaluacionIds)
                    ->with('colaborador.usuario', 'evaluacion')
                    ->get();
                $todasAppsSinEvaluador = $todasAppsSinEvaluador->merge($appsSinEvaluador);
            }

            $data['evaluador'] = [
                'total_evaluadores'    => $evaluadores->count(),
                'evaluaciones'         => $this->paginateCollection($todasEvaluaciones->unique('id'), 'eva_eval'),
                'apps_este_mes'        => $this->paginateCollection($todasAppsEsteMes->unique('id'), 'eva_mes'),
                'apps_sin_evaluador'   => $this->paginateCollection($todasAppsSinEvaluador->unique('id'), 'eva_sin'),
            ];
        }

        if ($usuario->hasRole('Contratista')) {
            $contratistas = $usuario->contratistas()->get();
            
            $todasLasAppsTotales = collect();
            $todosLosColaboradoresPendientes = collect();

            foreach ($contratistas as $contratista) {
                $appsContratista = Aplicacion::query()
                    ->whereNotNull('submitdate')
                    ->whereHas('colaborador', fn($q) =>
                        $q->where('Contratista_id', $contratista->id)
                    )
                    ->get();
                
                $todasLasAppsTotales = $todasLasAppsTotales->merge($appsContratista);

                $colaboradoresPendientes = $contratista->colaboradores()
                    ->with([
                        'usuario',
                        'aplicaciones' => fn($q) =>
                            $q->whereNull('submitdate')->with('evaluacion'),
                    ])
                    ->get()
                    ->flatMap(function ($colaborador) {
                        $filas = collect();
                        foreach ($colaborador->aplicaciones as $app) {
                            if ($app->evaluacion && $app->evaluacion->permanent == 0) {
                                $filas->push([
                                    'colaborador' => $colaborador,
                                    'app'         => $app,
                                    'evaluacion'  => $app->evaluacion,
                                ]);
                            }
                        }
                        return $filas;
                    });
                
                $todosLosColaboradoresPendientes = $todosLosColaboradoresPendientes->merge($colaboradoresPendientes);
            }

            $data['contratista'] = [
                'total_contratistas'        => $contratistas->count(),
                'semana_actual'              => $todasLasAppsTotales->filter(fn($a) => $a->submitdate?->isCurrentWeek())->count(),
                'mes_actual'                 => $todasLasAppsTotales->filter(fn($a) => $a->submitdate?->isCurrentMonth())->count(),
                'mes_pasado'                  => $todasLasAppsTotales->filter(fn($a) => $a->submitdate?->isLastMonth())->count(),
                'colaboradores_pendientes'   => $this->paginateCollection($todosLosColaboradoresPendientes->unique('app.id'), 'con_pend'),
            ];
        }

        if ($usuario->hasRole('Colaborador')) {
            $colaboradores = $usuario->colaboradores()->get();

            $pendientesNoPermanentes = collect();
            $pendientesPermanentes   = collect();
            $realizadas              = collect();

            foreach ($colaboradores as $colaboradorModel) {
                $evaluacionesAsignadas = $colaboradorModel->evaluaciones()
                    ->withPivot('token', 'fecha', 'submitdate', 'bloqueado')
                    ->wherePivot('bloqueado', 0)
                    ->get();

                foreach ($evaluacionesAsignadas as $ev) {
                    if (is_null($ev->pivot->submitdate)) {
                        $item = [
                            'evaluacion'  => $ev,
                            'colaborador' => $colaboradorModel,
                            'fecha'       => $ev->pivot->fecha,
                            'token'       => $ev->pivot->token,
                            'tipo_origen' => 'asignacion_directa',
                        ];
                        if ($ev->permanent == 0) {
                            $pendientesNoPermanentes->push($item);
                        } else {
                            $pendientesPermanentes->push($item);
                        }
                    }
                }

                $aplicacionesPendientes = $colaboradorModel->aplicaciones()
                    ->whereNull('submitdate')
                    ->with('evaluacion')
                    ->get();

                foreach ($aplicacionesPendientes as $app) {
                    if ($app->evaluacion) {
                        $item = [
                            'evaluacion'  => $app->evaluacion,
                            'colaborador' => $colaboradorModel,
                            'fecha'       => $app->fecha,
                            'token'       => $app->token,
                            'tipo_origen' => 'aplicacion',
                            'app_id'      => $app->id,
                        ];
                        if ($app->evaluacion->permanent == 0) {
                            $pendientesNoPermanentes->push($item);
                        } else {
                            $pendientesPermanentes->push($item);
                        }
                    }
                }

                $realizadas = $realizadas->merge(
                    $colaboradorModel->aplicaciones()
                        ->whereNotNull('submitdate')
                        ->with('evaluacion')
                        ->get()
                );
            }

            $pendientesNoPermanentes = $pendientesNoPermanentes->unique(function ($item) {
                return $item['token'] ?? ($item['evaluacion']->id . '_' . $item['colaborador']->id);
            })->values();

            $pendientesPermanentes = $pendientesPermanentes->unique(function ($item) {
                return $item['token'] ?? ($item['evaluacion']->id . '_' . $item['colaborador']->id);
            })->values();

            $realizadas = $realizadas->sortByDesc('submitdate')->unique('id')->values();

            $data['colaborador'] = [
                'total_colaboradores'           => $colaboradores->count(),
                'pendientes_no_permanentes'     => $this->paginateCollection($pendientesNoPermanentes, 'col_noperm'),
                'pendientes_permanentes'        => $this->paginateCollection($pendientesPermanentes,   'col_perm'),
                'realizadas'                     => $this->paginateCollection($realizadas,              'col_real'),
            ];
        }

        return view('dashboard', $data);
    }
}