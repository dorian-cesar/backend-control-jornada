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
        Schema::create('vehicle_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->foreign('vehicle_id')
            ->references('track_id')
            ->on('vehicles')
            ->nullOnDelete();
            $table->integer('velocidad')->default(0);
            $table->integer('direccion')->default(0);
            $table->boolean('ignicion')->default(false);
            $table->float('lat')->default(0.0);
            $table->float('lng')->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_logs');
    }
};
