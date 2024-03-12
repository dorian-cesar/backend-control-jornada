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
        Schema::table('driver_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable();
            
            $table->foreign('event_id')
                ->references('id')
                ->on('events')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_logs', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');

            $table->foreignId('event_id')
            ->constrained('events');
        });
    }
};
