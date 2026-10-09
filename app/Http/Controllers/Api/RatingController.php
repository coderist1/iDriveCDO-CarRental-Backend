<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RatingRequest;
use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ratings = Rating::with(['user', 'vehicle'])
            ->when($request->query('vehicle_id'), fn ($q, $id) => $q->where('vehicle_id', $id))
            ->when($request->query('user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($ratings);
    }

    /**
     * The renter and vehicle are taken from the booking, as fn_ratings_before_insert requires.
     */
    public function store(RatingRequest $request): JsonResponse
    {
        $booking = Booking::findOrFail($request->validated('booking_id'));

        $rating = Rating::create([
            ...$request->validated(),
            'user_id' => $booking->user_id,
            'vehicle_id' => $booking->vehicle_id,
        ]);

        return response()->json($rating->refresh(), 201);
    }

    public function show(Rating $rating): JsonResponse
    {
        return response()->json($rating->load(['user', 'vehicle', 'booking']));
    }

    public function update(RatingRequest $request, Rating $rating): JsonResponse
    {
        $rating->update($request->validated());

        return response()->json($rating->refresh());
    }

    public function destroy(Rating $rating): JsonResponse
    {
        $rating->delete();

        return response()->json(['message' => 'Rating deleted.']);
    }
}
