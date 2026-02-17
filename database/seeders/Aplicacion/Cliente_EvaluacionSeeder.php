<?php

namespace Database\Seeders\Aplicacion;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Cliente_EvaluacionSeeder extends Seeder
{
	public function run(): void
	{
		DB::table('Cliente_Evaluacion')->truncate();

		$registros = [
			[
				'Cliente_id'    => 1,
				'Evaluacion_id' => 1,
				'Usuario_id'    => 1,
				'fecha'         => now(),
			],
			[
				'Cliente_id'    => 1,
				'Evaluacion_id' => 2,
				'Usuario_id'    => 1,
				'fecha'         => now(),
			],
			[
				'Cliente_id'    => 2,
				'Evaluacion_id' => 1,
				'Usuario_id'    => 3,
				'fecha'         => now(),
			],
			[
				'Cliente_id'    => 2,
				'Evaluacion_id' => 3,
				'Usuario_id'    => 3,
				'fecha'         => now(),
			],
			[
				'Cliente_id'    => 3,
				'Evaluacion_id' => 4,
				'Usuario_id'    => 4,
				'fecha'         => now(),
			],
		];

		foreach ($registros as $registro) {
			DB::table('Cliente_Evaluacion')->insert($registro);
		}
	}
}