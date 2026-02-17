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
				'nombre'    => 'Empresa Constructora ABC',
				'rut'       => '76123456-7',
				'fecha'     => now(),
			],
			[
				'nombre'    => 'Minera del Sur S.A.',
				'rut'       => '76234567-8',
				'fecha'     => now(),
			],
			[
				'nombre'    => 'Ingeniería y Proyectos XYZ',
				'rut'       => '76345678-9',
				'fecha'     => now(),
			],
			[
				'nombre'    => 'Servicios Industriales Ltda.',
				'rut'       => '76456789-0',
				'fecha'     => now(),
			],
		];

		foreach ($clientes as $cliente) {
			Cliente::create($cliente);
		}
	}
}