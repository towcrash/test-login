<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario\Usuario;
use Illuminate\Support\Facades\DB;

class BloquearUsuariosExpirados extends Command
{
    protected $signature   = 'usuarios:bloquear-expirados';
    protected $description = 'Bloquea usuarios cuya vigencia ha expirado y propaga el bloqueo a sus relaciones';

    public function handle(): void
    {
        $expirados = Usuario::where('bloqueado', 0)
            ->whereNotNull('vigencia')
            ->where('vigencia', '<', now())
            ->get();

        if ($expirados->isEmpty()) {
            $this->info('No hay usuarios expirados pendientes de bloquear.');
            return;
        }

        foreach ($expirados as $usuario) {
            DB::transaction(function () use ($usuario) {

                $usuario->update(['bloqueado' => 1]);

                $this->bloquearRelaciones($usuario);
            });

            $this->info("Usuario bloqueado: [{$usuario->id}] {$usuario->nombre} (expiró: {$usuario->vigencia})");
        }

        $this->info("Total bloqueados: {$expirados->count()}");
    }

    private function bloquearRelaciones(Usuario $usuario): void
    {
        DB::table('Cliente_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        DB::table('Contratista_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        DB::table('Usuario_Rol')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        $evaluadorIds = DB::table('Evaluador')
            ->where('Usuario_id', $usuario->id)
            ->pluck('id');

        DB::table('Evaluador')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        if ($evaluadorIds->isNotEmpty()) {
            DB::table('Evaluador_Evaluacion')
                ->whereIn('Evaluador_id', $evaluadorIds)
                ->update(['bloqueado' => 1]);
        }

        $colaboradorIds = DB::table('Colaborador')
            ->where('Usuario_id', $usuario->id)
            ->pluck('id');

        DB::table('Colaborador')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);

        if ($colaboradorIds->isNotEmpty()) {
            DB::table('Colaborador_Evaluacion')
                ->whereIn('Colaborador_id', $colaboradorIds)
                ->update(['bloqueado' => 1]);
        }

        DB::table('Recurso_Usuario')
            ->where('Usuario_id', $usuario->id)
            ->update(['bloqueado' => 1]);
    }
}