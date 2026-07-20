<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInitialUserColumnsToDisputasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('disputas', function (Blueprint $blueprint) {
            $blueprint->integer('id_usuario_inicial')->nullable()->after('documentos_agente');
            $blueprint->string('rol_usuario_inicial', 255)->nullable()->after('id_usuario_inicial');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disputas', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['id_usuario_inicial', 'rol_usuario_inicial']);
        });
    }
}
