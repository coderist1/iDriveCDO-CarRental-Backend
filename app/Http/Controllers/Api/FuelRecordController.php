<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FuelRecordRequest;
use App\Models\FuelRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FuelRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $records = FuelRecord::with('vehicle')
            ->when($request->query('vehicle_id'), fn ($q, $id) => $q->where('vehicle_id', $id))
            ->latest('recorded_at')
            ->paginate($this->perPage($request));

        return response()->json($records);
    }

    public function store(FuelRecordRequest $request): JsonResponse
    {
        $record = FuelRecord::create($request->validated());

        return response()->json($record->refresh(), 201);
    }

    public function show(FuelRecord $fuelRecord): JsonResponse
    {
        return response()->json($fuelRecord->load(['vehicle', 'recorder']));
    }

    public function update(FuelRecordRequest $request, FuelRecord $fuelRecord): JsonResponse
    {
        $fuelRecord->update($request->validated());

        return response()->json($fuelRecord->refresh());
    }

    public function destroy(FuelRecord $fuelRecord): JsonResponse
    {
        $fuelRecord->delete();

        return response()->json(['message' => 'Fuel record deleted.']);
    }
}
