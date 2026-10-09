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
            'fuel_type' => fn (array $attributes) => Vehicle::find($attributes['vehicle_id'])->fuel,
            'notes' => fake()->optional()->sentence(4),
            'recorded_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
