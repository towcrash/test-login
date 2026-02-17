<?php

namespace Database\Seeders\Contratista;

use App\Models\Contratista\Colaborador;
use Illuminate\Database\Seeder;

class ColaboradorSeeder extends Seeder
{
	public function run(): void
	{
		$colaboradores = [
			[
				'Contratista_id' => 1,
				'Usuario_id'     => 2,
			],
			[
				'Contratista_id' => 1,
				'Usuario_id'     => 3,
			],
			[
				'Contratista_id' => 2,
				'Usuario_id'     => 4,
			],
			[
				'Contratista_id' => 3,
				'Usuario_id'     => 5,
			],
		];

		foreach ($colaboradores as $colaborador) {
			Colaborador::create($colaborador);
		}
	}
}