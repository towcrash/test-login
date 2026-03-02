<?php

namespace Database\Seeders\Cliente;

use App\Models\Cliente\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
	public function run(): void
	{
		Cliente::truncate();

		$clientes = [
			[
				'nombre'    => 'Engineering PR',
				'rut'       => '77828562-2',
			],
		];

		foreach ($clientes as $cliente) {
			Cliente::create($cliente);
		}
	}
}