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
        Schema::table('experiencias', function (Blueprint $table) {
            // Modificamos todas las columnas obligatorias a nullable
            $table->string('titulo', 191)->nullable()->change();
            $table->text('descripcion')->nullable()->change();
            $table->text('ruta_imagenes')->nullable()->change();
            $table->string('latitud_encuentro', 191)->nullable()->change();
            $table->string('longitud_encuentro', 191)->nullable()->change();
            $table->string('tipo_moneda', 191)->nullable()->change();
            $table->decimal('precio_persona', 15, 2)->nullable()->change();
            $table->decimal('precio_grupo', 15, 2)->nullable()->change();
            $table->integer('minimo_personas_grupo')->nullable()->change();
            $table->string('reglas_cancelacion', 191)->nullable()->change();

            // user_id ya es nullable según tu SQL, pero lo aseguramos
            $table->integer('user_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiencias', function (Blueprint $table) {
            // Revertimos a NOT NULL según la estructura original
            $table->string('titulo', 191)->nullable(false)->change();
            $table->text('descripcion')->nullable(false)->change();
            $table->text('ruta_imagenes')->nullable(false)->change();
            $table->string('latitud_encuentro', 191)->nullable(false)->change();
            $table->string('longitud_encuentro', 191)->nullable(false)->change();
            $table->string('tipo_moneda', 191)->nullable(false)->change();
            $table->decimal('precio_persona', 15, 2)->nullable(false)->change();
            $table->decimal('precio_grupo', 15, 2)->nullable(false)->change();
            $table->integer('minimo_personas_grupo')->nullable(false)->change();
            $table->string('reglas_cancelacion', 191)->nullable(false)->change();
        });
    }
};
