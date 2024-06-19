<?php

namespace Database\Seeders;

use App\Models\Smartcard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmartcardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Smartcard::factory()->count(10)->create();
    }
}
