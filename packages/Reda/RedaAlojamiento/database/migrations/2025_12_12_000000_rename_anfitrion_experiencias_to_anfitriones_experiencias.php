<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     *
     * @return void
     */
    public function up()
    {
        // Si existe la tabla antigua y no existe la nueva, renombramos
        if (Schema::hasTable('anfitrion_experiencias') && !Schema::hasTable('anfitriones_experiencias')) {
            try {
                Schema::rename('anfitrion_experiencias', 'anfitriones_experiencias');
            } catch (\Exception $e) {
                // Si falla el método Schema::rename, intentamos con una sentencia SQL segura
                try {
                    DB::statement('RENAME TABLE `anfitrion_experiencias` TO `anfitriones_experiencias`');
                } catch (\Exception $e2) {
                    // Relanzamos la excepción original para que la migración deje claro el fallo
                    throw $e;
                }
            }
        }
    }

    /**
     * Revertir las migraciones.
     *
     * @return void
     */
    public function down()
    {
        // Revertir solo si existe la tabla nueva y no existe la tabla antigua
        if (Schema::hasTable('anfitriones_experiencias') && !Schema::hasTable('anfitrion_experiencias')) {
            try {
                Schema::rename('anfitriones_experiencias', 'anfitrion_experiencias');
            } catch (\Exception $e) {
                try {
                    DB::statement('RENAME TABLE `anfitriones_experiencias` TO `anfitrion_experiencias`');
                } catch (\Exception $e2) {
                    throw $e;
                }
            }
        }
    }
};
