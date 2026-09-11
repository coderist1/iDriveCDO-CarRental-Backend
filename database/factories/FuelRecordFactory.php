<?php

namespace Database\Factories;

use App\Models\FuelRecord;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelRecord>
 */
class FuelRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'fuel_type' => fake()->randomElement(['Gasoline', 'Diesel', 'Premium']),
            'quantity' => fake()->randomFloat(2, 10, 60),
            'fuel_cost' => fake()->randomFloat(2, 500, 5000),
            'fuel_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'mileage' => fake()->randomFloat(2, 1000, 150000),
        ];
    }
}
