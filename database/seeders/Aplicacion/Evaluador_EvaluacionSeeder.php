<?php

namespace Database\Seeders\Aplicacion;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Evaluador_EvaluacionSeeder extends Seeder
{
	public function run(): void
	{
		DB::table('Evaluador_Evaluacion')->truncate();

		$registros = [
			[
				'Evaluador_id'  => 1,
				'Evaluacion_id' => 1,
				'Usuario_id'    => 1,
				'fecha'         => now(),
			],
			[
				'Evaluador_id'  => 1,
				'Evaluacion_id' => 2,
				'Usuario_id'    => 1,
				'fecha'         => now(),
			],
			[
				'Evaluador_id'  => 2,
				'Evaluacion_id' => 1,
				'Usuario_id'    => 2,
				'fecha'         => now(),
			],
			[
				'Evaluador_id'  => 3,
				'Evaluacion_id' => 3,
				'Usuario_id'    => 3,
				'fecha'         => now(),
			],
			[
				'Evaluador_id'  => 4,
				'Evaluacion_id' => 4,
				'Usuario_id'    => 4,
				'fecha'         => now(),
			],
		];

		foreach ($registros as $registro) {
			DB::table('Evaluador_Evaluacion')->insert($registro);
		}
	}
}