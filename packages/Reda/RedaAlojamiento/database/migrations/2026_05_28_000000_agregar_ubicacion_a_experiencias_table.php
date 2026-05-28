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
        Schema::table('experiencias', function (Blueprint $blueprint) {
            // Se agrega la columna 'ubicacion' de tipo text y que acepta valores nulos
            $blueprint->text('ubicacion')->nullable()->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiencias', function (Blueprint $blueprint) {
            $blueprint->dropColumn('ubicacion');
        });
    }
};
