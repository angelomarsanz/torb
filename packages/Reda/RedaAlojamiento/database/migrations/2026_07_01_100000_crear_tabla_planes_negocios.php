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
        Schema::create('planes_negocios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->string('moneda')->nullable();
            $table->string('lapso_pago')->nullable();
            $table->json('beneficios')->nullable();
            $table->boolean('destacado')->default(false)->nullable();
            $table->boolean('estatus')->default(true)->nullable();
            $table->integer('orden')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_negocios');
    }
};
