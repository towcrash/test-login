<?php

namespace Database\Seeders\Contratista;

use App\Models\Contratista\Contratista;
use Illuminate\Database\Seeder;

class ContratistaSeeder extends Seeder
{
	public function run(): void
	{
		Contratista::truncate();

		$contratistas = [
			[
				'nombre'    => 'Contratista General S.A.',
				'rut'       => '77123456-7',
				'fecha'     => now(),
			],
			[
				'nombre'    => 'Obras y Servicios Ltda.',
				'rut'       => '77234567-8',
				'fecha'     => now(),
			],
			[
				'nombre'    => 'Construcciones del Norte',
				'rut'       => '77345678-9',
				'fecha'     => now(),
			],
			[
				'nombre'    => 'Mantenimiento Industrial',
				'rut'       => '77456789-0',
				'fecha'     => now(),
			],
		];

		foreach ($contratistas as $contratista) {
			Contratista::create($contratista);
		}
	}
}