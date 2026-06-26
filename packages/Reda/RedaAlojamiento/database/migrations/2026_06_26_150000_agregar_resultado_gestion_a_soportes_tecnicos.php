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
        Schema::table('soportes_tecnicos', function (Blueprint $table) {
            // Agregamos la columna para registrar el resultado de la gestión (ej: 'Eliminado', 'Mantenido')
            $table->string('resultado_gestion')->nullable()->after('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soportes_tecnicos', function (Blueprint $table) {
            $table->dropColumn('resultado_gestion');
        });
    }
};
