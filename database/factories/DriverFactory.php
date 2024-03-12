<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\DriverLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = Faker::create();
        $rut = $faker->regexify('[1-9]{2}[0-9]{6}');
        $rev = strrev($rut);
        $sum = 0;
        $multiplier = 2;
        for ($i = 0; $i < strlen($rev); $i++) {
            $sum += intval($rev[$i]) * $multiplier;
            
            $multiplier++;
            if ($multiplier > 7) {
                $multiplier = 2;
            }
        }

        $modulus = $sum % 11;
        $checkDigit = 11 - $modulus;

        $digit = 0;
        
        if ($checkDigit == 10) {
            $digit = "K";
        } elseif ($checkDigit == 11) {
            $digit = "0";
        } else {
            $digit = strval($checkDigit);
        }

        return [
            'rut' => $faker->numerify($rut.'-'.$digit),
            'nombre' => $faker->firstName().' '.$faker->lastName(),
            'activo' => $faker->boolean(),
        ];
    }

    public function withLog()
    {
        return $this->afterCreating(function (Driver $driver) {
            $driver->driver_logs()->save(DriverLog::factory()->make());
        });
    }
}
