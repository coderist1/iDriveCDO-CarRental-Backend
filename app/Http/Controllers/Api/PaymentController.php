<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with('booking')
            ->when($request->query('booking_id'), fn ($q, $id) => $q->where('booking_id', $id))
            ->when($request->query('payment_status'), fn ($q, $status) => $q->where('payment_status', $status))
            ->when($request->query('payment_method'), fn ($q, $method) => $q->where('payment_method', $method))
            ->latest('payment_date')
            ->paginate($this->perPage($request));

        return response()->json($payments);
    }

    public function store(PaymentRequest $request): JsonResponse
    {
        $payment = DB::transaction(function () use ($request) {
            $payment = Payment::create($request->validated());
            $this->markBookingPaid($payment->refresh());

            return $payment;
        });

        return response()->json($payment->load('booking'), 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load(['booking', 'recorder']));
    }

    public function update(PaymentRequest $request, Payment $payment): JsonResponse
    {
        DB::transaction(function () use ($request, $payment) {
            $payment->update($request->validated());
            $this->markBookingPaid($payment->refresh());
        });

        return response()->json($payment->load('booking'));
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted.']);
    }

    /**
     * A paid payment marks its booking as paid, which bookings need before they can be confirmed.
     */
    private function markBookingPaid(Payment $payment): void
    {
        if ($payment->payment_status !== 'paid') {
            return;
        }

        $payment->booking->update([
            'payment_status' => 'paid',
            'payment_method' => $payment->payment_method,
        ]);
    }
}
