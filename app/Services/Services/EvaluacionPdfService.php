<?php

namespace App\Services\Services;

use App\Models\Contratista\Colaborador;
use App\Models\Documento\Documento;
use App\Models\Documento\Disco;
use App\Services\Facades\PdfGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EvaluacionPdfService
{
    public function generate(
        string|int $sid,
        string     $token,
        int        $evaluacionId,
        string     $submitdate,
        ?int       $responseId = null,
    ): array {
        $colaboradorInfo = $this->getColaboradorInfo($token);

        // Obtener nombre de la evaluación
        $evaluacion = DB::table('Evaluacion')
            ->where('id', $evaluacionId)
            ->select('nombre', 'permanent')
            ->first();
        $evaluacionNombre = $evaluacion?->nombre    ?? 'Evaluación';
        $permanent        = (bool) ($evaluacion?->permanent ?? false);

        $preguntas = DB::table('Pregunta')
            ->where('Evaluacion_id', $evaluacionId)
            ->where('bloqueado', 0)
            ->orderBy('id')
            ->get();

        $empty = [
            'bytes'          => '',
            'contratista'    => $colaboradorInfo->contratista_nombre,
            'colaborador'    => $colaboradorInfo->colaborador_nombre,
            'colaborador_id' => $colaboradorInfo->colaborador_id,
        ];

        if ($preguntas->isEmpty()) return $empty;

        // Mapa title → qid desde lime_questions
        $limeQuestions = DB::connection('survey')
            ->table('lime_questions')
            ->where('sid', $sid)
            ->where(function ($q) {
                $q->whereNull('parent_qid')->orWhere('parent_qid', 0);
            })
            ->select('qid', 'title')
            ->get()
            ->keyBy('title');

        // Textos de respuestas desde lime_answers + lime_answer_l10ns
        $qids = $limeQuestions->pluck('qid')->toArray();
        $limeAnswers = DB::connection('survey')
            ->table('lime_answers as a')
            ->join('lime_answer_l10ns as l', 'l.aid', '=', 'a.aid')
            ->whereIn('a.qid', $qids)
            ->where('l.language', 'es')
            ->select('a.qid', 'a.code', 'l.answer')
            ->get()
            ->groupBy('qid')
            ->map(fn($group) => $group->keyBy('code'));

        // Respuestas del colaborador
        $respuestasTable = DB::connection('survey')
            ->table("lime_responses_{$sid}");

        if ($responseId !== null) {
            // Usar específicamente el response recibido (caso SyncSubmitdateService::syncResponse)
            $respuestaRow = $respuestasTable
                ->where('id', $responseId)
                ->first();
        } else {
            // Fallback: buscar por token, priorizando la fila con la misma submitdate
            $respuestaRow = $respuestasTable
                ->where('token', $token)
                ->when($submitdate, function ($q) use ($submitdate) {
                    return $q->where('submitdate', $submitdate);
                })
                ->orderByDesc('submitdate')
                ->first();
        }

        if (!$respuestaRow) return $empty;
        $respuestaArr = (array) $respuestaRow;

        // Construir filas
        $filas          = [];
        $totalPreguntas = 0;
        $correctas      = 0;

        foreach ($preguntas as $pregunta) {
            $limeQ = $limeQuestions->get($pregunta->codigo);
            if (!$limeQ) continue;

            $columna    = 'Q' . $limeQ->qid;
            $codigoDado = (
                array_key_exists($columna, $respuestaArr) &&
                $respuestaArr[$columna] !== '' &&
                $respuestaArr[$columna] !== null
            ) ? (string) $respuestaArr[$columna] : null;

            // Texto de la respuesta dada desde lime_answers
            $answersQ  = $limeAnswers->get($limeQ->qid);
            $dadaTexto = match (true) {
                // Respuesta vacía: usar guion simple ASCII para evitar caracteres raros en el PDF
                $codigoDado === null                        => '-',
                $answersQ?->has($codigoDado) === true       => $answersQ->get($codigoDado)->answer,
                default                                     => $codigoDado,
            };

            // Alternativa esperada — omitida en evaluaciones permanentes
            $esperadaTexto  = '-';
            $correcta       = false;
            if (!$permanent) {
                $altEsperada    = DB::table('Alternativa')
                    ->where('Pregunta_id', $pregunta->id)
                    ->where('bloqueado', 0)
                    ->orderBy('id')
                    ->first();
                $esperadaCodigo = $altEsperada?->codigo ?? null;
                $esperadaTexto  = $altEsperada?->texto  ?? '—';
                $correcta = (
                    $codigoDado     !== null &&
                    $esperadaCodigo !== null &&
                    mb_strtolower($codigoDado) === mb_strtolower($esperadaCodigo)
                );
            }

            $totalPreguntas++;
            if (!$permanent && $correcta) $correctas++;

            $filas[] = [
                'pregunta' => $pregunta->texto,
                'esperada' => $esperadaTexto,
                'dada'     => $dadaTexto,
                'correcta' => $correcta,
            ];
        }

        $porcentaje = (!$permanent && $totalPreguntas > 0)
            ? round(($correctas / $totalPreguntas) * 100, 1)
            : 0;

        $bytes = $this->buildPdf(
            colaboradorInfo:  $colaboradorInfo,
            submitdate:       $submitdate,
            evaluacionNombre: $evaluacionNombre,
            filas:            $filas,
            correctas:        $correctas,
            total:            $totalPreguntas,
            porcentaje:       $porcentaje,
            permanent:        $permanent,
        );

        return [
            'bytes'          => $bytes,
            'contratista'    => $colaboradorInfo->contratista_nombre,
            'colaborador'    => $colaboradorInfo->colaborador_nombre,
            'colaborador_id' => $colaboradorInfo->colaborador_id,
            'porcentaje'     => $porcentaje,
        ];
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    private function getColaboradorInfo(string $token): object
    {
        $row = DB::table('Aplicacion as a')
            ->join('Colaborador as col', 'col.id', '=', 'a.Colaborador_id')
            ->join('Usuario as u',       'u.id',   '=', 'col.Usuario_id')
            ->join('Contratista as con', 'con.id', '=', 'col.Contratista_id')
            ->where('a.token', $token)
            ->select('col.id as colaborador_id', 'u.nombre as colaborador_nombre', 'con.nombre as contratista_nombre')
            ->first();

        if ($row) return $row;

        $row = DB::table('Colaborador_Evaluacion as ce')
            ->join('Colaborador as col', 'col.id', '=', 'ce.Colaborador_id')
            ->join('Usuario as u',       'u.id',   '=', 'col.Usuario_id')
            ->join('Contratista as con', 'con.id', '=', 'col.Contratista_id')
            ->where('ce.token', $token)
            ->select('col.id as colaborador_id', 'u.nombre as colaborador_nombre', 'con.nombre as contratista_nombre')
            ->first();

        return $row ?? (object) [
            'colaborador_id'     => null,
            'colaborador_nombre' => 'Desconocido',
            'contratista_nombre' => 'Desconocido',
        ];
    }

    private function buildPdf(
        object $colaboradorInfo,
        string $submitdate,
        string $evaluacionNombre,
        array  $filas,
        int    $correctas,
        int    $total,
        float  $porcentaje,
        bool   $permanent = false,
    ): string {
        PdfGeneratorService::reset();

        $logoWmPath = storage_path('app/public/logos/logo_epr_watermark.png');
        PdfGeneratorService::setWatermark($logoWmPath, opacity: 0.05, scale: 0.70);

        $logoHeaderPath = storage_path('app/public/logos/logo_epr.png');
        PdfGeneratorService::setHeaderLogo($logoHeaderPath, maxW: 150);

        $logosPath = storage_path('app/public/logos/logos_terceros/');

        PdfGeneratorService::addFooterLogo($logosPath . 'logo_terceros_1.png');
        PdfGeneratorService::addFooterLogo($logosPath . 'logo_terceros_2.png');
        PdfGeneratorService::addFooterLogo($logosPath . 'logo_terceros_3.png');
        PdfGeneratorService::addFooterLogo($logosPath . 'logo_terceros_4.png');
        PdfGeneratorService::addFooterLogo($logosPath . 'logo_terceros_5.png');
        PdfGeneratorService::addPage();

        // ── Encabezado ────────────────────────────────────────────────────
        // tabX=68: columna donde arranca el ":" — alineado por la label más larga ("Contratista")
        PdfGeneratorService::line('INFORME DE EVALUACIÓN', 16, true);
        PdfGeneratorService::spacer(4);
        PdfGeneratorService::tabbedLine('Evaluación',  $evaluacionNombre,                  68);
        PdfGeneratorService::tabbedLine('Contratista', $colaboradorInfo->contratista_nombre, 68);
        PdfGeneratorService::tabbedLine('Colaborador', $colaboradorInfo->colaborador_nombre, 68);
        PdfGeneratorService::tabbedLine('Fecha',       $submitdate,                          68);
        PdfGeneratorService::spacer(10);
        PdfGeneratorService::hLine();
        PdfGeneratorService::spacer(8);

        // ── Tarjetas de preguntas ─────────────────────────────────────────
        foreach ($filas as $fila) {
            PdfGeneratorService::questionCard(
                pregunta:  $fila['pregunta'],
                esperada:  $fila['esperada'],
                dada:      $fila['dada'],
                correcta:  $fila['correcta'],
                permanent: $permanent,
            );
        }

        // ── Zona final ────────────────────────────────────────────────────
        PdfGeneratorService::spacer(12);
        PdfGeneratorService::hLine();
        PdfGeneratorService::spacer(6);

        if (!$permanent) {
            // Resumen a la izquierda y leyenda a la derecha, alineados verticalmente
            $yBase   = PdfGeneratorService::getCursorY();
            $legendX = \App\Services\Services\PdfGeneratorService::MARGIN_LEFT + 280;

            // Resumen (columna izquierda)
            PdfGeneratorService::line('Resumen de resultados', 12, true);
            PdfGeneratorService::spacer(4);
            PdfGeneratorService::tabbedLine('Respuestas correctas', "{$correctas} de {$total}", 122);
            PdfGeneratorService::tabbedLine('Porcentaje obtenido',  "{$porcentaje}%",            122, 0, true);

            // Leyenda (columna derecha) usando la misma altura inicial
            PdfGeneratorService::legend($legendX, $yBase, false);
        } else {
            // Evaluación permanente: solo leyenda, inmediatamente bajo la línea separadora
            $legendX = \App\Services\Services\PdfGeneratorService::MARGIN_LEFT + 280;
            PdfGeneratorService::legend($legendX, PdfGeneratorService::getCursorY(), true);
        }

        return PdfGeneratorService::output();
    }
    
    public function registrarPorcentaje(
        int   $colaboradorId,
        int   $documentoId,
        float $porcentaje,
    ): void {
        DB::table('Colaborador_Documento')
            ->where('Colaborador_id', $colaboradorId)
            ->where('Documento_id',   $documentoId)
            ->update(['pAprobacion' => $porcentaje]);
    }

    /**
     * Crea el Documento, guarda el PDF en disco y registra el porcentaje.
     * Usar solo si el código externo NO crea el Documento por su cuenta.
     */
    public function saveDocumento(
        int    $colaboradorId,
        string $colaboradorNombre,
        string $bytes,
        float  $porcentaje,
    ): ?Documento {
        if (empty($bytes)) return null;

        $disco = Disco::find(2); // disco 'evaluaciones'
        if (!$disco) return null;

        $timestamp = now()->format('Y_m_d_His');
        $nombre    = $timestamp . '_' . str_replace(' ', '_', $colaboradorNombre) . '.pdf';
        $ruta      = 'Contratista/SID/' . $nombre;
        Storage::disk('public')->put($ruta, $bytes);

        $documento = Documento::create([
            'Disco_id'  => $disco->id,
            'nombre'    => $nombre,
            'fecha'     => now(),
            'bloqueado' => 0,
        ]);

        DB::table('Colaborador_Documento')->insert([
            'Colaborador_id'        => $colaboradorId,
            'Documento_id'          => $documento->id,
            'pAprobacion' => $porcentaje,
            'bloqueado'             => 0,
        ]);

        return $documento;
    }
}