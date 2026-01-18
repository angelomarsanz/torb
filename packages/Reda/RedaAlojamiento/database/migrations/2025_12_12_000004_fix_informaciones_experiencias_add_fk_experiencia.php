<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        // Verificar si ya existe la FK
        $exists = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'informaciones_experiencias' AND COLUMN_NAME = 'experiencia_id' AND REFERENCED_TABLE_NAME = 'experiencias'");

        if (empty($exists) || $exists[0]->cnt == 0) {
            // Asegurar tipo de columna
            try {
                Schema::table('informaciones_experiencias', function (Blueprint $table) {
                    if (Schema::hasColumn('informaciones_experiencias', 'experiencia_id')) {
                        $table->unsignedBigInteger('experiencia_id')->change();
                    }
                });
            } catch (\Exception $e) {
                try {
                    DB::statement("ALTER TABLE `informaciones_experiencias` MODIFY `experiencia_id` BIGINT UNSIGNED NOT NULL");
                } catch (\Exception $e2) {
                    throw $e;
                }
            }

            // Añadir la constraint
            try {
                DB::statement("ALTER TABLE `informaciones_experiencias` ADD CONSTRAINT `informaciones_experiencias_experiencia_id_foreign` FOREIGN KEY (`experiencia_id`) REFERENCES `experiencias`(`id`) ON DELETE CASCADE");
            } catch (\Exception $e) {
                throw $e;
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
        try {
            DB::statement("ALTER TABLE `informaciones_experiencias` DROP FOREIGN KEY `informaciones_experiencias_experiencia_id_foreign`");
        } catch (\Exception $e) {
            // Ignorar si no existe
        }
    }
};
