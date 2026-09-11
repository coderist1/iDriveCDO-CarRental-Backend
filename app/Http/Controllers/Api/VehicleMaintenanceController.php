<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleMaintenanceRequest;
use App\Models\VehicleMaintenance;
use Illuminate\Http\JsonResponse;

class VehicleMaintenanceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(VehicleMaintenance::with('vehicle')->latest()->paginate(15));
    }

    public function store(VehicleMaintenanceRequest $request): JsonResponse
    {
        return response()->json(VehicleMaintenance::create($request->validated()), 201);
    }

    public function show(VehicleMaintenance $vehicleMaintenance): JsonResponse
    {
        return response()->json($vehicleMaintenance->load('vehicle'));
    }

    public function update(VehicleMaintenanceRequest $request, VehicleMaintenance $vehicleMaintenance): JsonResponse
    {
        $vehicleMaintenance->update($request->validated());

        return response()->json($vehicleMaintenance);
    }

    public function destroy(VehicleMaintenance $vehicleMaintenance): JsonResponse
    {
        $vehicleMaintenance->delete();

        return response()->json(['message' => 'Maintenance record deleted.']);
    }
}
