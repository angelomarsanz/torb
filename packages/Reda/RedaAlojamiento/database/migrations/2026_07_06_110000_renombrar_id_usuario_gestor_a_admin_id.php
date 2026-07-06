<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenombrarIdUsuarioGestorAAdminId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('soportes_tecnicos', function (Blueprint $table) {
            // Renombramos la columna para mayor claridad y vinculación directa con el modelo Admin
            $table->renameColumn('id_usuario_gestor', 'admin_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('soportes_tecnicos', function (Blueprint $table) {
            $table->renameColumn('admin_id', 'id_usuario_gestor');
        });
    }
}
