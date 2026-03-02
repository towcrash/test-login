<?php

namespace App\Http\Controllers\Recurso;

use App\Http\Controllers\Controller;
use App\Models\Recurso\Recurso;
use App\Models\Recurso\TipoRecurso;
use App\Models\Documento\Documento;
use App\Models\Documento\Disco;
use App\Models\Evaluacion\Evaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Facades\SessionService;

class RecursoController extends Controller
{
    private const DISK = 'recursos';

    public function store(Request $request)
    {
        $this->autorizarSisAdmin();

        $request->validate([
            'Evaluacion_id'  => 'required|exists:Evaluacion,id',
            'TipoRecurso_id' => 'required|exists:TipoRecurso,id',
            'nombre'         => 'required|string|max:255',
            'descripcion'    => 'nullable|string',
            'archivo'        => 'required|file|max:102400',
        ]);

        $tipoRecurso = TipoRecurso::findOrFail($request->TipoRecurso_id);
        $evaluacion  = Evaluacion::findOrFail($request->Evaluacion_id);

        $file      = $request->file('archivo');
        $extension = $file->getClientOriginalExtension();
        $basename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $sid = $evaluacion->sid ?? Str::slug($evaluacion->nombre);

        $timestamp     = now()->format('ymdHi');
        $tipoSlug      = Str::slug($tipoRecurso->nombre);
        $nombreArchivo = $timestamp . '_' . $tipoSlug . '_' . $basename . '.' . $extension;

        $path = $file->storeAs($sid, $nombreArchivo, self::DISK);

        if (!$path) {
            return back()
                ->withInput()
                ->withErrors(['archivo' => 'No se pudo guardar el archivo. Verifica la configuracion del disco "' . self::DISK . '" en filesystems.php.']);
        }

        $disco = Disco::where('nombre', self::DISK)->first();

        $documento = Documento::create([
            'Disco_id'  => 1,
            'nombre'    => $nombreArchivo,
        ]);

        Recurso::create([
            'TipoRecurso_id' => $tipoRecurso->id,
            'Evaluacion_id'  => $evaluacion->id,
            'Documento_id'   => $documento->id,
            'Usuario_id'     => Auth::id(),
            'nombre'         => $request->nombre,
            'descripcion'    => $request->descripcion,
        ]);

        SessionService::success('Recurso', 'Recurso subido correctamente.');
        return redirect()->route('evaluacion.evaluacion.show', $evaluacion);
    }

    public function update(Request $request, Recurso $recurso)
    {
        $this->autorizarSisAdmin();

        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $recurso->update([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        SessionService::success('Recurso', 'Recurso actualizado correctamente.');
        return redirect()->route('evaluacion.evaluacion.show', $recurso->evaluacion);
    }

    public function download(Recurso $recurso)
    {
        if ($recurso->bloqueado) {
            abort(403, 'Este recurso no esta disponible.');
        }

        $recurso->loadMissing([
            'documento.disco',
            'evaluacion',
            'tipoRecurso',
        ]);

        $documento = $recurso->documento;
        $disco     = $documento->disco;
        $diskName  = $disco->nombre;

        $sid  = $recurso->evaluacion->sid ?? Str::slug($recurso->evaluacion->nombre);
        $path = $sid . '/' . $documento->nombre;

        if (!Storage::disk($diskName)->exists($path)) {
            abort(404, 'El archivo no existe en el disco "' . $diskName . '" con la ruta: ' . $path);
        }

        $recurso->usuarios()->attach(Auth::id());
        
        $extension      = pathinfo($documento->nombre, PATHINFO_EXTENSION);
        $nombreDescarga = $recurso->nombre . ($extension ? '.' . $extension : '');

        return Storage::disk($diskName)->download($path, $nombreDescarga);
    }

    public function destroy(Recurso $recurso)
    {
        $this->autorizarSisAdmin();

        $nuevoEstado = $recurso->bloqueado ? 0 : 1;
        $mensaje     = $nuevoEstado ? 'bloqueado' : 'desbloqueado';

        $recurso->update(['bloqueado' => $nuevoEstado]);

        SessionService::success('Recurso', "Recurso {$mensaje} correctamente.");
        return back();
    }

    private function autorizarSisAdmin(): void
    {
        if (!Auth::user()->isSisAdmin()) {
            abort(403, 'Solo un administrador puede gestionar recursos.');
        }
    }
}