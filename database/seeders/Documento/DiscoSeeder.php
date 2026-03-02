<?php

namespace Database\Seeders\Documento;

use App\Models\Documento\Disco;
use Illuminate\Database\Seeder;

class DiscoSeeder extends Seeder
{
	public function run(): void
	{
		Disco::truncate();

		$discos = [
			[
				'nombre'      => 'recursos',
				'descripcion' => 'Almacenamiento de recursos',
			],
			[
				'nombre'      => 'evaluaciones',
				'descripcion' => 'Documentos de evaluaciones completadas',
			],
		];

		foreach ($discos as $disco) {
			Disco::create($disco);
		}
	}
}