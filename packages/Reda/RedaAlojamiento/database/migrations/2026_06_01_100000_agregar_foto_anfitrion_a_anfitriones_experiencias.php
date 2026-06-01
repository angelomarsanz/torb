<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('anfitriones_experiencias', function (Blueprint $table) {
            $table->string('foto_anfitrion', 255)->nullable()->after('trayectoria_profesional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anfitriones_experiencias', function (Blueprint $table) {
            $table->dropColumn('foto_anfitrion');
        });
    }
};
