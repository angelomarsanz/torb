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
            if (Schema::hasColumn('actividades_experiencias', 'precio_promocion')) {
                $table->dropColumn('precio_promocion');
            }
            $table->text('precios_monedas_complementarios')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            $table->decimal('precio_promocion', 15, 2)->nullable();
            $table->dropColumn('precios_monedas_complementarios');
        });
    }
};
