<?php

namespace Database\Seeders\Recurso;

use App\Models\Recurso\Recurso;
use Illuminate\Database\Seeder;

class RecursoSeeder extends Seeder
{
	public function run(): void
	{
		Recurso::truncate();

		$recursos = [
			[
				'TipoRecurso_id' => 1,
				'Evaluacion_id'  => 1,
				'Documento_id'   => 1,
				'Usuario_id'     => 1,
				'nombre'         => 'Tutorial de Seguridad Básica',
				'descripcion'    => 'Video tutorial sobre procedimientos de seguridad básica',
				'fecha'          => now(),
			],
			[
				'TipoRecurso_id' => 2,
				'Evaluacion_id'  => 1,
				'Documento_id'   => 2,
				'Usuario_id'     => 1,
				'nombre'         => 'Manual de EPP',
				'descripcion'    => 'Documento PDF con información sobre equipos de protección',
				'fecha'          => now(),
			],
			[
				'TipoRecurso_id' => 3,
				'Evaluacion_id'  => 2,
				'Documento_id'   => 3,
				'Usuario_id'     => 2,
				'nombre'         => 'Diagrama de Procedimientos',
				'descripcion'    => 'Imagen con diagrama de flujo de procedimientos',
				'fecha'          => now(),
			],
			[
				'TipoRecurso_id' => 4,
				'Evaluacion_id'  => 3,
				'Documento_id'   => 4,
				'Usuario_id'     => 3,
				'nombre'         => 'Checklist de Control',
				'descripcion'    => 'Planilla Excel con checklist de control ambiental',
				'fecha'          => now(),
			],
			[
				'TipoRecurso_id' => 1,
				'Evaluacion_id'  => 4,
				'Documento_id'   => 5,
				'Usuario_id'     => 4,
				'nombre'         => 'Video de Inducción',
				'descripcion'    => 'Video de inducción para nuevo personal',
				'fecha'          => now(),
			],
		];

		foreach ($recursos as $recurso) {
			Recurso::create($recurso);
		}
	}
}