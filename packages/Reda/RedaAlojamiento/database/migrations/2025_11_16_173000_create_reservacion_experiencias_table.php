<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservacionExperienciasTable extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservacion_experiencias', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Relaciones foráneas
            $table->foreignId('experiencia_id')->constrained('experiencias')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('horarios_experiencia_id')->constrained('horarios_experiencias')->onDelete('cascade');

            // Campos solicitados
            $table->string('tipo_reservacion', 255);
            $table->integer('cantidad_personas')->default(1);
            $table->decimal('monto_pagado', 15, 2)->default(0.00);
            $table->boolean('cancelado')->default(false);
            $table->boolean('devolucion_dinero')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservacion_experiencias');
    }
}
