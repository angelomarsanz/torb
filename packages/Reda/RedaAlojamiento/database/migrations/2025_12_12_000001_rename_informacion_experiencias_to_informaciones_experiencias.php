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
        // Renombrar solo si existe la tabla antigua y no existe la nueva
        if (Schema::hasTable('informacion_experiencias') && !Schema::hasTable('informaciones_experiencias')) {
            try {
                Schema::rename('informacion_experiencias', 'informaciones_experiencias');
            } catch (\Exception $e) {
                // Fallback a sentencia SQL en caso de que Schema::rename falle
                try {
                    DB::statement('RENAME TABLE `informacion_experiencias` TO `informaciones_experiencias`');
                } catch (\Exception $e2) {
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
        // Revertir solo si existe la tabla nueva y no existe la antigua
        if (Schema::hasTable('informaciones_experiencias') && !Schema::hasTable('informacion_experiencias')) {
            try {
                Schema::rename('informaciones_experiencias', 'informacion_experiencias');
            } catch (\Exception $e) {
                try {
                    DB::statement('RENAME TABLE `informaciones_experiencias` TO `informacion_experiencias`');
                } catch (\Exception $e2) {
                    throw $e;
                }
            }
        }
    }
};
