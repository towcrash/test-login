<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Cliente\ClienteSeeder;
use Database\Seeders\Cliente\Cliente_UsuarioSeeder;
use Database\Seeders\Contratista\Contratista_UsuarioSeeder;
use Database\Seeders\Contratista\ContratistaSeeder;
use Database\Seeders\Documento\DiscoSeeder;
use Database\Seeders\Recurso\TipoRecursoSeeder;
use Database\Seeders\Usuario\UsuarioSeeder;
use Database\Seeders\Usuario\RolSeeder;
use Database\Seeders\Usuario\Usuario_RolSeeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{		
        $this->call([
			TruncateSeeder::class,
			UsuarioSeeder::class,
			RolSeeder::class,
			Usuario_RolSeeder::class,
			ClienteSeeder::class,
			ContratistaSeeder::class,
			DiscoSeeder::class,
			TipoRecursoSeeder::class,
			Contratista_UsuarioSeeder::class,
			Cliente_UsuarioSeeder::class,
			//FakerSeeder::class,
		]);
	}
}