<?php

namespace Database\Seeders\Usuario;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'SisAdmin', 
                'descripcion' => 'Usuario que gestiona el sistema.'
            ],
            [
                'nombre' => 'Evaluador',
                'descripcion' => 'Persona encargada de realizar evaluaciones y asignar calificaciones.'
            ],
            [
                'nombre' => 'Colaborador', 
                'descripcion' => 'Usuario que representa a un colaborador de una empresa contratista, con acceso a sus propias evaluaciones.'
            ],
            [
                'nombre' => 'Cliente', 
                'descripcion' => 'Usuario que solicita evaluaciones.'
            ],
            [
                'nombre' => 'Contratista', 
                'descripcion' => 'Usuario que representa a una empresa contratista, con acceso a sus propios colaboradores y evaluaciones.'
            ],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}
