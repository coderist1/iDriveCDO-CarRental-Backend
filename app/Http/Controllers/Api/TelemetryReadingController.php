<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelemetryReadingRequest;
use App\Models\TelemetryReading;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelemetryReadingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $readings = TelemetryReading::query()
            ->when($request->query('vehicle_id'), fn ($q, $id) => $q->where('vehicle_id', $id))
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($readings);
    }

    public function store(TelemetryReadingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['brand'] ??= Vehicle::withTrashed()->findOrFail($data['vehicle_id'])->brand;

        $reading = TelemetryReading::create($data);

        return response()->json($reading->refresh(), 201);
    }

    public function show(TelemetryReading $telemetryReading): JsonResponse
    {
        return response()->json($telemetryReading->load(['vehicle', 'predictions']));
    }

    public function destroy(TelemetryReading $telemetryReading): JsonResponse
    {
        $telemetryReading->delete();

        return response()->json(['message' => 'Telemetry reading deleted.']);
    }
}
