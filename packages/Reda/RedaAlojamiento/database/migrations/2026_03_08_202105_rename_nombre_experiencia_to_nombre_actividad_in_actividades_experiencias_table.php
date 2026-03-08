<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Usamos el nombre de la clase explícito
class RenameNombreExperienciaToNombreActividadInActividadesExperienciasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('actividades_experiencias', function (Blueprint $blueprint) {
            // Laravel usa Doctrine DBAL internamente para esto
            $blueprint->renameColumn('nombre_experiencia', 'nombre_actividad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividades_experiencias', function (Blueprint $blueprint) {
            $blueprint->renameColumn('nombre_actividad', 'nombre_experiencia');
        });
    }
}