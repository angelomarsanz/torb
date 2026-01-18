<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFieldsFromHorariosExperienciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('horarios_experiencias', function (Blueprint $table) {
            $table->dropColumn(['divisa', 'monto_por_persona', 'monto_por_grupo', 'minimo_personas_grupo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('horarios_experiencias', function (Blueprint $table) {
            $table->string('divisa');
            $table->decimal('monto_por_persona', 15, 2);
            $table->decimal('monto_por_grupo', 15, 2);
            $table->integer('minimo_personas_grupo');
        });
    }
}
