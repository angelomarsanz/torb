<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateReservacionExperienciasAddFkHorario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Revisar si ya existe la FK
        $exists = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservacion_experiencias' AND COLUMN_NAME = 'horarios_experiencia_id' AND REFERENCED_TABLE_NAME = 'horarios_experiencias'");

        if (empty($exists) || $exists[0]->cnt == 0) {
            // Intentar ajustar tipo a unsignedBigInteger para que coincida con horarios_experiencias.id (creado con $table->id())
            try {
                Schema::table('reservacion_experiencias', function (Blueprint $table) {
                    if (Schema::hasColumn('reservacion_experiencias', 'horarios_experiencia_id')) {
                        $table->unsignedBigInteger('horarios_experiencia_id')->change();
                    }
                });
            } catch (\Exception $e) {
                // Si change() no está disponible, seguimos intentando el ALTER
            }

            // Añadir la constraint con nombre predecible
            try {
                DB::statement("ALTER TABLE `reservacion_experiencias` ADD CONSTRAINT `reservacion_experiencias_horarios_experiencia_id_foreign` FOREIGN KEY (`horarios_experiencia_id`) REFERENCES `horarios_experiencias`(`id`) ON DELETE CASCADE");
            } catch (\Exception $e) {
                // Propagar para que la migración falle con el error si ocurre
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
            DB::statement("ALTER TABLE `reservacion_experiencias` DROP FOREIGN KEY `reservacion_experiencias_horarios_experiencia_id_foreign`");
        } catch (\Exception $e) {
            // Ignorar si no existe
        }
    }
}
