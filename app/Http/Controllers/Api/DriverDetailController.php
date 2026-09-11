<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverDetailRequest;
use App\Models\DriverDetail;
use Illuminate\Http\JsonResponse;

class DriverDetailController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(DriverDetail::latest()->paginate(15));
    }

    public function store(DriverDetailRequest $request): JsonResponse
    {
        return response()->json(DriverDetail::create($request->validated()), 201);
    }

    public function show(DriverDetail $driverDetail): JsonResponse
    {
        return response()->json($driverDetail->load('bookings'));
    }

    public function update(DriverDetailRequest $request, DriverDetail $driverDetail): JsonResponse
    {
        $driverDetail->update($request->validated());

        return response()->json($driverDetail);
    }

    public function destroy(DriverDetail $driverDetail): JsonResponse
    {
        $driverDetail->delete();

        return response()->json(['message' => 'Driver deleted.']);
    }
}
