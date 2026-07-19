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
        Schema::table('diputas', function (Blueprint $table) {
            $table->longText('documentos_turista')->nullable();
            $table->longText('documentos_anfitrion')->nullable();
            $table->longText('documentos_agente')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diputas', function (Blueprint $table) {
            $table->dropColumn(['documentos_turista', 'documentos_anfitrion', 'documentos_agente']);
        });
    }
};
