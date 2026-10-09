<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\FuelRecord;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\MaintenancePrediction;
use App\Models\MessageThread;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\TelemetryReading;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with catalog data and a consistent demo data set.
     *
     * Model events stay enabled: they generate codes, booking refs and totals.
     */
    public function run(): void
    {
        $this->call([LocationSeeder::class, AddonSeeder::class, FleetSeeder::class]);

        DB::transaction(function () {
            $admin = User::factory()->admin()->create([
                'first_name' => 'Test',
                'last_name' => 'Admin',
                'email' => 'test@example.com',
            ]);

            $staff = User::factory(2)->staff()->create();
            $customers = User::factory(6)->customer()->create();

            $drivers = User::factory(3)->driver()->create()->map(fn (User $user) => Driver::factory()->create([
                'user_id' => $user->user_id,
                'full_name' => $user->name,
                'phone' => $user->phone,
            ]));
            $drivers->push(Driver::factory()->create(['duty_status' => 'on_call']));

            $vehicles = Vehicle::all()->keyBy('code');

            $this->seedFleetRecords($vehicles, $staff->first());
            $this->seedBookings($vehicles, $customers, $drivers, $staff->first());

            AuditLog::create(['action' => 'seed', 'user_id' => $admin->user_id, 'detail' => 'Demo data seeded.']);
        });
    }

    /**
     * @param  Collection<string, Vehicle>  $vehicles
     */
    private function seedFleetRecords(Collection $vehicles, User $staff): void
    {
        foreach ($vehicles as $vehicle) {
            VehicleRegistration::factory()->create([
                'vehicle_id' => $vehicle->vehicle_id,
                'plate_number' => $vehicle->plate_number,
            ]);

            Maintenance::factory(2)->create(['vehicle_id' => $vehicle->vehicle_id, 'created_by' => $staff->user_id]);

            FuelRecord::factory(2)->create([
                'vehicle_id' => $vehicle->vehicle_id,
                'fuel_type' => $vehicle->fuel,
                'recorded_by' => $staff->user_id,
            ]);
        }

        foreach ($vehicles->take(3) as $vehicle) {
            $reading = TelemetryReading::create([
                'vehicle_id' => $vehicle->vehicle_id,
                'brand' => $vehicle->brand,
                'reading_time' => now()->subHours(2),
                'odometer_reading' => $vehicle->mileage,
                'engine_temp_c' => 92.5,
                'engine_rpm' => 2100,
                'oil_pressure_psi' => 42,
                'coolant_temp_c' => 88,
                'fuel_level_percent' => 64,
                'fuel_consumption_lph' => 6.8,
                'engine_load_percent' => 38,
                'throttle_pos_percent' => 22,
                'air_flow_rate_gps' => 14.2,
                'exhaust_gas_temp_c' => 410,
                'vibration_level' => 1.4,
                'engine_hours' => 1850,
                'brake_fluid_level_psi' => 1200,
                'brake_pad_wear_mm' => 7.5,
                'brake_temp_c' => 120,
                'abs_fault_indicator' => 0,
                'brake_pedal_pos_percent' => 0,
                'wheel_speed_fl_kph' => 60,
                'wheel_speed_fr_kph' => 60,
                'wheel_speed_rl_kph' => 60,
                'wheel_speed_rr_kph' => 60,
                'battery_voltage_v' => 13.9,
                'battery_current_a' => 12,
                'battery_temp_c' => 34,
                'alternator_output_v' => 14.1,
                'battery_charge_percent' => 88,
                'battery_health_percent' => 91,
                'vehicle_speed_kph' => 60,
                'ambient_temp_c' => 31,
                'humidity_percent' => 74,
                'recorded_by' => $staff->user_id,
            ]);

            MaintenancePrediction::create([
                'vehicle_id' => $vehicle->vehicle_id,
                'telemetry_id' => $reading->telemetry_id,
                'prediction' => 0,
                'needs_maintenance' => false,
                'probability' => 0.12,
                'predicted_by' => $staff->user_id,
            ]);
        }
    }

    /**
     * One booking per scenario, each on its own vehicle so active bookings never overlap.
     *
     * @param  Collection<string, Vehicle>  $vehicles
     * @param  Collection<int, User>  $customers
     * @param  Collection<int, Driver>  $drivers
     */
    private function seedBookings(Collection $vehicles, Collection $customers, Collection $drivers, User $staff): void
    {
        $locations = Location::orderBy('sort_order')->get();
        $addons = Addon::all()->keyBy('code');

        // [vehicle code, start offset in days, days, status, drive mode, payment method, add-on codes, passengers]
        $scenarios = [
            ['veh_ranger', -30, 2, 'completed', 'self', 'cash', [], 2],
            ['veh_vios', -20, 3, 'completed', 'self', 'cashless', ['add_gps'], 3],
            ['veh_fortuner', -15, 4, 'completed', 'chauffeur', 'card', ['add_child_seat'], 6],
            ['veh_city', -10, 2, 'cancelled', 'self', null, [], 2],
            ['veh_crv', -3, 3, 'return_requested', 'self', 'cashless', [], 4],
            ['veh_hiace', -2, 4, 'ongoing', 'chauffeur', 'cash', ['add_wifi'], 10],
            ['veh_montero', 3, 2, 'confirmed', 'self', 'card', ['add_insurance'], 5],
            ['veh_mirage', 5, 3, 'pending', 'self', null, [], 2],
            ['veh_everest', 7, 2, 'rejected', 'self', null, [], 3],
            ['veh_hilux', 10, 5, 'pending', 'chauffeur', null, ['add_cooler'], 4],
        ];

        foreach ($scenarios as $i => [$code, $offset, $days, $status, $mode, $method, $addonCodes, $passengers]) {
            $vehicle = $vehicles[$code];
            $customer = $customers[$i % $customers->count()];
            $chosen = $addons->only($addonCodes);
            $start = today()->addDays($offset);
            $paid = $method !== null;

            $booking = Booking::create([
                'user_id' => $customer->user_id,
                'vehicle_id' => $vehicle->vehicle_id,
                'driver_id' => $mode === 'chauffeur' ? $drivers[$i % $drivers->count()]->driver_id : null,
                'created_by' => $staff->user_id,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays($days)->toDateString(),
                'pickup_location_id' => $locations[$i % $locations->count()]->location_id,
                'dropoff_location_id' => $locations[($i + 1) % $locations->count()]->location_id,
                'number_of_passengers' => $passengers,
                'drive_mode' => $mode,
                'fuel_before_rent' => 'Full',
                'fuel_upon_return' => $status === 'completed' ? '3/4' : null,
                'subtotal' => $vehicle->daily_rate * $days,
                'extras' => $chosen->sum(fn (Addon $addon) => $addon->daily_rate * $days),
                'status' => $status,
                'payment_status' => $paid ? 'paid' : 'unpaid',
                'payment_method' => $method,
                'started_at' => in_array($status, ['ongoing', 'return_requested', 'completed'], true) ? $start : null,
                'started_by' => in_array($status, ['ongoing', 'return_requested', 'completed'], true) ? $staff->user_id : null,
                'return_requested_at' => $status === 'return_requested' ? now() : null,
                'returned_at' => $status === 'completed' ? $start->copy()->addDays($days) : null,
                'returned_by' => $status === 'completed' ? $staff->user_id : null,
            ]);

            foreach ($chosen as $addon) {
                $booking->addons()->attach($addon->addon_id, ['daily_rate' => $addon->daily_rate, 'days' => $days]);
            }

            $booking->renterDocument()->create([
                'id_type' => 'National ID',
                'id_number' => sprintf('PSN-%04d-%07d', 1000 + $i, 4500000 + $i),
            ]);

            if ($paid) {
                Payment::create([
                    'booking_id' => $booking->booking_id,
                    'amount' => $booking->total,
                    'payment_method' => $method,
                    'brand' => match ($method) {
                        'cashless' => 'GCash',
                        'card' => 'Visa',
                        default => 'Cash',
                    },
                    'account_last4' => $method === 'cash' ? null : '4242',
                    'holder' => $method === 'card' ? $customer->name : null,
                    'payment_status' => 'paid',
                    'recorded_by' => $staff->user_id,
                ]);
            }

            if ($status === 'completed') {
                Rating::create([
                    'booking_id' => $booking->booking_id,
                    'user_id' => $customer->user_id,
                    'vehicle_id' => $vehicle->vehicle_id,
                    'stars' => 4 + ($i % 2),
                    'comment' => 'Clean car and smooth handover.',
                ]);
            }

            if ($status === 'pending') {
                $thread = MessageThread::create([
                    'kind' => 'booking',
                    'topic' => "Booking {$booking->ref}",
                    'booking_id' => $booking->booking_id,
                    'customer_id' => $customer->user_id,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                ]);

                $thread->messages()->create([
                    'from_role' => 'customer',
                    'from_user_id' => $customer->user_id,
                    'from_name' => $customer->name,
                    'body' => 'Hi, can I pick up the car 30 minutes earlier?',
                ]);

                $thread->messages()->create([
                    'from_role' => 'staff',
                    'from_user_id' => $staff->user_id,
                    'from_name' => $staff->name,
                    'body' => 'Yes, that works. See you then!',
                ]);
            }
        }

        MessageThread::create([
            'kind' => 'contact',
            'topic' => 'Long-term rental inquiry',
            'customer_name' => 'Maria Santos',
            'customer_email' => 'maria.santos@example.com',
            'customer_phone' => '09171234567',
        ])->messages()->create([
            'from_role' => 'customer',
            'from_name' => 'Maria Santos',
            'body' => 'Do you offer monthly rates for an SUV?',
        ]);
    }
}
