<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('driver_logs', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });

        DB::table('events')->truncate();

        DB::table('events')->insert([
            'evento' => 'Registrado'
        ]);

        DB::table('events')->insert([
            'evento' => 'Vehiculo Asignado'
        ]);

        DB::table('events')->insert([
            'evento' => 'Tarjeta Asignada'
        ]);

        DB::table('events')->insert([
            'evento' => 'Inicio de Jornada'
        ]);

        DB::table('events')->insert([
            'evento' => 'Fin de Jornada'
        ]);

        DB::table('events')->insert([
            'evento' => 'Inicio de Descanso'
        ]);

        DB::table('events')->insert([
            'evento' => 'Fin de Descanso'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
