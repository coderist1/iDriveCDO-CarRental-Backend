<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $yearPurchased = fake()->numberBetween(2016, 2025);

        return [
            'plate_number' => strtoupper(fake()->unique()->bothify('???-####')),
            'mileage' => fake()->numberBetween(0, 150000),
            'brand' => fake()->randomElement(['Toyota', 'Mitsubishi', 'Honda', 'Nissan', 'Hyundai', 'Suzuki']),
            'model' => fake()->randomElement(['Vios', 'Mirage', 'City', 'Almera', 'Accent', 'Ertiga']),
            'type' => fake()->randomElement(['Sedan', 'SUV', 'Van', 'Hatchback', 'Pickup']),
            'capacity' => fake()->randomElement([4, 5, 7, 8, 12]),
            'year_model' => fake()->numberBetween(2015, $yearPurchased),
            'year_purchased' => $yearPurchased,
        ];
    }
}
