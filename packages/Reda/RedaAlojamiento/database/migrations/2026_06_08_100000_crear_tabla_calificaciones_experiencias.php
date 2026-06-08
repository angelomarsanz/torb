<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla de calificaciones de experiencias.
     */
    public function up(): void
    {
        Schema::create('calificaciones_experiencias', function (Blueprint $table) {
            $table->id();
            // Relación con la tabla de experiencias
            $table->unsignedBigInteger('experiencia_id')->nullable();
            // Relación con el usuario que realiza la calificación
            $table->unsignedBigInteger('user_id')->nullable();
            // Puntuación (ej. de 1 a 5 estrellas)
            $table->integer('estrellas')->nullable();
            // Comentario u opinión detallada
            $table->text('comentario')->nullable();
            $table->timestamps();

            // Definición de llaves foráneas para integridad referencial
            $table->foreign('experiencia_id')
                  ->references('id')
                  ->on('experiencias')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Revierte la migración eliminando la tabla.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones_experiencias');
    }
};
