<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 |-------------------------------------------------------------------------
 | Migración: crear tabla informacion_experiencias
 |-------------------------------------------------------------------------
 | Esta migración crea la tabla `informacion_experiencias` que tiene una
 | relación de uno a muchos con `experiencias` a través de
 | `experiencia_id`.
 */

class CreateInformacionExperienciasTable extends Migration
{
    /**
     * Ejecutar las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informacion_experiencias', function (Blueprint $table) {
            $table->id();
            // FK a la tabla experiencias
            $table->foreignId('experiencia_id')->constrained('experiencias')->onDelete('cascade');

            // Campos solicitados
            $table->text('requisitos_viajero')->nullable();
            $table->string('nivel_actividad', 255)->nullable();
            $table->text('que_debes_traer')->nullable();
            $table->string('accesibilidad', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('informacion_experiencias');
    }
}
