<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favoritos_comercios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('experiencia_id')->nullable();
            $table->timestamps();

            // Índices y relaciones (considerando que las tablas originales pueden estar en otra conexión o base de datos)
            $table->index('user_id');
            $table->index('experiencia_id');
            
            // Si las tablas están en la misma base de datos, se podrían agregar llaves foráneas.
            // Por directriz del proyecto, permitimos nulos en columnas nuevas.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favoritos_comercios');
    }
};
