<?php

namespace Database\Seeders\Aplicacion;

use App\Models\Aplicacion\Aplicacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AplicacionSeeder extends Seeder
{
	public function run(): void
	{
		Aplicacion::truncate();

		$aplicaciones = [
			[
				'Evaluador_id'   => 1,
				'Evaluacion_id'  => 1,
				'Colaborador_id' => 1,
				'token'          => Str::random(32),
				'fecha'          => now(),
			],
			[
				'Evaluador_id'   => 1,
				'Evaluacion_id'  => 1,
				'Colaborador_id' => 2,
				'token'          => Str::random(32),
				'fecha'          => now(),
			],
			[
				'Evaluador_id'   => 2,
				'Evaluacion_id'  => 2,
				'Colaborador_id' => 3,
				'token'          => Str::random(32),
				'fecha'          => now(),
			],
			[
				'Evaluador_id'   => 3,
				'Evaluacion_id'  => 3,
				'Colaborador_id' => 4,
				'token'          => Str::random(32),
				'fecha'          => now(),
			],
		];

		foreach ($aplicaciones as $aplicacion) {
			Aplicacion::create($aplicacion);
		}
	}
}