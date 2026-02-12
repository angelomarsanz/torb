<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdenActividadToActividadesExperienciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            // Creamos la columna orden_actividad
            // integer() en Laravel por defecto es equivalente a INT(11) en MariaDB/MySQL
            $table->integer('orden_actividad')->default(0)->after('foto_actividad');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            // Eliminamos la columna si se hace un rollback
            $table->dropColumn('orden_actividad');
        });
    }
}