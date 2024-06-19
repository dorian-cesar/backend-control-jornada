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
        Schema::dropIfExists('driver_logs');
        Schema::create('driver_logs', function (Blueprint $table) {
            $table->id();
            $table->string('coordenadas',50);
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')
            ->references('id')
            ->on('drivers')
            ->nullOnDelete();
            $table->foreignId('event_id')
            ->constrained('events');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_logs');
    }
};
