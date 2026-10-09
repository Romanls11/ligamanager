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
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fase_id')->constrained('fases')->cascadeOnDelete();
            $table->unsignedInteger('jornada')->nullable();
            $table->foreignId('local_inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->foreignId('visita_inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->dateTime('fecha_hora')->nullable();
            $table->string('cancha')->nullable();
            $table->unsignedInteger('goles_local')->nullable();
            $table->unsignedInteger('goles_visita')->nullable();
            $table->string('estado')->default('programado');
            $table->unsignedInteger('llave')->nullable();
            $table->boolean('es_vuelta')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
