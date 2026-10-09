<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Location;
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
        $start = fake()->dateTimeBetween('+1 day', '+2 months');
        $days = fake()->numberBetween(1, 5);

        return [
            'user_id' => User::factory()->customer(),
            'vehicle_id' => Vehicle::factory(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => (clone $start)->modify("+{$days} days")->format('Y-m-d'),
            'pickup_time' => '09:00',
            'return_time' => '09:00',
            'pickup_location_id' => Location::factory(),
            'dropoff_location_id' => fn (array $attributes) => $attributes['pickup_location_id'],
            'number_of_passengers' => fake()->numberBetween(1, 4),
            'drive_mode' => 'self',
            'fuel_before_rent' => 'Full',
            'subtotal' => $days * 2000,
            'extras' => 0,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ];
    }

    /**
     * A paid booking in the given status (confirmed, ongoing, return_requested or completed).
     */
    public function paid(string $status = 'confirmed', string $method = 'cash'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'payment_status' => 'paid',
            'payment_method' => $method,
        ]);
    }

    public function completed(): static
    {
        return $this->paid('completed')->state(fn (array $attributes) => [
            'fuel_upon_return' => 'Full',
            'returned_at' => now(),
        ]);
    }

    public function chauffeur(): static
    {
        return $this->state(fn (array $attributes) => [
            'drive_mode' => 'chauffeur',
            'driver_id' => Driver::factory(),
        ]);
    }
}
