<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Usuario', function (Blueprint $table) {
            $table->id();
            $table->string('user');
            $table->string('password');
            $table->string('rut')->nullable();
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->timestamp('fecha')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->tinyInteger('bloqueado')->default(0);
            $table->timestamp('vigencia')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Usuario');
    }
};
