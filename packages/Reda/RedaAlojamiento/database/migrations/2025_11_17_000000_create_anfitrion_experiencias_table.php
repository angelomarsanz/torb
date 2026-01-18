<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anfitrion_experiencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('fecha_nacimiento')->nullable();
            $table->boolean('identidad_verificada')->default(false);
            $table->text('descripcion_anfitrion')->nullable();
            $table->text('trayectoria_profesional')->nullable();
            $table->text('me_dedico_a')->nullable();
            $table->text('intereses_anfitrion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Relación con usuarios
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Revertir las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anfitrion_experiencias');
    }
};
