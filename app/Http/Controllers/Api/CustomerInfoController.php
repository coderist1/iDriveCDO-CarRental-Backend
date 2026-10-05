<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerInfoRequest;
use App\Http\Requests\CustomerSyncRequest;
use App\Models\CustomerInfo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * Finds or creates the customer's user by email and upserts their customer_info row.
     * The frontend still signs people in locally, so it never knows the user's password;
     * new users get a random one until login moves to the backend.
     */
    public function sync(CustomerSyncRequest $request): JsonResponse
    {
        $data = $request->validated();

        $info = DB::transaction(function () use ($data) {
            $user = User::firstOrCreate(
                ['email' => Str::lower($data['email'])],
                ['name' => $data['customer_full_name'], 'password' => Str::random(40)]
            );
            $user->update(['name' => $data['customer_full_name']]);

            $existing = CustomerInfo::find($user->id);

            return CustomerInfo::updateOrCreate(['user_id' => $user->id], [
                'customer_full_name' => $data['customer_full_name'],
                'address' => $data['address'] ?? $existing?->address ?? '',
                'driver_license' => $data['driver_license'] ?? $existing?->driver_license ?? '',
            ]);
        });

        return response()->json($info->load('user'), $info->wasRecentlyCreated ? 201 : 200);
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
