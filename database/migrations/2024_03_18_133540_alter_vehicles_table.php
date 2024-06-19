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
        Schema::table('vehicles', function (Blueprint $table){
            $table->index('track_id');
            $table->dropForeign(['vehicle_state_id']);
            $table->dropColumn('vehicle_state_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
                $table->dropIndex('track_id');
                $table->foreignId('vehicle_state_id')
                ->constrained('vehicle_state_id');
        });
    }
};
