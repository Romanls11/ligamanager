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
        Schema::table('suspensiones', function (Blueprint $table) {
            $table->foreignId('evento_partido_id')->nullable()->after('partido_origen_id')
                ->constrained('eventos_partido')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suspensiones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evento_partido_id');
        });
    }
};
