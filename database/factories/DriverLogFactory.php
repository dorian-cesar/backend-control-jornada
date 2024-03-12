<?php

namespace Database\Factories;

use App\Models\DriverLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverLogFactory extends Factory
{
    protected $model = DriverLog::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = Faker::create();
        return [
            'coordenadas' => '-33.40987,-70.57134',
            'velocidad' => 0,
            'event_id' => 1,
        ];
    }
}
