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
        // Revisar si ya existe la FK para evitar errores
        $exists = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'anfitriones_experiencias' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users'");

        if (empty($exists) || $exists[0]->cnt == 0) {
            // Asegurar que la columna tiene tipo BIGINT UNSIGNED (coincide con $table->id())
            try {
                Schema::table('anfitriones_experiencias', function (Blueprint $table) {
                    if (Schema::hasColumn('anfitriones_experiencias', 'user_id')) {
                        $table->unsignedBigInteger('user_id')->change();
                    }
                });
            } catch (\Exception $e) {
                // Fallback a ALTER TABLE si change() no está disponible
                try {
                    DB::statement("ALTER TABLE `anfitriones_experiencias` MODIFY `user_id` BIGINT UNSIGNED NOT NULL");
                } catch (\Exception $e2) {
                    // Si falla, relanzamos la excepción original
                    throw $e;
                }
            }

            // Añadir la constraint con un nombre predecible
            try {
                DB::statement("ALTER TABLE `anfitriones_experiencias` ADD CONSTRAINT `anfitriones_experiencias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
            } catch (\Exception $e) {
                // Lanzar para que la migración deje claro el fallo
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
            DB::statement("ALTER TABLE `anfitriones_experiencias` DROP FOREIGN KEY `anfitriones_experiencias_user_id_foreign`");
        } catch (\Exception $e) {
            // Ignorar si no existe
        }
    }
};
