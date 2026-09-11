<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        $bookings = Booking::with(['user', 'vehicle', 'driverDetail'])
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }

    public function store(BookingRequest $request): JsonResponse
    {
        $booking = Booking::create($request->validated());

        return response()->json($booking->load(['user', 'vehicle', 'driverDetail']), 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking->load(['user', 'vehicle', 'driverDetail', 'payments']));
    }

    public function update(BookingRequest $request, Booking $booking): JsonResponse
    {
        $booking->update($request->validated());

        return response()->json($booking->load(['user', 'vehicle', 'driverDetail']));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();

        return response()->json(['message' => 'Booking deleted.']);
    }
}
