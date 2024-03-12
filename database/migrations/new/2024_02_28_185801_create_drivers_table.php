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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('dv_name');
            $table->string('dv_rut');
            $table->boolean('dv_enabled');
            $table->unsignedBigInteger('smartcard_id')
            ->unique()
            ->nullable();
            $table->unsignedBigInteger('company_id')
            ->nullable();
            $table->foreign('smartcard_id')
            ->references('id')
            ->on('smartcards')
            ->nullOnDelete()
            ->cascadeOnUpdate();
            $table->foreign('company_id')
            ->references('id')
            ->on('companies')
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
        Schema::dropIfExists('drivers');
    }
};
