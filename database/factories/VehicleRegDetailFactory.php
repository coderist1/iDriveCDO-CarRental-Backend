<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleRegDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleRegDetail>
 */
class VehicleRegDetailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $renewalDay = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'vehicle_id' => Vehicle::factory(),
            'plate_number' => strtoupper(fake()->bothify('???-####')),
            'renewal_scheduled_day' => $renewalDay->format('Y-m-d'),
            'next_reg_renewal' => (clone $renewalDay)->modify('+1 year')->format('Y-m-d'),
        ];
    }
}
