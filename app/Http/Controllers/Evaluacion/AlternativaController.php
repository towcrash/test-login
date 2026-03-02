<?php

namespace App\Http\Controllers\Evaluacion;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion\Alternativa;
use App\Models\Evaluacion\Pregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Facades\SessionService;

class AlternativaController extends Controller
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
            'Pregunta_id' => 'required|exists:Pregunta,id',
            'texto'       => 'required|string|max:500',
            'codigo'      => 'required|string|max:100',
        ]);

        Alternativa::create([
            'Pregunta_id' => $request->Pregunta_id,
            'texto'       => $request->texto,
            'codigo'      => $request->codigo,
            'fecha'       => now(),
            'bloqueado'   => 0,
        ]);

        SessionService::success('Alternativa', 'Alternativa creada correctamente.');
        return redirect()->route('evaluacion.pregunta.edit', $request->Pregunta_id);
    }

    public function update(Request $request, Alternativa $alternativa)
    {
        $request->validate([
            'texto'     => 'required|string|max:500',
            'codigo'    => 'required|string|max:100',
            'bloqueado' => 'nullable|boolean',
        ]);

        $alternativa->update([
            'texto'     => $request->texto,
            'codigo'    => $request->codigo,
            'bloqueado' => $request->boolean('bloqueado') ? 1 : 0,
        ]);

        SessionService::success('Alternativa', 'Alternativa actualizada correctamente.');
        return redirect()->route('evaluacion.pregunta.edit', $alternativa->Pregunta_id);
    }

    public function destroy(Alternativa $alternativa)
    {
        $preguntaId  = $alternativa->Pregunta_id;
        $nuevoEstado = $alternativa->bloqueado ? 0 : 1;
        $mensaje     = $nuevoEstado ? 'bloqueada' : 'desbloqueada';

        $alternativa->update(['bloqueado' => $nuevoEstado]);

        SessionService::success('Alternativa', "Alternativa {$mensaje} correctamente.");
        return redirect()->route('evaluacion.pregunta.edit', $preguntaId);
    }
}