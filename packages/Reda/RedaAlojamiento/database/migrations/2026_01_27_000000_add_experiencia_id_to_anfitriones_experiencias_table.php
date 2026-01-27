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
        Schema::table('anfitriones_experiencias', function (Blueprint $table) {
            // 1. Creamos la columna (después de user_id para mantener orden)
            $table->unsignedBigInteger('experiencia_id')->nullable()->after('user_id');

            // 2. Definimos la relación foránea
            $table->foreign('experiencia_id')
                  ->references('id')
                  ->on('experiencias')
                  ->onDelete('cascade'); // Si se borra la experiencia, se borra el registro de anfitrión vinculado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anfitriones_experiencias', function (Blueprint $table) {
            // Eliminamos primero la llave foránea y luego la columna
            $table->dropForeign(['experiencia_id']);
            $table->dropColumn('experiencia_id');
        });
    }
};