<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torneo_id')->constrained('torneos')->cascadeOnDelete();
            $table->string('nombre');
            $table->unsignedInteger('min_jugadores')->default(16);
            $table->unsignedInteger('max_jugadores')->default(40);
            $table->unsignedInteger('amarillas_para_suspension')->default(5);
            $table->unsignedInteger('partidos_castigo_roja')->default(1);
            $table->unsignedInteger('jornadas_amnistia')->nullable();
            $table->date('fecha_cierre_registros')->nullable();
            $table->unsignedTinyInteger('vueltas')->default(1);
            $table->unsignedInteger('equipos_a_liguilla')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
