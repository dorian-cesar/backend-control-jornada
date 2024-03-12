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
        Schema::create('cardlogs', function (Blueprint $table) {
            $table->id();
            $table->date('cardlog_date');
            $table->time('cardlog_time');
            $table->unsignedBigInteger('smartcard_id');
            $table->foreign('smartcard_id')
            ->references('id')
            ->on('smartcards')
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
        Schema::dropIfExists('cardlogs');
    }
};
