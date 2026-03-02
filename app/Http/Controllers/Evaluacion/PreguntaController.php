<?php

namespace App\Http\Controllers\Evaluacion;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion\Pregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Facades\SessionService;

class PreguntaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::guard('usuario')->check() || !Auth::guard('usuario')->user()->isSisAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'Evaluacion_id' => 'required|exists:Evaluacion,id',
            'texto'         => 'required|string|max:1000',
            'codigo'        => 'required|string|max:100',
        ]);

        Pregunta::create([
            'Evaluacion_id' => $request->Evaluacion_id,
            'texto'         => $request->texto,
            'codigo'        => $request->codigo,
            'fecha'         => now(),
            'bloqueado'     => 0,
        ]);

        SessionService::success('Pregunta', 'Pregunta creada correctamente.');
        return redirect()->route('evaluacion.evaluacion.show', $request->Evaluacion_id);
    }

    public function edit(Pregunta $pregunta)
    {
        $pregunta->load(['evaluacion', 'alternativas' => fn($q) => $q->orderBy('id')]);
        return view('evaluacion.pregunta.edit', compact('pregunta'));
    }

    public function update(Request $request, Pregunta $pregunta)
    {
        $request->validate([
            'texto'     => 'required|string|max:1000',
            'codigo'    => 'required|string|max:100',
            'bloqueado' => 'nullable|boolean',
        ]);

        $pregunta->update([
            'texto'     => $request->texto,
            'codigo'    => $request->codigo,
            'bloqueado' => $request->boolean('bloqueado') ? 1 : 0,
        ]);

        SessionService::success('Pregunta', 'Pregunta actualizada correctamente.');
        return redirect()->route('evaluacion.evaluacion.show', $pregunta->Evaluacion_id);
    }

    public function destroy(Pregunta $pregunta)
    {
        $evaluacionId = $pregunta->Evaluacion_id;
        $nuevoEstado  = $pregunta->bloqueado ? 0 : 1;
        $mensaje      = $nuevoEstado ? 'bloqueada' : 'desbloqueada';

        $pregunta->update(['bloqueado' => $nuevoEstado]);

        SessionService::success('Pregunta', "Pregunta {$mensaje} correctamente.");
        return redirect()->route('evaluacion.evaluacion.show', $evaluacionId);
    }
}