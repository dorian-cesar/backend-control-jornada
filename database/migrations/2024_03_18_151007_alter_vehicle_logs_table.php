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
        Schema::table('vehicle_logs', function (Blueprint $table){
            $table->dropColumn('lat');
            $table->dropColumn('lng');
        });
        Schema::table('vehicle_logs', function (Blueprint $table){
            $table->float('lat',9,7)->default(0.0);
            $table->float('lng',9,7)->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_logs', function (Blueprint $table){
            $table->dropColumn('lat');
            $table->dropColumn('lng');
        });
        Schema::table('vehicle_logs', function (Blueprint $table){
            $table->float('lat')->default(0.0);
            $table->float('lng')->default(0.0);
        });
    }
};
