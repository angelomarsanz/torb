<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateReservacionExperienciasAddFkUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Comprobamos si ya existe la FK para evitar errores en entornos ya corregidos
        $exists = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservacion_experiencias' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users'");

        if (empty($exists) || $exists[0]->cnt == 0) {
            // Aseguramos que la columna user_id tiene el tipo correcto (unsigned INT) antes de añadir la FK.
            // Intentamos cambiar el tipo si es necesario; esto requiere doctrine/dbal en algunos proyectos.
            try {
                Schema::table('reservacion_experiencias', function (Blueprint $table) {
                    // Intentamos forzar tipo a unsignedInteger (si fallara por falta de doctrine/dbal, seguirá adelante)
                    if (Schema::hasColumn('reservacion_experiencias', 'user_id')) {
                        $table->unsignedInteger('user_id')->change();
                    }
                });
            } catch (\Exception $e) {
                // No hacemos nada aquí: si change() no está disponible, el siguiente ALTER puede todavía funcionar
            }

            // Añadimos la constraint con un nombre predecible
            try {
                DB::statement("ALTER TABLE `reservacion_experiencias` ADD CONSTRAINT `reservacion_experiencias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
            } catch (\Exception $e) {
                // Lanzamos la excepción para que la migración deje claro el fallo si ocurre
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            DB::statement("ALTER TABLE `reservacion_experiencias` DROP FOREIGN KEY `reservacion_experiencias_user_id_foreign`");
        } catch (\Exception $e) {
            // Si no existe la FK, ignoramos el error
        }
    }
}
