<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Recurso_Usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('Recurso_id');
            $table->unsignedInteger('Usuario_id');
            $table->timestamp('fecha')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->tinyInteger('bloqueado')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Recurso_Usuario');
    }
};
