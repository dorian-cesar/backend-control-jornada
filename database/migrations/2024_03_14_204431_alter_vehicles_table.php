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
        Schema::table('vehicles', function (Blueprint $table){
            $table->string('estado')->default('');
            $table->string('conexion')->default('');
            $table->string('imei')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('estado');
                $table->dropColumn('conexion');
                $table->dropColumn('imei');
        });
    }
};
