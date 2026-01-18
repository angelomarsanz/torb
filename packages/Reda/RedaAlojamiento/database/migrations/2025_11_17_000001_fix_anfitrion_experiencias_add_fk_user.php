<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixAnfitrionExperienciasAddFkUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Comprobamos si ya existe la FK para evitar errores en entornos ya corregidos
        $exists = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'anfitrion_experiencias' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users'");

        if (empty($exists) || $exists[0]->cnt == 0) {
            // Aseguramos que la columna user_id tiene el tipo correcto (unsigned INT) antes de añadir la FK.
            try {
                Schema::table('anfitrion_experiencias', function (Blueprint $table) {
                    if (Schema::hasColumn('anfitrion_experiencias', 'user_id')) {
                        // Intentamos cambiar el tipo a unsignedInteger
                        $table->unsignedInteger('user_id')->change();
                    }
                });
            } catch (\Exception $e) {
                // Si el change() no está disponible (falta doctrine/dbal), intentaremos con una sentencia SQL directa
                try {
                    DB::statement("ALTER TABLE `anfitrion_experiencias` MODIFY `user_id` INT UNSIGNED NOT NULL");
                } catch (\Exception $e2) {
                    // Si esto falla, relanzamos la excepción original para que se note el problema
                    throw $e;
                }
            }

            // Aseguramos motor InnoDB (por si acaso)
            try {
                DB::statement("ALTER TABLE `anfitrion_experiencias` ENGINE=InnoDB");
            } catch (\Exception $e) {
                // ignoramos si no se puede cambiar
            }

            // Añadimos la constraint con un nombre predecible
            try {
                DB::statement("ALTER TABLE `anfitrion_experiencias` ADD CONSTRAINT `anfitrion_experiencias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
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
            DB::statement("ALTER TABLE `anfitrion_experiencias` DROP FOREIGN KEY `anfitrion_experiencias_user_id_foreign`");
        } catch (\Exception $e) {
            // Si no existe la FK, ignoramos el error
        }
    }
}
