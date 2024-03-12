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
        // La siguiente tabla asume como se obtendran los datos desde el
        // CCV, puede que sean completamente distintos
        Schema::create('ccvlogs', function (Blueprint $table) {
            $table->id();
            $table->string('ccvlog_coordinates');
            $table->integer('ccvlog_speed');
            $table->date('ccvlog_date');
            $table->time('ccvlog_time');
            $table->unsignedBigInteger('ccv_id');
            $table->foreign('smartcard_id')
            ->references('id')
            ->on('ccvs')
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
        Schema::dropIfExists('ccvlogs');
    }
};
