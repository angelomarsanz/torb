<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToActividadesExperienciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actividades_experiencias', function (Blueprint $blueprint) {
            // Agregamos nombre_experiencia después de experiencia_id
            $blueprint->string('nombre_experiencia', 255)->nullable()->after('experiencia_id');
            
            // Agregamos precio, currency_id y disponibilidad después de orden_actividad
            $blueprint->decimal('precio', 15, 2)->nullable()->after('orden_actividad');
            
            // currency_id debe ser unsignedInt porque en currency.sql el id es int(10) UNSIGNED
            $blueprint->integer('currency_id')->unsigned()->nullable()->after('precio');
            
            $blueprint->boolean('disponibilidad')->nullable()->after('currency_id');

            // Definición de la llave foránea
            $blueprint->foreign('currency_id')
                      ->references('id')
                      ->on('currency')
                      ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actividades_experiencias', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['currency_id']);
            $blueprint->dropColumn(['nombre_experiencia', 'precio', 'currency_id', 'disponibilidad']);
        });
    }
}