<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            $table->decimal('precio_promocion', 15, 2)->nullable()->after('precio');
            $table->string('tipo_carga_precio_local')->nullable()->after('precio_promocion');
            $table->boolean('estatus_producto_servicio')->nullable()->after('disponibilidad');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('actividades_experiencias', function (Blueprint $table) {
            $table->dropColumn(['precio_promocion', 'tipo_carga_precio_local', 'estatus_producto_servicio']);
        });
    }
};
