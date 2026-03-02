<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Colaborador_Documento', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('Colaborador_id');
            $table->unsignedInteger('Documento_id');
            $table->decimal('pAprobacion', 5, 2)->nullable();
            $table->tinyInteger('bloqueado')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Colaborador_Documento');
    }
};