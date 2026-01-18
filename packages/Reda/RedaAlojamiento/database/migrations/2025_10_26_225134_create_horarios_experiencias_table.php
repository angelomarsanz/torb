<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horarios_experiencias', function (Blueprint $table) {
            $table->id();
            $table->integer('property_id')->unsigned();
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->dateTime('fecha_hora_desde');
            $table->dateTime('fecha_hora_hasta');
            $table->integer('cupos_planificados');
            $table->integer('cupos_reservados');
            $table->integer('cupos_pagados');
            $table->string('divisa');
            $table->decimal('monto_por_persona', 15, 2);
            $table->decimal('monto_por_grupo', 15, 2);
            $table->integer('minimo_personas_grupo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('horarios_experiencias');
    }
};