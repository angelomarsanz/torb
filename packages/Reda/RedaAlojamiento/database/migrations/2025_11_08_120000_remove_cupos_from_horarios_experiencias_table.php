<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCuposFromHorariosExperienciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Comprobamos la existencia de las columnas antes de intentar eliminarlas.
        if (Schema::hasColumn('horarios_experiencias', 'cupos_reservados')) {
            Schema::table('horarios_experiencias', function (Blueprint $table) {
                $table->dropColumn('cupos_reservados');
            });
        }

        if (Schema::hasColumn('horarios_experiencias', 'cupos_pagados')) {
            Schema::table('horarios_experiencias', function (Blueprint $table) {
                $table->dropColumn('cupos_pagados');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Al restaurar, añadimos las columnas como enteros. Usamos valor por defecto 0
        // para evitar problemas al restaurar en tablas con registros existentes.
        Schema::table('horarios_experiencias', function (Blueprint $table) {
            $table->integer('cupos_reservados')->default(0);
            $table->integer('cupos_pagados')->default(0);
        });
    }
}
