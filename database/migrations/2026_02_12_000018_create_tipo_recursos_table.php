<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TipoRecurso', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('Disco_id');
            $table->string('nombre');
            $table->string('codigo');
            $table->string('color');
            $table->timestamp('fecha')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->tinyInteger('bloqueado')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TipoRecurso');
    }
};
