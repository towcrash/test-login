<?php

namespace App\Services\Services;

use App\Services\Facades\EvaluacionPdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SyncSubmitdateService2
{
    private const DISCO_ID = 2; 
    public function sync(string|int $sid): array
    {
        $tabla = "lime_responses_{$sid}";

        if (!Schema::connection('survey')->hasTable($tabla)) {
            throw new \RuntimeException("La tabla Survey \"{$tabla}\" no existe.");
        }

        $respuestas = DB::connection('survey')
            ->table($tabla)
            ->whereNotNull('submitdate')
            ->select('token', 'submitdate')
            ->get();

        if ($respuestas->isEmpty()) {
            return [
                'actualizados_aplicacion' => 0,
                'actualizados_pivot'      => 0,
                'pdfs_generados'          => 0,
                'errores'                 => [],
            ];
        }

        $evaluacion = DB::table('Evaluacion')
            ->where('sid', $sid)
            ->where('bloqueado', 0)
            ->first();

        if (!$evaluacion) {
            throw new \RuntimeException("No se encontró una Evaluación activa con SID {$sid}.");
        }

        $actualizadosAplicacion = 0;
        $actualizadosPivot      = 0;
        $pdfsGenerados          = 0;
        $errores                = [];

        foreach ($respuestas as $row) {
            $token      = $row->token;
            $submitdate = $row->submitdate;

            try {
                // ── 1. Actualizar Aplicacion ──────────────────────────────
                $countApp = DB::table('Aplicacion')
                    ->where('token', $token)
                    ->whereNull('submitdate')
                    ->update(['submitdate' => $submitdate]);

                $actualizadosAplicacion += $countApp;

                // ── 2. Actualizar Colaborador_Evaluacion ──────────────────
                $countPivot = DB::table('Colaborador_Evaluacion')
                    ->where('token', $token)
                    ->whereNull('submitdate')
                    ->update(['submitdate' => $submitdate]);

                $actualizadosPivot += $countPivot;

                // ── 3. Generar PDF ─────────────────────────────────────────
                if ($countApp > 0 || $countPivot > 0) {
                    $resultado = EvaluacionPdfService::generate(
                        sid:          $sid,
                        token:        $token,
                        evaluacionId: $evaluacion->id,
                        submitdate:   $submitdate,
                    );

                    if ($resultado['bytes'] !== '') {
                        $this->guardarPdf(
                            bytes:         $resultado['bytes'],
                            sid:           $sid,
                            submitdate:    $submitdate,
                            contratista:   $resultado['contratista'],
                            colaborador:   $resultado['colaborador'],
                            colaboradorId: $resultado['colaborador_id'],
                        );
                        $pdfsGenerados++;
                    }
                }

            } catch (\Throwable $e) {
                $errores[] = "Token [{$token}]: " . $e->getMessage();
            }
        }

        return [
            'actualizados_aplicacion' => $actualizadosAplicacion,
            'actualizados_pivot'      => $actualizadosPivot,
            'pdfs_generados'          => $pdfsGenerados,
            'errores'                 => $errores,
        ];
    }

    private function guardarPdf(
        string     $bytes,
        string|int $sid,
        string     $submitdate,
        string     $contratista,
        string     $colaborador,
        ?int       $colaboradorId,
    ): void {
        $contratistaSafe = $this->sanitizeName($contratista);
        $colaboradorSafe = $this->sanitizeName($colaborador);
        $timestamp       = \Carbon\Carbon::parse($submitdate)->format('YmdHi');
        $nombreArchivo   = "{$timestamp}_{$colaboradorSafe}.pdf";

        $rutaRelativa = "{$contratistaSafe}/{$sid}/{$nombreArchivo}";

        Storage::disk('evaluaciones')->put($rutaRelativa, $bytes);

        $documentoId = DB::table('Documento')->insertGetId([
            'Disco_id'  => self::DISCO_ID,
            'nombre'    => $rutaRelativa,   
            'fecha'     => now(),
            'bloqueado' => 0,
        ]);

        if ($colaboradorId !== null) {
            DB::table('Colaborador_Documento')->insert([
                'Colaborador_id' => $colaboradorId,
                'Documento_id'   => $documentoId,
                'bloqueado'      => 0,
            ]);
        }
    }

    private function sanitizeName(string $name): string
    {
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
        $name = str_replace(' ', '_', $name);
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '', $name);

        return $name ?: 'sin_nombre';
    }
}