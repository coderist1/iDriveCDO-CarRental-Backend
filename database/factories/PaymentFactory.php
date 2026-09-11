<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount' => fake()->randomFloat(2, 500, 25000),
            'payment_method' => fake()->randomElement(['Cash', 'GCash', 'Bank Transfer']),
            'reference_number' => strtoupper(fake()->bothify('REF-########')),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'payment_status' => fake()->randomElement(['Pending', 'Paid']),
        ];
    }
}
