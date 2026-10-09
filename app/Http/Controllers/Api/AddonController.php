<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddonRequest;
use App\Models\Addon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addons = Addon::query()
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        return response()->json($addons);
    }

    public function store(AddonRequest $request): JsonResponse
    {
        $addon = Addon::create($request->validated());

        return response()->json($addon->refresh(), 201);
    }

    public function show(Addon $addon): JsonResponse
    {
        return response()->json($addon);
    }

    public function update(AddonRequest $request, Addon $addon): JsonResponse
    {
        $addon->update($request->validated());

        return response()->json($addon->refresh());
    }

    public function destroy(Addon $addon): JsonResponse
    {
        $addon->delete();

        return response()->json(['message' => 'Add-on deleted.']);
    }
}
