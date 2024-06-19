<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('nombre');
            $table->dropForeign(['company_state_id']);
            $table->dropColumn('company_state_id');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('estado');
            DB::statement("ALTER TABLE `companies` CHANGE `razon` `nombre` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            DB::statement("ALTER TABLE `companies` CHANGE `nombre` `razon` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        //    $table->dropColumn('estado');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->string('nombre');
        //    $table->foreignId('company_state_id')
        //    ->constrained('company_states');
        });
    }
};
