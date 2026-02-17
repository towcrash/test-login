<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateSeeder extends Seeder
{
    /**
     * Tablas a excluir del truncado
     * 
     * @var array
     */
    protected $except = [

    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Iniciando truncado de todas las tablas...');
        
        // Desactivar restricciones de claves foráneas
        Schema::disableForeignKeyConstraints();
        
        // Obtener todas las tablas
        $databaseName = DB::connection()->getDatabaseName();
        $tables = DB::select("SHOW TABLES");
        
        $tableCount = 0;
        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$databaseName}"};
            
            // Verificar si la tabla debe ser excluida
            if (!in_array($tableName, $this->except)) {
                DB::table($tableName)->truncate();
                $this->command->info("Tabla '{$tableName}' truncada");
                $tableCount++;
            } else {
                $this->command->warn("Tabla '{$tableName}' excluida");
            }
        }

        Schema::enableForeignKeyConstraints();
        
        $this->command->info("Proceso completado. {$tableCount} tablas truncadas.");
    }
}