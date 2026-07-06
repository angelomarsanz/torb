<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenombrarAsignadoAEnSoportesTecnicos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('soportes_tecnicos', function (Blueprint $table) {
            // Renombramos la columna
            $table->renameColumn('asignado_a', 'id_usuario_gestor');
        });

        Schema::table('soportes_tecnicos', function (Blueprint $table) {
            // Cambiamos el tipo a entero (BigInteger para compatibilidad con IDs de Laravel) y aseguramos que sea nulo
            $table->unsignedBigInteger('id_usuario_gestor')->nullable()->change();
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
            $table->string('id_usuario_gestor', 255)->nullable()->change();
            $table->renameColumn('id_usuario_gestor', 'asignado_a');
        });
    }
}
