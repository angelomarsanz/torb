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
            // Agregamos la columna varchar(255) y nullable
            $table->string('tipo_producto_servicio', 255)->nullable()->after('nombre_actividad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            $table->dropColumn('tipo_producto_servicio');
        });
    }
};
