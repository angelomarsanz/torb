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
        // 1) experiencia_id -> experiencias(id)
        $existsExp = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservaciones_experiencias' AND COLUMN_NAME = 'experiencia_id' AND REFERENCED_TABLE_NAME = 'experiencias'");
        if (empty($existsExp) || $existsExp[0]->cnt == 0) {
            try {
                Schema::table('reservaciones_experiencias', function (Blueprint $table) {
                    if (Schema::hasColumn('reservaciones_experiencias', 'experiencia_id')) {
                        $table->unsignedBigInteger('experiencia_id')->change();
                    }
                });
            } catch (\Exception $e) {
                try {
                    DB::statement("ALTER TABLE `reservaciones_experiencias` MODIFY `experiencia_id` BIGINT UNSIGNED NOT NULL");
                } catch (\Exception $e2) {
                    throw $e;
                }
            }

            try {
                DB::statement("ALTER TABLE `reservaciones_experiencias` ADD CONSTRAINT `reservaciones_experiencias_experiencia_id_foreign` FOREIGN KEY (`experiencia_id`) REFERENCES `experiencias`(`id`) ON DELETE CASCADE");
            } catch (\Exception $e) {
                throw $e;
            }
        }

        // 2) user_id -> users(id)
        $existsUser = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservaciones_experiencias' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users'");
        if (empty($existsUser) || $existsUser[0]->cnt == 0) {
            try {
                Schema::table('reservaciones_experiencias', function (Blueprint $table) {
                    if (Schema::hasColumn('reservaciones_experiencias', 'user_id')) {
                        $table->unsignedBigInteger('user_id')->change();
                    }
                });
            } catch (\Exception $e) {
                try {
                    DB::statement("ALTER TABLE `reservaciones_experiencias` MODIFY `user_id` BIGINT UNSIGNED NOT NULL");
                } catch (\Exception $e2) {
                    // Si falla, relanzamos
                    throw $e;
                }
            }

            try {
                DB::statement("ALTER TABLE `reservaciones_experiencias` ADD CONSTRAINT `reservaciones_experiencias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
            } catch (\Exception $e) {
                throw $e;
            }
        }

        // 3) horarios_experiencia_id -> horarios_experiencias(id)
        $existsHorario = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservaciones_experiencias' AND COLUMN_NAME = 'horarios_experiencia_id' AND REFERENCED_TABLE_NAME = 'horarios_experiencias'");
        if (empty($existsHorario) || $existsHorario[0]->cnt == 0) {
            try {
                Schema::table('reservaciones_experiencias', function (Blueprint $table) {
                    if (Schema::hasColumn('reservaciones_experiencias', 'horarios_experiencia_id')) {
                        $table->unsignedBigInteger('horarios_experiencia_id')->change();
                    }
                });
            } catch (\Exception $e) {
                try {
                    DB::statement("ALTER TABLE `reservaciones_experiencias` MODIFY `horarios_experiencia_id` BIGINT UNSIGNED NOT NULL");
                } catch (\Exception $e2) {
                    throw $e;
                }
            }

            try {
                DB::statement("ALTER TABLE `reservaciones_experiencias` ADD CONSTRAINT `reservaciones_experiencias_horarios_experiencia_id_foreign` FOREIGN KEY (`horarios_experiencia_id`) REFERENCES `horarios_experiencias`(`id`) ON DELETE CASCADE");
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
            DB::statement("ALTER TABLE `reservaciones_experiencias` DROP FOREIGN KEY `reservaciones_experiencias_experiencia_id_foreign`");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE `reservaciones_experiencias` DROP FOREIGN KEY `reservaciones_experiencias_user_id_foreign`");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE `reservaciones_experiencias` DROP FOREIGN KEY `reservaciones_experiencias_horarios_experiencia_id_foreign`");
        } catch (\Exception $e) {}
    }
};
