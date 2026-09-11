<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\CustomerInfo;
use App\Models\DriverDetail;
use App\Models\FuelRecord;
use App\Models\Payment;
use App\Models\StaffInfo;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\VehicleRegDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        StaffInfo::factory()->create([
            'user_id' => $admin->id,
            'staff_full_name' => $admin->name,
            'department' => 'Operations',
        ]);

        User::factory(3)->create()->each(function (User $staff) {
            StaffInfo::factory()->create([
                'user_id' => $staff->id,
                'staff_full_name' => $staff->name,
            ]);
        });

        $customers = User::factory(8)->create();

        $customers->each(function (User $customer) {
            CustomerInfo::factory()->create([
                'user_id' => $customer->id,
                'customer_full_name' => $customer->name,
            ]);
        });

        $drivers = DriverDetail::factory(5)->create();

        $vehicles = Vehicle::factory(6)->create();

        $vehicles->each(function (Vehicle $vehicle) {
            VehicleRegDetail::factory()->create([
                'vehicle_id' => $vehicle->vehicle_id,
                'plate_number' => $vehicle->plate_number,
            ]);

            VehicleMaintenance::factory(2)->create([
                'vehicle_id' => $vehicle->vehicle_id,
            ]);

            FuelRecord::factory(3)->create([
                'vehicle_id' => $vehicle->vehicle_id,
            ]);
        });

        foreach (range(1, 12) as $ignored) {
            $driver = fake()->boolean(60) ? $drivers->random() : null;

            $booking = Booking::factory()->create([
                'user_id' => $customers->random()->id,
                'vehicle_id' => $vehicles->random()->vehicle_id,
                'driver_details_id' => $driver?->driver_details_id,
                'driver_option' => $driver ? 'With Driver' : 'Self Drive',
            ]);

            if ($booking->booking_status !== 'Cancelled') {
                Payment::factory()->create([
                    'booking_id' => $booking->booking_id,
                    'payment_method' => $booking->payment_method,
                    'payment_status' => $booking->booking_status === 'Completed' ? 'Paid' : 'Pending',
                ]);
            }
        }
    }
}
