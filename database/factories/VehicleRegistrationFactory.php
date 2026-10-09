<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleRegistration>
 */
class VehicleRegistrationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'plate_number' => fn (array $attributes) => Vehicle::find($attributes['vehicle_id'])->plate_number,
            'renewal_scheduled_day' => fake()->numberBetween(1, 28),
            'next_reg_renewal' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
        ];
    }
}
