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
        Schema::table('experiencias', function (Blueprint $table) {
            // Agregamos la columna como texto y nullable
            $table->text('categoria_negocio')->nullable()->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiencias', function (Blueprint $table) {
            $table->dropColumn('categoria_negocio');
        });
    }
};
