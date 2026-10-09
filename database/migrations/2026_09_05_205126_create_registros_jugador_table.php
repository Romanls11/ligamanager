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
        Schema::create('registros_jugador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->foreignId('jugador_id')->constrained('jugadores')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero_camiseta');
            $table->unsignedInteger('amarillas_acumuladas')->default(0);
            $table->unsignedInteger('jornadas_sin_tarjeta')->default(0);
            $table->string('estado')->default('activo');
            $table->timestamps();

            $table->unique(['inscripcion_id', 'numero_camiseta']);
            $table->unique(['inscripcion_id', 'jugador_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_jugador');
    }
};
