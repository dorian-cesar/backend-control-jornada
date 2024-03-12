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
        Schema::dropIfExists('vehicles');
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('patente',8);
            $table->foreignId('vehicle_type_id')
            ->constrained('vehicle_types');
            $table->foreignId('vehicle_state_id')
            ->constrained('vehicle_states');
            $table->foreignId('company_id')
            ->constrained('companies');
            $table->unsignedBigInteger('device_id')->unique()->nullable();
            $table->foreign('device_id')
            ->references('id')
            ->on('devices')
            ->nullOnDelete();
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
