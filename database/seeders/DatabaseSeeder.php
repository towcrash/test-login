<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Aplicacion\AplicacionSeeder;
use Database\Seeders\Aplicacion\Cliente_EvaluacionSeeder;
use Database\Seeders\Aplicacion\Colaborador_EvaluacionSeeder;
use Database\Seeders\Aplicacion\Contratista_EvaluacionSeeder;
use Database\Seeders\Aplicacion\Evaluador_EvaluacionSeeder;
use Database\Seeders\Cliente\ClienteSeeder;
use Database\Seeders\Cliente\Cliente_ContratistaSeeder;
use Database\Seeders\Cliente\Cliente_UsuarioSeeder;
use Database\Seeders\Cliente\EvaluadorSeeder;
use Database\Seeders\Contratista\ColaboradorSeeder;
use Database\Seeders\Contratista\Contratista_UsuarioSeeder;
use Database\Seeders\Contratista\ContratistaSeeder;
use Database\Seeders\Documento\DiscoSeeder;
use Database\Seeders\Documento\DocumentoSeeder;
use Database\Seeders\Evaluacion\EvaluacionSeeder;
use Database\Seeders\Evaluacion\PreguntaSeeder;
use Database\Seeders\Evaluacion\AlternativaSeeder;
use Database\Seeders\Recurso\TipoRecursoSeeder;
use Database\Seeders\Recurso\Recurso_UsuarioSeeder;
use Database\Seeders\Recurso\RecursoSeeder;
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
		]);
		/*
		$this->call([
			
			ClienteSeeder::class,
			ContratistaSeeder::class,
			DiscoSeeder::class,
		]);

		$this->call([
			EvaluadorSeeder::class,
			ColaboradorSeeder::class,
			EvaluacionSeeder::class,
			DocumentoSeeder::class,
		]);

		$this->call([
			PreguntaSeeder::class,
			TipoRecursoSeeder::class,
		]);

		$this->call([
			AlternativaSeeder::class,
			RecursoSeeder::class,
		]);

		$this->call([
			AplicacionSeeder::class,
		]);

		// 6. Tablas pivote
		$this->call([
			Cliente_UsuarioSeeder::class,
			Contratista_UsuarioSeeder::class,
			Cliente_ContratistaSeeder::class,
			Cliente_EvaluacionSeeder::class,
			Evaluador_EvaluacionSeeder::class,
			Contratista_EvaluacionSeeder::class,
			Colaborador_EvaluacionSeeder::class,
			Recurso_UsuarioSeeder::class,
		]);
		*/
		$this->call([
			FakerSeeder::class,
		]);
	}
}