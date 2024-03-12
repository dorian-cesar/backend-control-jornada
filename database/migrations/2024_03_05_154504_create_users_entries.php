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
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@wit.la',
            'password' => bcrypt('adminpass'),
            'level' => '10',
        ]);

        DB::table('users')->insert([
            'name' => 'User',
            'email' => 'user@wit.la',
            'password' => bcrypt('userpass'),
            'level' => '1',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->truncate();
    }
};
