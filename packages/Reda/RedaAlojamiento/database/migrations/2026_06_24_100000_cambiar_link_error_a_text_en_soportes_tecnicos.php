<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CambiarLinkErrorATextEnSoportesTecnicos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('soportes_tecnicos', function (Blueprint $table) {
            $table->text('link_error')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('soportes_tecnicos', function (Blueprint $table) {
            $table->string('link_error', 255)->nullable()->change();
        });
    }
}
