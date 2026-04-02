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
            // Modificamos a nullable.
            // Usamos ->change() que requiere doctrine/dbal
            $table->text('descripcion_actividad')->nullable()->change();
            $table->integer('orden_actividad')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            // Volvemos a NOT NULL en caso de error
            $table->text('descripcion_actividad')->nullable(false)->change();
            $table->integer('orden_actividad')->nullable(false)->change();
        });
    }
};
