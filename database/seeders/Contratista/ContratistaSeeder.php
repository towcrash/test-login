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
				'nombre'    => 'Engineering PR',
				'rut'       => '77828562-2',
			],
		];

		foreach ($contratistas as $contratista) {
			Contratista::create($contratista);
		}
	}
}