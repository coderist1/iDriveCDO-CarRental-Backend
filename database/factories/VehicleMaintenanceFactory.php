<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleMaintenance>
 */
class VehicleMaintenanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduled = fake()->dateTimeBetween('-3 months', '+2 months');
        $isDone = $scheduled < now();

        return [
            'vehicle_id' => Vehicle::factory(),
            'maintenance_type' => fake()->randomElement([
                'Oil Change',
                'Brake Service',
                'Tire Rotation',
                'Engine Tune-up',
                'Aircon Cleaning',
            ]),
            'scheduled_date' => $scheduled->format('Y-m-d'),
            'performed_at' => $isDone ? $scheduled->format('Y-m-d') : null,
            'finish' => $isDone ? 'Yes' : null,
        ];
    }
}
