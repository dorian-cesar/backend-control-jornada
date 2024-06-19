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
        Schema::dropIfExists('drivers');
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('rut',15)->unique();
            $table->string('nombre',50);
            $table->boolean('activo');
            $table->unsignedBigInteger('smartcard_id')->unique()->nullable();
            $table->foreign('smartcard_id')
            ->references('id')
            ->on('smartcards')
            ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
