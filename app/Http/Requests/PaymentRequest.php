<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $payment = $this->route('payment');

        return [
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('payments', 'code')->ignore($payment, 'payment_id')],
            'booking_id' => [
                $required,
                Rule::exists('bookings', 'booking_id')->whereNotIn('status', ['cancelled', 'rejected']),
            ],
            'amount' => [$required, 'numeric', 'gt:0', 'max:99999999.99'],
            'payment_method' => [$required, Rule::in(Payment::METHODS)],
            'brand' => [
                'nullable', 'string', 'max:20',
                'required_unless:payment_method,cash',
                Rule::when($this->input('payment_method') === 'cash', [Rule::in(['Cash'])]),
                Rule::when($this->input('payment_method') === 'cashless', [Rule::in(Payment::CASHLESS_BRANDS)]),
            ],
            'account_last4' => ['sometimes', 'nullable', 'digits:4'],
            'holder' => ['sometimes', 'nullable', 'string', 'max:80'],
            'reference_number' => ['sometimes', 'string', 'max:40', Rule::unique('payments', 'reference_number')->ignore($payment, 'payment_id')],
            'payment_status' => ['sometimes', Rule::in(Payment::STATUSES)],
            'payment_date' => ['sometimes', 'date'],
            'recorded_by' => ['sometimes', 'nullable', Rule::exists('users', 'user_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'booking_id.exists' => 'The selected booking does not exist or cannot be paid.',
        ];
    }
}
