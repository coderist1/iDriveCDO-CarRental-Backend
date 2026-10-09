<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'driver_license' => fake()->unique()->regexify('[A-Z][0-9]{2}-[0-9]{2}-[0-9]{6}'),
            'type_driver_license' => 'Professional',
            'license_expiry' => fake()->dateTimeBetween('+6 months', '+5 years')->format('Y-m-d'),
            'phone' => fake()->numerify('09#########'),
            'status' => 'active',
            'duty_status' => fake()->randomElement(Driver::DUTY_STATUSES),
        ];
    }
}
