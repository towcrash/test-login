<?php

namespace Database\Seeders\Cliente;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Cliente_ContratistaSeeder extends Seeder
{
	public function run(): void
	{
		DB::table('Cliente_Contratista')->truncate();

		$registros = [
			[
				'Cliente_id'     => 1,
				'Contratista_id' => 1,
				'Usuario_id'     => 1,
				'fecha'          => now(),
			],
			[
				'Cliente_id'     => 1,
				'Contratista_id' => 2,
				'Usuario_id'     => 1,
				'fecha'          => now(),
			],
			[
				'Cliente_id'     => 2,
				'Contratista_id' => 1,
				'Usuario_id'     => 3,
				'fecha'          => now(),
			],
			[
				'Cliente_id'     => 2,
				'Contratista_id' => 3,
				'Usuario_id'     => 3,
				'fecha'          => now(),
			],
			[
				'Cliente_id'     => 3,
				'Contratista_id' => 2,
				'Usuario_id'     => 4,
				'fecha'          => now(),
			],
		];

		foreach ($registros as $registro) {
			DB::table('Cliente_Contratista')->insert($registro);
		}
	}
}