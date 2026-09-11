<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffInfoRequest;
use App\Models\StaffInfo;
use Illuminate\Http\JsonResponse;

class StaffInfoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(StaffInfo::with('user')->latest()->paginate(15));
    }

    public function store(StaffInfoRequest $request): JsonResponse
    {
        return response()->json(StaffInfo::create($request->validated()), 201);
    }

    public function show(StaffInfo $staffInfo): JsonResponse
    {
        return response()->json($staffInfo->load('user'));
    }

    public function update(StaffInfoRequest $request, StaffInfo $staffInfo): JsonResponse
    {
        $staffInfo->update($request->validated());

        return response()->json($staffInfo);
    }

    public function destroy(StaffInfo $staffInfo): JsonResponse
    {
        $staffInfo->delete();

        return response()->json(['message' => 'Staff info deleted.']);
    }
}
