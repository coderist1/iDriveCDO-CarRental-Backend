<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pickupDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $returnDate = (clone $pickupDate)->modify('+'.fake()->numberBetween(1, 5).' days');

        return [
            'driver_details_id' => null,
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),

            'pickup_time' => fake()->time('H:i:s'),
            'pickup_date' => $pickupDate->format('Y-m-d'),
            'return_time' => fake()->time('H:i:s'),
            'return_date' => $returnDate->format('Y-m-d'),

            'payment_method' => fake()->randomElement(['Cash', 'GCash', 'Bank Transfer']),
            'number_of_passenger' => fake()->numberBetween(1, 7),
            'driver_option' => 'Self Drive',

            'fuel_before_rent' => fake()->randomFloat(2, 20, 100),
            'fuel_upon_return' => fake()->randomFloat(2, 0, 100),

            'date_reserve' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'booking_status' => fake()->randomElement(['Pending', 'Confirmed', 'Completed', 'Cancelled']),
        ];
    }
}
