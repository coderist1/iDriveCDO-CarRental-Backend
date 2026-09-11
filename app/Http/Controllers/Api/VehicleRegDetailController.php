<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRegDetailRequest;
use App\Models\VehicleRegDetail;
use Illuminate\Http\JsonResponse;

class VehicleRegDetailController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(VehicleRegDetail::with('vehicle')->latest()->paginate(15));
    }

    public function store(VehicleRegDetailRequest $request): JsonResponse
    {
        return response()->json(VehicleRegDetail::create($request->validated()), 201);
    }

    public function show(VehicleRegDetail $vehicleRegDetail): JsonResponse
    {
        return response()->json($vehicleRegDetail->load('vehicle'));
    }

    public function update(VehicleRegDetailRequest $request, VehicleRegDetail $vehicleRegDetail): JsonResponse
    {
        $vehicleRegDetail->update($request->validated());

        return response()->json($vehicleRegDetail);
    }

    public function destroy(VehicleRegDetail $vehicleRegDetail): JsonResponse
    {
        $vehicleRegDetail->delete();

        return response()->json(['message' => 'Registration detail deleted.']);
    }
}
