<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerInfoRequest;
use App\Models\CustomerInfo;
use Illuminate\Http\JsonResponse;

class CustomerInfoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CustomerInfo::with('user')->latest()->paginate(15));
    }

    public function store(CustomerInfoRequest $request): JsonResponse
    {
        return response()->json(CustomerInfo::create($request->validated()), 201);
    }

    public function show(CustomerInfo $customerInfo): JsonResponse
    {
        return response()->json($customerInfo->load('user'));
    }

    public function update(CustomerInfoRequest $request, CustomerInfo $customerInfo): JsonResponse
    {
        $customerInfo->update($request->validated());

        return response()->json($customerInfo);
    }

    public function destroy(CustomerInfo $customerInfo): JsonResponse
    {
        $customerInfo->delete();

        return response()->json(['message' => 'Customer info deleted.']);
    }
}
