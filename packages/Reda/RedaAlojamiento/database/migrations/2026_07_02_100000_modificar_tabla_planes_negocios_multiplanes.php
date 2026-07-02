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
        Schema::table('planes_negocios', function (Blueprint $table) {
            // Eliminar columnas individuales
            $table->dropColumn(['precio', 'moneda', 'lapso_pago']);
            
            // Agregar columna JSON para multiplanes
            $table->json('planes_pago')->nullable()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planes_negocios', function (Blueprint $table) {
            $table->decimal('precio', 10, 2)->nullable()->after('nombre');
            $table->string('moneda')->nullable()->after('precio');
            $table->string('lapso_pago')->nullable()->after('moneda');
            $table->dropColumn('planes_pago');
        });
    }
};
