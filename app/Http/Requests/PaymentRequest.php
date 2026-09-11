<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'booking_id' => [$required, 'exists:bookings,booking_id'],
            'amount' => [$required, 'numeric', 'between:0,99999999.99'],
            'payment_method' => [$required, 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'payment_date' => [$required, 'date'],
            'payment_status' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
