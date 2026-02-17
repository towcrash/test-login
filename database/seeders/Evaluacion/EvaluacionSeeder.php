<?php

namespace Database\Seeders\Evaluacion;

use App\Models\Evaluacion\Evaluacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EvaluacionSeeder extends Seeder
{
	public function run(): void
	{
		Evaluacion::truncate();

		$evaluaciones = [
			[
				'nombre'      => 'Evaluación de Seguridad Industrial',
				'descripcion' => 'Evaluación completa de procedimientos de seguridad en el trabajo',
				'sid'         => Str::random(10),
				'byEvaluador' => 'SI',
				'permanent'   => 1,
				'fecha'       => now(),
			],
			[
				'nombre'      => 'Evaluación de Calidad de Obra',
				'descripcion' => 'Control de calidad en procesos constructivos',
				'sid'         => Str::random(10),
				'byEvaluador' => 'SI',
				'permanent'   => 1,
				'fecha'       => now(),
			],
			[
				'nombre'      => 'Evaluación de Medio Ambiente',
				'descripcion' => 'Cumplimiento de normas medioambientales',
				'sid'         => Str::random(10),
				'byEvaluador' => 'NO',
				'permanent'   => 0,
				'fecha'       => now(),
			],
			[
				'nombre'      => 'Evaluación de Desempeño',
				'descripcion' => 'Evaluación del desempeño del personal',
				'sid'         => Str::random(10),
				'byEvaluador' => 'SI',
				'permanent'   => 1,
				'fecha'       => now(),
			],
		];

		foreach ($evaluaciones as $evaluacion) {
			Evaluacion::create($evaluacion);
		}
	}
}