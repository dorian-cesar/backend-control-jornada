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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vh_plate');
            $table->boolean('vh_enabled');
            $table->unsignedBigInteger('ccv_id')
            ->unique()
            ->nullable();
            $table->unsignedBigInteger('driver_id')
            ->nullable();
            $table->foreign('ccv_id')
            ->references('id')
            ->on('ccvs')
            ->nullOnDelete()
            ->cascadeOnUpdate();
            $table->foreign('driver_id')
            ->references('id')
            ->on('drivers')
            ->nullOnDelete()
            ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
