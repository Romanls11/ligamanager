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
        Schema::table('registros_jugador', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero_camiseta')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registros_jugador', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero_camiseta')->nullable(false)->change();
        });
    }
};
