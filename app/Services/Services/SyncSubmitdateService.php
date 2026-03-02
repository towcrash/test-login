<?php

namespace App\Services\Services;

use App\Services\Facades\EvaluacionPdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SyncSubmitdateService
{
    private const DISCO_ID = 2;

    public function syncResponse(string|int $sid, int $responseId): array
    {
        $tabla = "lime_responses_{$sid}";

        if (!Schema::connection('survey')->hasTable($tabla)) {
            throw new \RuntimeException("La tabla Survey \"{$tabla}\" no existe.");
        }

        $response = DB::connection('survey')
            ->table($tabla)
            ->where('id', $responseId)
            ->select('token', 'submitdate')
            ->first();

        if (!$response) {
            throw new \RuntimeException("No se encontró el response [{$responseId}] en {$tabla}.");
        }

        if (!$response->submitdate) {
            throw new \RuntimeException("El response [{$responseId}] aún no tiene submitdate.");
        }

        $evaluacion = $this->getEvaluacion($sid);

        return $this->procesarToken(
            sid:         $sid,
            token:       $response->token,
            submitdate:  $response->submitdate,
            evaluacion:  $evaluacion,
            responseId:  $responseId,
        );
    }

    private function procesarToken(
        string|int $sid,
        string     $token,
        string     $submitdate,
        object     $evaluacion,
        ?int       $responseId = null,
    ): array {
        try {
            // Verificar banderas de la evaluación
            $esPermanente  = (bool) ($evaluacion->permanent    ?? false);
            $esByEvaluador = (bool) ($evaluacion->byEvaluador ?? false);
            
            // ── 1. Actualizar Aplicacion ─────
            $countApp = 0;
            if ($esByEvaluador) {
                if ($esPermanente) {
                    $aplicacionExistente = DB::table('Aplicacion')
                        ->where('token', $token)
                        ->first();
                    
                    if ($aplicacionExistente) {
                        $fechaActual    = strtotime($submitdate);
                        $fechaExistente = $aplicacionExistente->submitdate ? strtotime($aplicacionExistente->submitdate) : 0;
                        
                        if ($fechaActual > $fechaExistente) {
                            $countApp = DB::table('Aplicacion')
                                ->where('token', $token)
                                ->update(['submitdate' => $submitdate]);
                        }
                    }
                } else {
                    $aplicacionExistente = DB::table('Aplicacion')
                        ->where('token', $token)
                        ->first();

                    if ($aplicacionExistente) {
                        $fechaActual    = strtotime($submitdate);
                        $fechaExistente = $aplicacionExistente->submitdate ? strtotime($aplicacionExistente->submitdate) : 0;

                        if ($fechaActual > $fechaExistente) {
                            $countApp = DB::table('Aplicacion')
                                ->where('token', $token)
                                ->update(['submitdate' => $submitdate]);
                        }
                    }
                }
            }

            // ── 2. Actualizar Colaborador_Evaluacion según el tipo de evaluación ──
            $countPivot = 0;
            if ($esPermanente) {
                $pivotExistente = DB::table('Colaborador_Evaluacion')
                    ->where('token', $token)
                    ->first();
                
                if ($pivotExistente) {
                    $fechaActual = strtotime($submitdate);
                    $fechaExistente = $pivotExistente->submitdate ? strtotime($pivotExistente->submitdate) : 0;
                    
                    if ($fechaActual > $fechaExistente) {
                        $countPivot = DB::table('Colaborador_Evaluacion')
                            ->where('token', $token)
                            ->update(['submitdate' => $submitdate]);
                    }
                }
            } else {
                $pivotExistente = DB::table('Colaborador_Evaluacion')
                    ->where('token', $token)
                    ->first();

                if ($pivotExistente) {
                    $fechaActual    = strtotime($submitdate);
                    $fechaExistente = $pivotExistente->submitdate ? strtotime($pivotExistente->submitdate) : 0;

                    if ($fechaActual > $fechaExistente) {
                        $countPivot = DB::table('Colaborador_Evaluacion')
                            ->where('token', $token)
                            ->update(['submitdate' => $submitdate]);
                    }
                }
            }

            // ── 3. Generar PDF si hubo alguna actualización ─────
            $pdfGenerado = false;
            if ($countApp > 0 || $countPivot > 0) {
                $resultado = EvaluacionPdfService::generate(
                    sid:          $sid,
                    token:        $token,
                    evaluacionId: $evaluacion->id,
                    submitdate:   $submitdate,
                    responseId:   $responseId,
                );

                if ($resultado['bytes'] !== '') {
                    $this->guardarPdf(
                        bytes:         $resultado['bytes'],
                        sid:           $sid,
                        submitdate:    $submitdate,
                        contratista:   $resultado['contratista'],
                        colaborador:   $resultado['colaborador'],
                        colaboradorId: $resultado['colaborador_id'],
                        porcentaje:    $esPermanente ? null : (float) $resultado['porcentaje'],
                        esPermanente:  $esPermanente,
                        token:         $token,
                    );
                    $pdfGenerado = true;
                }
            }

            return [
                'actualizado_aplicacion' => $countApp   > 0,
                'actualizado_pivot'      => $countPivot > 0,
                'pdf_generado'           => $pdfGenerado,
                'es_permanente'          => $esPermanente,
                'errores'                => [],
            ];

        } catch (\Throwable $e) {
            return [
                'actualizado_aplicacion' => false,
                'actualizado_pivot'      => false,
                'pdf_generado'           => false,
                'es_permanente'          => false,
                'errores'                => ["Token [{$token}]: " . $e->getMessage()],
            ];
        }
    }

    private function getEvaluacion(string|int $sid): object
    {
        $evaluacion = DB::table('Evaluacion')
            ->where('sid', $sid)
            ->where('bloqueado', 0)
            ->first();

        if (!$evaluacion) {
            throw new \RuntimeException("No se encontró una Evaluación activa con SID {$sid}.");
        }

        return $evaluacion;
    }

    private function guardarPdf(
        string     $bytes,
        string|int $sid,
        string     $submitdate,
        string     $contratista,
        string     $colaborador,
        ?int       $colaboradorId,
        ?float     $porcentaje = null,
        bool       $esPermanente = false,
        string     $token = '',
    ): void {
        $contratistaSafe = $this->sanitizeName($contratista);
        $colaboradorSafe = $this->sanitizeName($colaborador);
        $timestamp       = \Carbon\Carbon::parse($submitdate)->format('YmdHi');
        
        $nombreArchivo = "{$timestamp}_{$colaboradorSafe}.pdf";
        
        $rutaRelativa    = "{$contratistaSafe}/{$sid}/{$nombreArchivo}";

        Storage::disk('evaluaciones')->put($rutaRelativa, $bytes);

        // Siempre crear un nuevo documento
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
                'pAprobacion'    => $porcentaje,
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