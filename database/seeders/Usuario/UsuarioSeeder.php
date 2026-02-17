<?php

namespace Database\Seeders\Usuario;

use App\Models\Usuario\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
	public function run(): void
	{
		$usuarios = [
			[
				'user'		=> 'SReyes',
				'password'	=> 'password123',
				'rut'		=> '11111111-1',
				'nombre'    => 'Samuel Reyes',
				'email'     => 'samuel.reyes@EngineeringPR.cl',
			],
			[
				'user'		=> 'YNeiline',
				'password'	=> 'password123',
				'rut'		=> '22222222-2',
				'nombre'    => 'Yarieth Neiline',
				'email'     => 'yarieth.neiline@EngineeringPR.cl',
			],
			[
				'user'		=> 'PReyes',
				'password'	=> 'password123',
				'rut'		=> '33333333-3',
				'nombre'    => 'Pablo Reyes',
				'email'     => 'pablo.reyes@EngineeringPR.cl',
			],
			[
				'user'		=> 'FVega',
				'password'	=> 'password123',
				'rut'		=> '44444444-4',	
				'nombre'    => 'Fernanda Vega',
				'email'     => 'fernanda.vega@EngineeringPR.cl',
			],
			[
				'user'		=> 'TAdmin',
				'password'	=> 'password123',
				'rut'		=> '55555555-5',
				'nombre'    => 'Test Admin',
				'email'     => 'admin@EngineeringPR.cl',
			],
			[
				'user'		=> 'TCliente',
				'password'	=> 'password123',
				'rut'		=> '66666666-6',
				'nombre'    => 'Test Cliente',
				'email'     => 'cliente@EngineeringPR.cl',
			],
			[
				'user'		=> 'TEvaluador',
				'password'	=> 'password123',
				'rut'		=> '77777777-7',
				'nombre'    => 'Test Evaluador',
				'email'     => 'evaluador@EngineeringPR.cl',
			],
			[
				'user'		=> 'TContratista',
				'password'	=> 'password123',
				'rut'		=> '88888888-8',
				'nombre'    => 'Test Contratista',
				'email'     => 'contratista@EngineeringPR.cl',
			],
			[
				'user'		=> 'TColaborador',
				'password'	=> 'password123',
				'rut'		=> '99999999-9',
				'nombre'    => 'Test Colaborador',
				'email'     => 'colaborador@EngineeringPR.cl',
			],
			[
				'user'		=> 'Expirado',
				'password'	=> 'password123',
				'rut'		=> '12345678-9',
				'nombre'    => 'Test Colaborador',
				'email'     => 'exp@EngineeringPR.cl',
				'vigencia'  => '2020-06-06 10:10:10'
			],
			[
				'user'		=> 'Bloqueado',
				'password'	=> 'password123',
				'rut'		=> '98765432-1',
				'nombre'    => 'Test Colaborador',
				'email'     => 'exp@EngineeringPR.cl',
				'bloqueado'  => 1,
			],
		];

		foreach ($usuarios as $usuario) {
			Usuario::create($usuario);
		}
	}
}