<?php

namespace Database\Seeders\Evaluacion;

use App\Models\Evaluacion\Alternativa;
use Illuminate\Database\Seeder;

class AlternativaSeeder extends Seeder
{
	public function run(): void
	{
		Alternativa::truncate();

		$alternativas = [
			// Alternativas para Pregunta 1
			['Pregunta_id' => 1, 'texto' => 'Sí, completamente', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 1, 'texto' => 'Parcialmente', 'codigo' => 'B', 'fecha' => now()],
			['Pregunta_id' => 1, 'texto' => 'No cumple', 'codigo' => 'C', 'fecha' => now()],
			
			// Alternativas para Pregunta 2
			['Pregunta_id' => 2, 'texto' => 'Sí', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 2, 'texto' => 'No', 'codigo' => 'B', 'fecha' => now()],
			['Pregunta_id' => 2, 'texto' => 'No aplica', 'codigo' => 'C', 'fecha' => now()],
			
			// Alternativas para Pregunta 3
			['Pregunta_id' => 3, 'texto' => 'Excelente', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 3, 'texto' => 'Aceptable', 'codigo' => 'B', 'fecha' => now()],
			['Pregunta_id' => 3, 'texto' => 'Deficiente', 'codigo' => 'C', 'fecha' => now()],
			
			// Alternativas para Pregunta 4
			['Pregunta_id' => 4, 'texto' => 'Sí, completamente', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 4, 'texto' => 'Parcialmente', 'codigo' => 'B', 'fecha' => now()],
			['Pregunta_id' => 4, 'texto' => 'No', 'codigo' => 'C', 'fecha' => now()],
			
			// Alternativas para Pregunta 5
			['Pregunta_id' => 5, 'texto' => 'Todos realizados', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 5, 'texto' => 'Algunos pendientes', 'codigo' => 'B', 'fecha' => now()],
			['Pregunta_id' => 5, 'texto' => 'No realizados', 'codigo' => 'C', 'fecha' => now()],
			
			// Alternativas para Pregunta 6
			['Pregunta_id' => 6, 'texto' => 'Cumple', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 6, 'texto' => 'No cumple', 'codigo' => 'B', 'fecha' => now()],
			
			// Alternativas para Pregunta 7
			['Pregunta_id' => 7, 'texto' => 'Cumple', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 7, 'texto' => 'No cumple', 'codigo' => 'B', 'fecha' => now()],
			
			// Alternativas para Pregunta 8
			['Pregunta_id' => 8, 'texto' => 'Siempre', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 8, 'texto' => 'Frecuentemente', 'codigo' => 'B', 'fecha' => now()],
			['Pregunta_id' => 8, 'texto' => 'A veces', 'codigo' => 'C', 'fecha' => now()],
			['Pregunta_id' => 8, 'texto' => 'Nunca', 'codigo' => 'D', 'fecha' => now()],
			
			// Alternativas para Pregunta 9
			['Pregunta_id' => 9, 'texto' => 'Excelente', 'codigo' => 'A', 'fecha' => now()],
			['Pregunta_id' => 9, 'texto' => 'Bueno', 'codigo' => 'B', 'fecha' => now()],
			['Pregunta_id' => 9, 'texto' => 'Regular', 'codigo' => 'C', 'fecha' => now()],
			['Pregunta_id' => 9, 'texto' => 'Deficiente', 'codigo' => 'D', 'fecha' => now()],
		];

		foreach ($alternativas as $alternativa) {
			Alternativa::create($alternativa);
		}
	}
}