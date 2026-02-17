<?php

namespace Database\Seeders\Recurso;

use App\Models\Recurso\TipoRecurso;
use Illuminate\Database\Seeder;

class TipoRecursoSeeder extends Seeder
{
	public function run(): void
	{
		TipoRecurso::truncate();

		$tipoRecursos = [
			[
				'Disco_id'      => 1,
				'Evaluacion_id' => 1,
				'Documento_id'  => 1,
				'Usuario_id'    => 1,
				'nombre'        => 'Video Tutorial',
				'codigo'        => 'VID',
				'color'         => '#FF5733',
				'fecha'         => now(),
			],
			[
				'Disco_id'      => 1,
				'Evaluacion_id' => 1,
				'Documento_id'  => 2,
				'Usuario_id'    => 1,
				'nombre'        => 'Documento PDF',
				'codigo'        => 'PDF',
				'color'         => '#3357FF',
				'fecha'         => now(),
			],
			[
				'Disco_id'      => 2,
				'Evaluacion_id' => 2,
				'Documento_id'  => 3,
				'Usuario_id'    => 2,
				'nombre'        => 'Imagen',
				'codigo'        => 'IMG',
				'color'         => '#33FF57',
				'fecha'         => now(),
			],
			[
				'Disco_id'      => 3,
				'Evaluacion_id' => 3,
				'Documento_id'  => 4,
				'Usuario_id'    => 3,
				'nombre'        => 'Planilla Excel',
				'codigo'        => 'XLS',
				'color'         => '#F3FF33',
				'fecha'         => now(),
			],
		];

		foreach ($tipoRecursos as $tipoRecurso) {
			TipoRecurso::create($tipoRecurso);
		}
	}
}