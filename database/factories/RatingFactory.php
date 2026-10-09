<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory()->completed(),
            'user_id' => fn (array $attributes) => Booking::find($attributes['booking_id'])->user_id,
            'vehicle_id' => fn (array $attributes) => Booking::find($attributes['booking_id'])->vehicle_id,
            'stars' => fake()->numberBetween(3, 5),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
