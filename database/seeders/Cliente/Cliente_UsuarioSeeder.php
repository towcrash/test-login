<?php

namespace Database\Seeders\Cliente;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Cliente_UsuarioSeeder extends Seeder
{
	public function run(): void
	{
		DB::table('Cliente_Usuario')->truncate();

		$registros = [
			[
				'Cliente_id' => 1,
				'Usuario_id' => 1,
				'fecha'      => now(),
			],
			[
				'Cliente_id' => 1,
				'Usuario_id' => 2,
				'fecha'      => now(),
			],
			[
				'Cliente_id' => 2,
				'Usuario_id' => 3,
				'fecha'      => now(),
			],
			[
				'Cliente_id' => 3,
				'Usuario_id' => 4,
				'fecha'      => now(),
			],
			[
				'Cliente_id' => 4,
				'Usuario_id' => 5,
				'fecha'      => now(),
			],
		];

		foreach ($registros as $registro) {
			DB::table('Cliente_Usuario')->insert($registro);
		}
	}
}