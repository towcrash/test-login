<?php

namespace App\Services\Services;

use App\Services\Facades\PdfGeneratorService;
use Illuminate\Support\Facades\DB;

class EvaluacionPdfService2
{
    private const COL_PREGUNTA    = 255;
    private const COL_ESPERADA    = 105;
    private const COL_COLABORADOR = 100;
    private const COL_RESULTADO   =  62;

    private const RESULTADO_CELL_X = 505;

    public function generate(
        string|int $sid,
        string     $token,
        int        $evaluacionId,
        string     $submitdate
    ): array {
        $colaboradorInfo = $this->getColaboradorInfo($token);

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

        if ($preguntas->isEmpty()) {
            return $empty;
        }

        $limeQuestions = DB::connection('survey')
            ->table('lime_questions')
            ->where('sid', $sid)
            ->where(function ($q) {
                $q->whereNull('parent_qid')->orWhere('parent_qid', 0);
            })
            ->select('qid', 'title')
            ->get()
            ->keyBy('title');

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

        $respuestaRow = DB::connection('survey')
            ->table("lime_responses_{$sid}")
            ->where('token', $token)
            ->first();

        if (!$respuestaRow) {
            return $empty;
        }

        $respuestaArr = (array) $respuestaRow;

        $filas          = [];
        $totalPreguntas = 0;
        $correctas      = 0;

        foreach ($preguntas as $pregunta) {
            $limeQ = $limeQuestions->get($pregunta->codigo);
            if (!$limeQ) {
                continue;
            }

            $columna    = 'Q' . $limeQ->qid;
            $codigoDado = (
                array_key_exists($columna, $respuestaArr) &&
                $respuestaArr[$columna] !== '' &&
                $respuestaArr[$columna] !== null
            ) ? (string) $respuestaArr[$columna] : null;

            $answersParaEstaPregunta = $limeAnswers->get($limeQ->qid);
            if ($codigoDado !== null && $answersParaEstaPregunta?->has($codigoDado)) {
                $dadaTexto = $answersParaEstaPregunta->get($codigoDado)->answer;
            } else {
                $dadaTexto = $codigoDado ?? '—';
            }

            $altEsperada = DB::table('Alternativa')
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

            $totalPreguntas++;
            if ($correcta) $correctas++;

            $filas[] = [
                'pregunta' => $pregunta->texto,
                'esperada' => $esperadaTexto,
                'dada'     => $dadaTexto,
                'correcta' => $correcta,
            ];
        }

        $porcentaje = $totalPreguntas > 0
            ? round(($correctas / $totalPreguntas) * 100, 1)
            : 0;

        $bytes = $this->buildPdf(
            colaboradorInfo: $colaboradorInfo,
            submitdate:      $submitdate,
            filas:           $filas,
            correctas:       $correctas,
            total:           $totalPreguntas,
            porcentaje:      $porcentaje,
        );

        return [
            'bytes'          => $bytes,
            'contratista'    => $colaboradorInfo->contratista_nombre,
            'colaborador'    => $colaboradorInfo->colaborador_nombre,
            'colaborador_id' => $colaboradorInfo->colaborador_id,
        ];
    }


    private function getColaboradorInfo(string $token): object
    {
        $row = DB::table('Aplicacion as a')
            ->join('Colaborador as col', 'col.id', '=', 'a.Colaborador_id')
            ->join('Usuario as u',       'u.id',   '=', 'col.Usuario_id')
            ->join('Contratista as con', 'con.id', '=', 'col.Contratista_id')
            ->where('a.token', $token)
            ->select(
                'col.id     as colaborador_id',
                'u.nombre   as colaborador_nombre',
                'con.nombre as contratista_nombre',
            )
            ->first();

        if ($row) return $row;

        $row = DB::table('Colaborador_Evaluacion as ce')
            ->join('Colaborador as col', 'col.id', '=', 'ce.Colaborador_id')
            ->join('Usuario as u',       'u.id',   '=', 'col.Usuario_id')
            ->join('Contratista as con', 'con.id', '=', 'col.Contratista_id')
            ->where('ce.token', $token)
            ->select(
                'col.id     as colaborador_id',
                'u.nombre   as colaborador_nombre',
                'con.nombre as contratista_nombre',
            )
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
        array  $filas,
        int    $correctas,
        int    $total,
        float  $porcentaje,
    ): string {
        PdfGeneratorService::reset();

        $logoPath = storage_path('app/public/logos/logo_epr_min.png');
        PdfGeneratorService::setWatermark($logoPath, opacity: 0.5, scale: 0.18);

        PdfGeneratorService::addPage();

        PdfGeneratorService::line('INFORME DE EVALUACIÓN', 16, true);
        PdfGeneratorService::spacer(4);
        PdfGeneratorService::line('Contratista   : ' . $colaboradorInfo->contratista_nombre);
        PdfGeneratorService::line('Colaborador   : ' . $colaboradorInfo->colaborador_nombre);
        PdfGeneratorService::line('Fecha         : ' . $submitdate);
        PdfGeneratorService::spacer(8);
        PdfGeneratorService::hLine();
        PdfGeneratorService::spacer(6);

        PdfGeneratorService::tableHeaderRow([
            ['text' => 'Pregunta',          'width' => self::COL_PREGUNTA],
            ['text' => 'Resp. Esperada',    'width' => self::COL_ESPERADA],
            ['text' => 'Resp. Colaborador', 'width' => self::COL_COLABORADOR],
            ['text' => 'Resultado',         'width' => self::COL_RESULTADO],
        ]);

        foreach ($filas as $fila) {
            $yCentroFila = PdfGeneratorService::tableDataRow([
                ['text' => $fila['pregunta'], 'width' => self::COL_PREGUNTA],
                ['text' => $fila['esperada'], 'width' => self::COL_ESPERADA],
                ['text' => $fila['dada'],     'width' => self::COL_COLABORADOR],
                ['text' => '',                'width' => self::COL_RESULTADO],
            ]);

            $cellYBottom = PdfGeneratorService::getCursorY();
            $cellHeight  = ($yCentroFila - $cellYBottom) * 2;

            PdfGeneratorService::checkMarkInCell(
                cellX:   self::RESULTADO_CELL_X,
                cellY:   $cellYBottom,
                cellW:   self::COL_RESULTADO,
                cellH:   $cellHeight,
                correct: $fila['correcta'],
            );
        }

        PdfGeneratorService::spacer(12);
        PdfGeneratorService::hLine();
        PdfGeneratorService::spacer(6);
        PdfGeneratorService::line('Resumen de resultados', 12, true);
        PdfGeneratorService::spacer(4);
        PdfGeneratorService::line("Respuestas correctas : {$correctas} de {$total}");
        PdfGeneratorService::line("Porcentaje obtenido  : {$porcentaje}%", 11, true);

        return PdfGeneratorService::output();
    }
}