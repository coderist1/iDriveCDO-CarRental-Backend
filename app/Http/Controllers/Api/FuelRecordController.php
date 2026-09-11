<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FuelRecordRequest;
use App\Models\FuelRecord;
use Illuminate\Http\JsonResponse;

class FuelRecordController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(FuelRecord::with('vehicle')->latest()->paginate(15));
    }

    public function store(FuelRecordRequest $request): JsonResponse
    {
        return response()->json(FuelRecord::create($request->validated()), 201);
    }

    public function show(FuelRecord $fuelRecord): JsonResponse
    {
        return response()->json($fuelRecord->load('vehicle'));
    }

    public function update(FuelRecordRequest $request, FuelRecord $fuelRecord): JsonResponse
    {
        $fuelRecord->update($request->validated());

        return response()->json($fuelRecord);
    }

    public function destroy(FuelRecord $fuelRecord): JsonResponse
    {
        $fuelRecord->delete();

        return response()->json(['message' => 'Fuel record deleted.']);
    }
}
