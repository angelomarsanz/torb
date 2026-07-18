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
        Schema::create('diputas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('booking_id')->nullable();
            $table->string('estado', 255)->nullable();
            $table->text('ultima_actividad')->nullable();
            $table->string('prioridad', 255)->nullable();
            $table->dateTime('fecha_apertura')->nullable();
            $table->dateTime('fecha_limite')->nullable();
            $table->unsignedInteger('id_usuario_agente_asignado')->nullable();
            $table->unsignedInteger('id_usuario_turista')->nullable();
            $table->unsignedInteger('id_usuario_anfitrion')->nullable();
            $table->string('categoria', 255)->nullable();
            $table->text('motivo')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();

            // Relación con la tabla bookings del proyecto principal
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diputas');
    }
};
