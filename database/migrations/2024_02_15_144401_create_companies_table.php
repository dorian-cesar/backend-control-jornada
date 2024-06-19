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
        Schema::dropIfExists('companies');
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('rut',15)->unique();
            $table->string('nombre',50);
            $table->string('razon', 50);
            $table->foreignId('company_state_id')
            ->constrained('company_states');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
