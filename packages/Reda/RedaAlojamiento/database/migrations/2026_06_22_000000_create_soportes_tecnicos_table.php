<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoportesTecnicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soportes_tecnicos', function (Blueprint $blueprint) {
            $blueprint->bigIncrements('id');
            $blueprint->unsignedBigInteger('user_id')->nullable();
            $blueprint->string('tema', 255)->nullable();
            $blueprint->text('mensaje_usuario')->nullable();
            $blueprint->string('link_error', 255)->nullable();
            $blueprint->string('asignado_a', 255)->nullable();
            $blueprint->string('estatus', 255)->nullable();
            $blueprint->dateTime('fecha_cambio_estatus')->nullable();
            $blueprint->dateTime('fecha_prometido_para')->nullable();
            $blueprint->text('mensaje_soporte_tecnico')->nullable();
            
            // Columnas sugeridas adicionales
            $blueprint->string('prioridad', 50)->nullable();
            $blueprint->boolean('visto_por_admin')->default(false)->nullable();
            $blueprint->boolean('visto_por_usuario')->default(false)->nullable();
            
            $blueprint->timestamps();

            // Opcional: Relación con la tabla users si existe
            // $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soportes_tecnicos');
    }
}
