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
				'nombre'      => 'Disco Principal',
				'descripcion' => 'Almacenamiento principal de documentos',
				'fecha'       => now(),
			],
			[
				'nombre'      => 'Disco de Seguridad',
				'descripcion' => 'Documentos de seguridad industrial',
				'fecha'       => now(),
			],
			[
				'nombre'      => 'Disco de Calidad',
				'descripcion' => 'Documentos de control de calidad',
				'fecha'       => now(),
			],
		];

		foreach ($discos as $disco) {
			Disco::create($disco);
		}
	}
}