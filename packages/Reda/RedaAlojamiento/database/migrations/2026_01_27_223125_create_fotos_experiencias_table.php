<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fotos_experiencias', function (Blueprint $table) {
            $table->increments('id'); // Este puede seguir siendo increments (int 10) para ser como el original
            
            // CAMBIO CLAVE: Cambiar de integer a unsignedBigInteger para coincidir con experiencias.id
            $table->unsignedBigInteger('experiencia_id'); 
            
            $table->string('photo', 191);
            $table->string('message', 105)->nullable();
            $table->integer('cover_photo')->default(0);
            $table->integer('serial')->default(0);
            
            // Relación con el modelo Experiencia
            $table->foreign('experiencia_id')
                  ->references('id')
                  ->on('experiencias')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fotos_experiencias');
    }
};