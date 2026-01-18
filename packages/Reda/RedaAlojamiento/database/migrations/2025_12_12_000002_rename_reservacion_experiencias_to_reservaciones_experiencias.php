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
        if (Schema::hasTable('reservacion_experiencias') && !Schema::hasTable('reservaciones_experiencias')) {
            try {
                Schema::rename('reservacion_experiencias', 'reservaciones_experiencias');
            } catch (\Exception $e) {
                // Si Schema::rename falla, intentamos con SQL directo
                try {
                    DB::statement('RENAME TABLE `reservacion_experiencias` TO `reservaciones_experiencias`');
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
        if (Schema::hasTable('reservaciones_experiencias') && !Schema::hasTable('reservacion_experiencias')) {
            try {
                Schema::rename('reservaciones_experiencias', 'reservacion_experiencias');
            } catch (\Exception $e) {
                try {
                    DB::statement('RENAME TABLE `reservaciones_experiencias` TO `reservacion_experiencias`');
                } catch (\Exception $e2) {
                    throw $e;
                }
            }
        }
    }
};
