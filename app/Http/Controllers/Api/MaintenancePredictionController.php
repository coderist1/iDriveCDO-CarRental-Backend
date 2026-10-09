<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenancePredictionRequest;
use App\Models\MaintenancePrediction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenancePredictionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $predictions = MaintenancePrediction::query()
            ->when($request->query('vehicle_id'), fn ($q, $id) => $q->where('vehicle_id', $id))
            ->when($request->query('target'), fn ($q, $target) => $q->where('target', $target))
            ->when($request->has('needs_maintenance'), fn ($q) => $q->where('needs_maintenance', $request->boolean('needs_maintenance')))
            ->latest('predicted_at')
            ->paginate($this->perPage($request));

        return response()->json($predictions);
    }

    public function store(MaintenancePredictionRequest $request): JsonResponse
    {
        $prediction = MaintenancePrediction::create($request->validated());

        return response()->json($prediction->refresh(), 201);
    }

    public function show(MaintenancePrediction $maintenancePrediction): JsonResponse
    {
        return response()->json($maintenancePrediction->load(['vehicle', 'telemetryReading', 'maintenances']));
    }

    public function destroy(MaintenancePrediction $maintenancePrediction): JsonResponse
    {
        $maintenancePrediction->delete();

        return response()->json(['message' => 'Prediction deleted.']);
    }
}
