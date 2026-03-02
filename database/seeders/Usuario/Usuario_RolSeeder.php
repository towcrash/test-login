<?php

namespace Database\Seeders\Usuario;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Usuario_RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registros = [
			[
				'Usuario_id'     => 1,
                'Rol_id'         => 1,
			],
			[
				'Usuario_id'     => 2,
                'Rol_id'         => 1,
			],
			[
				'Usuario_id'     => 3,
                'Rol_id'         => 1,
			],
			[
				'Usuario_id'     => 4,
                'Rol_id'         => 1,
			],
			[
				'Usuario_id'     => 1,
                'Rol_id'         => 2,
			],
			[
				'Usuario_id'     => 2,
                'Rol_id'         => 2,
			],
			[
				'Usuario_id'     => 3,
                'Rol_id'         => 2,
			],
			[
				'Usuario_id'     => 4,
                'Rol_id'         => 2,
			],
			[
				'Usuario_id'     => 1,
                'Rol_id'         => 3,
			],
			[
				'Usuario_id'     => 2,
                'Rol_id'         => 3,
			],
			[
				'Usuario_id'     => 3,
                'Rol_id'         => 3,
			],
			[
				'Usuario_id'     => 4,
                'Rol_id'         => 3,
			],
			[
				'Usuario_id'     => 1,
                'Rol_id'         => 4,
			],
			[
				'Usuario_id'     => 2,
                'Rol_id'         => 4,
			],
			[
				'Usuario_id'     => 3,
                'Rol_id'         => 4,
			],
			[
				'Usuario_id'     => 4,
                'Rol_id'         => 4,
			],
			[
				'Usuario_id'     => 1,
                'Rol_id'         => 5,
			],
			[
				'Usuario_id'     => 2,
                'Rol_id'         => 5,
			],
			[
				'Usuario_id'     => 3,
                'Rol_id'         => 5,
			],
			[
				'Usuario_id'     => 4,
                'Rol_id'         => 5,
			],
			/*
			[
				'Usuario_id'     => 5,
                'Rol_id'         => 1,
			],
            [
                'Usuario_id'     => 6,
                'Rol_id'         => 4,
            ],
			[
				'Usuario_id'     => 7,
                'Rol_id'         => 2,
			],
			[
				'Usuario_id'     => 8,
                'Rol_id'         => 5,
			],
			[
				'Usuario_id'     => 9,
                'Rol_id'         => 3,
			],
			*/
		];

		foreach ($registros as $registro) {
			DB::table('Usuario_Rol')->insert($registro);
		}
    }
}
