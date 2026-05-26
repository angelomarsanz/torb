<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('experiencias', function (Blueprint $table) {
            // Añadimos la columna 'horarios' de tipo text, nullable, después de 'reglas_cancelacion'
            $table->text('horarios')->nullable()->after('reglas_cancelacion');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('experiencias', function (Blueprint $table) {
            $table->dropColumn('horarios');
        });
    }
};
