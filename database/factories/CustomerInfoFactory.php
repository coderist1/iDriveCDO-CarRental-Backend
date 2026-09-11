<?php

namespace Database\Factories;

use App\Models\CustomerInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerInfo>
 */
class CustomerInfoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_full_name' => fake()->name(),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'driver_license' => strtoupper(fake()->bothify('?##-##-######')),
        ];
    }
}
