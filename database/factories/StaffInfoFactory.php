<?php

namespace Database\Factories;

use App\Models\StaffInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffInfo>
 */
class StaffInfoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'staff_full_name' => fake()->name(),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'department' => fake()->randomElement(['Operations', 'Finance', 'Maintenance', 'Front Desk']),
        ];
    }
}
