<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRegistrationRequest;
use App\Models\VehicleRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleRegistrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $registrations = VehicleRegistration::with('vehicle')
            ->when($request->query('vehicle_id'), fn ($q, $id) => $q->where('vehicle_id', $id))
            ->orderBy('next_reg_renewal')
            ->paginate($this->perPage($request));

        return response()->json($registrations);
    }

    public function store(VehicleRegistrationRequest $request): JsonResponse
    {
        $registration = VehicleRegistration::create($request->validated());

        return response()->json($registration->refresh(), 201);
    }

    public function show(VehicleRegistration $vehicleRegistration): JsonResponse
    {
        return response()->json($vehicleRegistration->load('vehicle'));
    }

    public function update(VehicleRegistrationRequest $request, VehicleRegistration $vehicleRegistration): JsonResponse
    {
        $vehicleRegistration->update($request->validated());

        return response()->json($vehicleRegistration->refresh());
    }

    public function destroy(VehicleRegistration $vehicleRegistration): JsonResponse
    {
        $vehicleRegistration->delete();

        return response()->json(['message' => 'Registration deleted.']);
    }
}
