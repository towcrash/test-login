<?php

namespace Database\Seeders\Documento;

use App\Models\Documento\Documento;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
	public function run(): void
	{
		Documento::truncate();

		$documentos = [
			[
				'Disco_id'    => 1,
				'nombre'      => 'Manual de Procedimientos',
				'descripcion' => 'Manual general de procedimientos operativos',
				'fecha'       => now(),
			],
			[
				'Disco_id'    => 1,
				'nombre'      => 'Normas de Seguridad',
				'descripcion' => 'Documento de normas de seguridad',
				'fecha'       => now(),
			],
			[
				'Disco_id'    => 2,
				'nombre'      => 'Protocolo de Emergencias',
				'descripcion' => 'Protocolo de actuación en emergencias',
				'fecha'       => now(),
			],
			[
				'Disco_id'    => 2,
				'nombre'      => 'EPP Requeridos',
				'descripcion' => 'Listado de equipos de protección personal',
				'fecha'       => now(),
			],
			[
				'Disco_id'    => 3,
				'nombre'      => 'Especificaciones Técnicas',
				'descripcion' => 'Especificaciones técnicas de materiales',
				'fecha'       => now(),
			],
		];

		foreach ($documentos as $documento) {
			Documento::create($documento);
		}
	}
}