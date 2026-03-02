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
				'nombre'        => 'Video Tutorial',
				'codigo'        => 'VID',
				'color'         => '#ff2727',
			],
			[
				'nombre'        => 'Documento PDF',
				'codigo'        => 'PDF',
				'color'         => '#3357FF',
			],
			[
				'nombre'        => 'Presentacion',
				'codigo'        => 'PPT',
				'color'         => '#ff7e14',
			],
			[
				'nombre'        => 'Hoja de cálculo',
				'codigo'        => 'XLS',
				'color'         => '#22ff22',
			],
			[
				'nombre'        => 'Otros',
				'codigo'        => 'OTR',
				'color'         => '#1ce5ff',
			],

		];

		foreach ($tipoRecursos as $tipoRecurso) {
			TipoRecurso::create($tipoRecurso);
		}
	}
}