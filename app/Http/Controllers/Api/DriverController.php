<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRequest;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $drivers = Driver::with('user')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('duty_status'), fn ($q, $duty) => $q->where('duty_status', $duty))
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($drivers);
    }

    public function store(DriverRequest $request): JsonResponse
    {
        $driver = Driver::create($request->validated());

        return response()->json($driver->refresh(), 201);
    }

    public function show(Driver $driver): JsonResponse
    {
        return response()->json($driver->load('user'));
    }

    public function update(DriverRequest $request, Driver $driver): JsonResponse
    {
        $driver->update($request->validated());

        return response()->json($driver->refresh());
    }

    public function destroy(Driver $driver): JsonResponse
    {
        $driver->delete();

        return response()->json(['message' => 'Driver deleted.']);
    }
}
