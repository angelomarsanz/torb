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
            $table->text('precios_monedas_complementarios')->nullable()->after('precio')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            // No es estrictamente necesario revertir el orden estético, 
            // pero si se deseara mover al final, se requeriría conocer la última columna.
        });
    }
};
