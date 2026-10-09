<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Maintenance>
 */
class MaintenanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduled = fake()->dateTimeBetween('-2 months', '+2 months');
        $finished = $scheduled < now() && fake()->boolean(70);

        return [
            'vehicle_id' => Vehicle::factory(),
            'maintenance_type' => fake()->randomElement(['Oil Change', 'Tire Rotation', 'Brake Inspection', 'Aircon Cleaning', 'General Check-up']),
            'scheduled_date' => $scheduled->format('Y-m-d'),
            'performed_at' => $finished ? $scheduled->format('Y-m-d') : null,
            'finished' => $finished,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
