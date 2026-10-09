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
        $type = fake()->randomElement(Vehicle::TYPES);
        $brand = fake()->randomElement(['Toyota', 'Honda', 'Mitsubishi', 'Ford', 'Nissan']);
        $model = fake()->randomElement(['Vios', 'City', 'Mirage', 'Fortuner', 'Montero', 'Hiace', 'Ranger', 'Navara']);
        $year = fake()->numberBetween(2018, 2025);

        return [
            'name' => "{$brand} {$model} {$year}",
            'brand' => $brand,
            'model' => $model,
            'year_model' => $year,
            'year_purchased' => $year,
            'type' => $type,
            'transmission' => fake()->randomElement(Vehicle::TRANSMISSIONS),
            'fuel' => fake()->randomElement(Vehicle::FUELS),
            'capacity' => match ($type) {
                'Van' => 12,
                'SUV' => 7,
                default => 5,
            },
            'luggage' => fake()->numberBetween(1, 6),
            'mileage' => fake()->numberBetween(1000, 120000),
            'daily_rate' => fake()->numberBetween(15, 80) * 100,
            'plate_number' => fake()->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
            'description' => fake()->sentence(),
            'status' => 'available',
        ];
    }
}
