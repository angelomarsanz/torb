<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExperienciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experiencias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->text('ruta_imagenes');
            $table->string('latitud_encuentro');
            $table->string('longitud_encuentro');
            $table->string('tipo_moneda');
            $table->decimal('precio_persona', 15, 2);
            $table->decimal('precio_grupo', 15, 2);
            $table->integer('minimo_personas_grupo');
            $table->string('reglas_cancelacion');
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
        Schema::dropIfExists('experiencias');
    }
}
