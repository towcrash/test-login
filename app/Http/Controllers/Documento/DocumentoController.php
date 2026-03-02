<?php

namespace App\Http\Controllers\Documento;

use App\Http\Controllers\Controller;
use App\Models\Documento\Documento;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    /**
     * Descarga el documento como archivo adjunto.
     */
    public function download(Documento $documento)
    {
        if ($documento->bloqueado) {
            abort(403, 'Este documento no está disponible.');
        }

        $documento->loadMissing('disco');

        $diskName       = $documento->disco->nombre;
        $path           = $documento->nombre;
        $nombreDescarga = basename($documento->nombre);

        if (!Storage::disk($diskName)->exists($path)) {
            abort(404, 'El archivo no existe en el disco "' . $diskName . '" con la ruta: ' . $path);
        }

        return Storage::disk($diskName)->download($path, $nombreDescarga);
    }

    /**
     * Sirve el documento inline para abrirlo en el visor del navegador (nueva pestaña).
     */
    public function view(Documento $documento)
    {
        if ($documento->bloqueado) {
            abort(403, 'Este documento no está disponible.');
        }

        $documento->loadMissing('disco');

        $diskName = $documento->disco->nombre;
        $path     = $documento->nombre;

        if (!Storage::disk($diskName)->exists($path)) {
            abort(404, 'El archivo no existe en el disco "' . $diskName . '" con la ruta: ' . $path);
        }

        $mime     = Storage::disk($diskName)->mimeType($path);
        $contents = Storage::disk($diskName)->get($path);

        return response($contents, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }
}