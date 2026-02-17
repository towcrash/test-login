<?php

namespace Database\Seeders\Aplicacion;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Colaborador_EvaluacionSeeder extends Seeder
{
	public function run(): void
	{
		DB::table('Colaborador_Evaluacion')->truncate();

		$registros = [
			[
				'Colaborador_id' => 1,
				'Evaluacion_id'  => 1,
				'token'          => Str::random(32),
				'fecha'          => now(),
			],
			[
				'Colaborador_id' => 2,
				'Evaluacion_id'  => 1,
				'token'          => Str::random(32),
				'fecha'          => now(),
			],
			[
				'Colaborador_id' => 3,
				'Evaluacion_id'  => 2,
				'token'          => Str::random(32),
				'fecha'          => now(),
			],
			[
				'Colaborador_id' => 4,
				'Evaluacion_id'  => 3,
				'token'          => Str::random(32),
				'fecha'          => now(),
			],
		];

		foreach ($registros as $registro) {
			DB::table('Colaborador_Evaluacion')->insert($registro);
		}
	}
}