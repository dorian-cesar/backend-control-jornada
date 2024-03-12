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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('cmp_name');
            $table->string('cmp_rut');
            $table->boolean('cmp_enabled');
            $table->timestamps();
        });

        DB::table('companies')->insert([
            'cmp_name' => 'Empresa Las Condes Norte',
            'cmp_rut' => '80800800-9',
            'cmp_enabled' => True,
        ]);

        DB::table('companies')->insert([
            'cmp_name' => 'Empresa Las Condes Centro',
            'cmp_rut' => '85700300-4',
            'cmp_enabled' => True,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
