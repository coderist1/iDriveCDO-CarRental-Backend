<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vehicles = Vehicle::with('features')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->query('brand'), fn ($q, $brand) => $q->where('brand', $brand))
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($vehicles);
    }

    public function store(VehicleRequest $request): JsonResponse
    {
        $vehicle = DB::transaction(function () use ($request) {
            $vehicle = Vehicle::create($request->safe()->except('features'));
            $vehicle->syncFeatures($request->validated('features', []));

            return $vehicle;
        });

        return response()->json($vehicle->refresh()->load('features'), 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle->load(['features', 'registration', 'maintenances', 'fuelRecords']));
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        DB::transaction(function () use ($request, $vehicle) {
            $vehicle->update($request->safe()->except('features'));

            if ($request->has('features')) {
                $vehicle->syncFeatures($request->validated('features', []));
            }
        });

        return response()->json($vehicle->refresh()->load('features'));
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted.']);
    }
}
