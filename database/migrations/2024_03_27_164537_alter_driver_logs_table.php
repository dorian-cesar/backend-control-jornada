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
        });
        Schema::table('driver_logs', function (Blueprint $table) {
            $table->dropColumn('event_id');
            $table->dropColumn('coordenadas');
            $table->dropColumn('velocidad');
            $table->string('event');
        });
        DB::table('driver_logs')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_logs', function (Blueprint $table) {
            $table->dropColumn('event');
            $table->string('coordenadas',50);
            $table->integer('velocidad');
            $table->unsignedBigInteger('event_id')->nullable();
            
            $table->foreign('event_id')
                ->references('id')
                ->on('events')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
