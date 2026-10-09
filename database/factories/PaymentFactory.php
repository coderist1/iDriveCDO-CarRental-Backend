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
            'booking_id' => Booking::factory()->paid(),
            'amount' => fn (array $attributes) => Booking::find($attributes['booking_id'])->total,
            'payment_method' => 'cash',
            'brand' => 'Cash',
            'payment_status' => 'paid',
            'payment_date' => now(),
        ];
    }

    public function cashless(string $brand = 'GCash'): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cashless',
            'brand' => $brand,
            'account_last4' => fake()->numerify('####'),
        ]);
    }

    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'card',
            'brand' => fake()->randomElement(['Visa', 'Mastercard']),
            'account_last4' => fake()->numerify('####'),
            'holder' => fake()->name(),
        ]);
    }
}
