<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    private const DETAIL = ['user', 'vehicle.features', 'driver', 'pickupLocation', 'dropoffLocation', 'addons', 'renterDocument', 'payments', 'rating'];

    /**
     * Timestamp column set when a booking enters each status.
     */
    private const STATUS_TIMESTAMPS = [
        'ongoing' => 'started_at',
        'return_requested' => 'return_requested_at',
        'completed' => 'returned_at',
    ];

    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['user', 'vehicle', 'driver', 'pickupLocation', 'dropoffLocation'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('payment_status'), fn ($q, $status) => $q->where('payment_status', $status))
            ->when($request->query('user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->query('vehicle_id'), fn ($q, $id) => $q->where('vehicle_id', $id))
            ->when($request->query('driver_id'), fn ($q, $id) => $q->where('driver_id', $id))
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($bookings);
    }

    public function store(BookingRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['addon_ids', 'renter_document']);

        $booking = DB::transaction(function () use ($request, $data) {
            $days = $this->days($data['start_date'], $data['end_date']);
            $addons = Addon::whereIn('addon_id', $request->validated('addon_ids', []))->get();

            $booking = Booking::create([
                ...$data,
                'subtotal' => $data['subtotal'] ?? $this->subtotal(Vehicle::findOrFail($data['vehicle_id']), $days),
                'extras' => $addons->sum(fn (Addon $addon) => $addon->daily_rate * $days),
            ]);

            foreach ($addons as $addon) {
                $booking->addons()->attach($addon->addon_id, ['daily_rate' => $addon->daily_rate, 'days' => $days]);
            }

            if ($document = $request->validated('renter_document')) {
                $booking->renterDocument()->create($document);
            }

            return $booking;
        });

        return response()->json($booking->refresh()->load(self::DETAIL), 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking->load([...self::DETAIL, 'messageThread']));
    }

    public function update(BookingRequest $request, Booking $booking): JsonResponse
    {
        $data = $request->safe()->except(['addon_ids', 'renter_document']);

        DB::transaction(function () use ($request, $booking, $data) {
            if (($data['drive_mode'] ?? null) === 'self') {
                $data['driver_id'] = null;
            }

            if (isset($data['status']) && $data['status'] !== $booking->status
                && ($column = self::STATUS_TIMESTAMPS[$data['status']] ?? null) && ! $booking->{$column}) {
                $data[$column] = now();
            }

            $booking->fill($data);
            $days = $this->days($booking->start_date, $booking->end_date);
            $repriced = $booking->isDirty(['start_date', 'end_date', 'vehicle_id']);

            if ($repriced && ! array_key_exists('subtotal', $data)) {
                $booking->subtotal = $this->subtotal(Vehicle::withTrashed()->findOrFail($booking->vehicle_id), $days);
            }

            if ($request->has('addon_ids')) {
                $addons = Addon::whereIn('addon_id', $request->validated('addon_ids', []))->get();
                $booking->addons()->sync($addons->mapWithKeys(fn (Addon $addon) => [
                    $addon->addon_id => ['daily_rate' => $addon->daily_rate, 'days' => $days],
                ])->all());
            } elseif ($repriced) {
                $booking->addons()->newPivotQuery()->update(['days' => $days]);
            }

            $booking->extras = $booking->addons()->get()->sum(fn (Addon $addon) => $addon->pivot->daily_rate * $days);
            $booking->save();

            if ($request->has('renter_document')) {
                $document = $request->validated('renter_document');
                $document
                    ? $booking->renterDocument()->updateOrCreate([], $document)
                    : $booking->renterDocument()->delete();
            }
        });

        return response()->json($booking->refresh()->load(self::DETAIL));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();

        return response()->json(['message' => 'Booking deleted.']);
    }

    private function days(mixed $start, mixed $end): int
    {
        return (int) Carbon::parse($start)->diffInDays(Carbon::parse($end));
    }

    private function subtotal(Vehicle $vehicle, int $days): string
    {
        return number_format((float) $vehicle->daily_rate * $days, 2, '.', '');
    }
}
