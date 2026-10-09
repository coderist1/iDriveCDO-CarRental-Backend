<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest;
use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $maintenances = Maintenance::with('vehicle')
            ->when($request->query('vehicle_id'), fn ($q, $id) => $q->where('vehicle_id', $id))
            ->when($request->has('finished'), fn ($q) => $q->where('finished', $request->boolean('finished')))
            ->orderByDesc('scheduled_date')
            ->paginate($this->perPage($request));

        return response()->json($maintenances);
    }

    public function store(MaintenanceRequest $request): JsonResponse
    {
        $maintenance = Maintenance::create($request->validated());

        return response()->json($maintenance->refresh(), 201);
    }

    public function show(Maintenance $maintenance): JsonResponse
    {
        return response()->json($maintenance->load(['vehicle', 'prediction', 'creator']));
    }

    public function update(MaintenanceRequest $request, Maintenance $maintenance): JsonResponse
    {
        $maintenance->update($request->validated());

        return response()->json($maintenance->refresh());
    }

    public function destroy(Maintenance $maintenance): JsonResponse
    {
        $maintenance->delete();

        return response()->json(['message' => 'Maintenance deleted.']);
    }
}
