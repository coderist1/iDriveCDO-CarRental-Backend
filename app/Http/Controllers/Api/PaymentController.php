<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Payment::with('booking')->latest()->paginate(15));
    }

    public function store(PaymentRequest $request): JsonResponse
    {
        return response()->json(Payment::create($request->validated()), 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load('booking'));
    }

    public function update(PaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment->update($request->validated());

        return response()->json($payment);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted.']);
    }
}
