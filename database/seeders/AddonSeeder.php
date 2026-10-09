<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Seed the optional extras a renter can add to a booking.
     */
    public function run(): void
    {
        $addons = [
            ['add_child_seat', 'Child Seat', 250],
            ['add_gps', 'GPS Navigator', 150],
            ['add_wifi', 'Pocket Wi-Fi', 200],
            ['add_insurance', 'Full Insurance Cover', 500],
            ['add_cooler', 'Cooler Box', 100],
        ];

        foreach ($addons as [$code, $name, $rate]) {
            Addon::updateOrCreate(['code' => $code], ['name' => $name, 'daily_rate' => $rate, 'is_active' => true]);
        }
    }
}
