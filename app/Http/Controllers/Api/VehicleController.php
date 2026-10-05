<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 200);

        return response()->json(Vehicle::latest()->paginate($perPage));
    }

    public function store(VehicleRequest $request): JsonResponse
    {
        return response()->json(Vehicle::create($request->validated()), 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle->load(['regDetails', 'maintenances', 'fuelRecords']));
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $vehicle->update($request->validated());

        return response()->json($vehicle);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted.']);
    }
}
