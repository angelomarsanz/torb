<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDisputasMessageType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si ya existe para evitar duplicados
        $exists = DB::table('message_type')->where('name', 'disputas')->exists();
        
        if (!$exists) {
            DB::table('message_type')->insert([
                'name' => 'disputas',
                'description' => 'Mensajes relacionados con mediaciones o disputas'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('message_type')->where('name', 'disputas')->delete();
    }
}
