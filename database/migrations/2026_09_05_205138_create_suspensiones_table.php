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
        Schema::create('suspensiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_jugador_id')->constrained('registros_jugador')->cascadeOnDelete();
            $table->string('motivo');
            $table->unsignedInteger('partidos_castigo');
            $table->unsignedInteger('partidos_cumplidos')->default(0);
            $table->boolean('cumplida')->default(false);
            $table->foreignId('partido_origen_id')->nullable()->constrained('partidos')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspensiones');
    }
};
