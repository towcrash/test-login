<?php

namespace Database\Seeders\Recurso;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Recurso_UsuarioSeeder extends Seeder
{
	public function run(): void
	{
		DB::table('Recurso_Usuario')->truncate();

		$registros = [
			[
				'Recurso_id' => 1,
				'Usuario_id' => 1,
				'fecha'      => now(),
			],
			[
				'Recurso_id' => 1,
				'Usuario_id' => 2,
				'fecha'      => now(),
			],
			[
				'Recurso_id' => 2,
				'Usuario_id' => 1,
				'fecha'      => now(),
			],
			[
				'Recurso_id' => 2,
				'Usuario_id' => 3,
				'fecha'      => now(),
			],
			[
				'Recurso_id' => 3,
				'Usuario_id' => 2,
				'fecha'      => now(),
			],
			[
				'Recurso_id' => 4,
				'Usuario_id' => 3,
				'fecha'      => now(),
			],
			[
				'Recurso_id' => 5,
				'Usuario_id' => 4,
				'fecha'      => now(),
			],
		];

		foreach ($registros as $registro) {
			DB::table('Recurso_Usuario')->insert($registro);
		}
	}
}