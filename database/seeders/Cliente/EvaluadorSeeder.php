<?php

namespace Database\Seeders\Cliente;

use App\Models\Cliente\Evaluador;
use Illuminate\Database\Seeder;

class EvaluadorSeeder extends Seeder
{
	public function run(): void
	{
		Evaluador::truncate();

		$evaluadores = [
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
		];

		foreach ($evaluadores as $evaluador) {
			Evaluador::create($evaluador);
		}
	}
}