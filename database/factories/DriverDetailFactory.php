<?php

namespace Database\Factories;

use App\Models\DriverDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverDetail>
 */
class DriverDetailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fullName' => fake()->name(),
            'Driver_License' => strtoupper(fake()->unique()->bothify('?##-##-######')),
            'type_DriverLicense' => fake()->randomElement(['Professional', 'Non-Professional']),
            'Driver_License_Expiry' => fake()->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
            'status' => 'available',
        ];
    }
}
