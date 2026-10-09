<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\FuelRecord;
use App\Models\Maintenance;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRegistration;
use Illuminate\Http\JsonResponse;

/**
 * Every collection the frontend mirrors, in one round trip. Rows have the same shape as the
 * matching index endpoints, without pagination (no extra COUNT queries, one DB connection).
 */
class SyncController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'vehicles' => Vehicle::with('features')->latest()->get(),
            'users' => User::query()->latest()->get(),
            'drivers' => Driver::with('user')->latest()->get(),
            'bookings' => Booking::with(['user', 'vehicle', 'driver', 'pickupLocation', 'dropoffLocation'])->latest()->get(),
            'payments' => Payment::with('booking')->latest('payment_date')->get(),
            'fuel_records' => FuelRecord::with('vehicle')->latest('recorded_at')->get(),
            'maintenances' => Maintenance::with('vehicle')->orderByDesc('scheduled_date')->get(),
            'vehicle_registrations' => VehicleRegistration::with('vehicle')->orderBy('next_reg_renewal')->get(),
        ]);
    }
}
