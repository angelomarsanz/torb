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
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            // Cambiamos a nullable.
            // Debe ser unsignedBigInteger porque es una llave foránea.
            $table->unsignedBigInteger('experiencia_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            // En caso de rollback, vuelve a ser obligatorio
            $table->unsignedBigInteger('experiencia_id')->nullable(false)->change();
        });
    }
};
