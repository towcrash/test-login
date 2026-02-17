<?php

namespace Database\Seeders\Evaluacion;

use App\Models\Evaluacion\Pregunta;
use Illuminate\Database\Seeder;

class PreguntaSeeder extends Seeder
{
	public function run(): void
	{
		Pregunta::truncate();

		$preguntas = [
			// Preguntas para Evaluación 1
			[
				'Evaluacion_id' => 1,
				'texto'         => '¿El personal cuenta con todos los elementos de protección personal requeridos?',
				'codigo'        => 'SEG-001',
				'fecha'         => now(),
			],
			[
				'Evaluacion_id' => 1,
				'texto'         => '¿Se han realizado las charlas de seguridad correspondientes?',
				'codigo'        => 'SEG-002',
				'fecha'         => now(),
			],
			[
				'Evaluacion_id' => 1,
				'texto'         => '¿Existe señalización adecuada en las áreas de trabajo?',
				'codigo'        => 'SEG-003',
				'fecha'         => now(),
			],
			// Preguntas para Evaluación 2
			[
				'Evaluacion_id' => 2,
				'texto'         => '¿Los materiales utilizados cumplen con las especificaciones técnicas?',
				'codigo'        => 'CAL-001',
				'fecha'         => now(),
			],
			[
				'Evaluacion_id' => 2,
				'texto'         => '¿Se realizaron los ensayos de calidad requeridos?',
				'codigo'        => 'CAL-002',
				'fecha'         => now(),
			],
			// Preguntas para Evaluación 3
			[
				'Evaluacion_id' => 3,
				'texto'         => '¿Se está realizando correctamente la gestión de residuos?',
				'codigo'        => 'AMB-001',
				'fecha'         => now(),
			],
			[
				'Evaluacion_id' => 3,
				'texto'         => '¿Se están cumpliendo los procedimientos de control de emisiones?',
				'codigo'        => 'AMB-002',
				'fecha'         => now(),
			],
			// Preguntas para Evaluación 4
			[
				'Evaluacion_id' => 4,
				'texto'         => '¿El trabajador cumple con los objetivos asignados?',
				'codigo'        => 'DES-001',
				'fecha'         => now(),
			],
			[
				'Evaluacion_id' => 4,
				'texto'         => '¿Demuestra trabajo en equipo y colaboración?',
				'codigo'        => 'DES-002',
				'fecha'         => now(),
			],
		];

		foreach ($preguntas as $pregunta) {
			Pregunta::create($pregunta);
		}
	}
}