<?php

namespace App\Console\Commands\Herramientas;

use App\Services\Facades\SyncSubmitdateService;
use Illuminate\Console\Command;

class SyncSubmitdate extends Command
{
    protected $signature = 'EPR:H:syncsubmitdate
                            {sid         : SID de la encuesta en LimeSurvey}
                            {response_id : ID del registro en lime_responses_{sid}}';

    protected $description = 'Sincroniza un response específico desde LimeSurvey y genera su PDF de informe';

    public function handle(): int
    {
        $sid        = $this->argument('sid');
        $responseId = (int) $this->argument('response_id');

        $this->info("Sincronizando response [{$responseId}] para SID: {$sid} …");

        try {
            $resultado = SyncSubmitdateService::syncResponse(
                sid:        $sid,
                responseId: $responseId,
            );
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info("Aplicacion actualizada         : " . ($resultado['actualizado_aplicacion'] ? 'Sí' : 'No'));
        $this->info("Colaborador_Evaluacion updated : " . ($resultado['actualizado_pivot']      ? 'Sí' : 'No'));
        $this->info("PDF generado                   : " . ($resultado['pdf_generado']           ? 'Sí' : 'No'));

        if (!empty($resultado['errores'])) {
            $this->warn('Errores:');
            foreach ($resultado['errores'] as $err) {
                $this->warn("  • {$err}");
            }
            return self::FAILURE;
        }

        $this->info('Sincronización completada.');
        return self::SUCCESS;
    }
}