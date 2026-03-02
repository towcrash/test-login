<?php

namespace Database\Seeders\Contratista;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Contratista_UsuarioSeeder extends Seeder
{
	public function run(): void
	{
		$registros = [
			[
				'Contratista_id' => 1,
				'Usuario_id'     => 1,
			],
			[
				'Contratista_id' => 1,
				'Usuario_id'     => 2,
			],
			[
				'Contratista_id' => 2,
				'Usuario_id'     => 3,
			],
			[
				'Contratista_id' => 3,
				'Usuario_id'     => 4,
			],
		];

		foreach ($registros as $registro) {
			DB::table('Contratista_Usuario')->insert($registro);
		}
	}
}