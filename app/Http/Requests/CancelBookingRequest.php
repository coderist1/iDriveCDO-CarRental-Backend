<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CancelBookingRequest extends FormRequest
{
    public const REASONS = [
        'Change of plans',
        'Booked the wrong dates',
        'Found another car',
        'Other',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'in:'.implode(',', self::REASONS)],
            'reason_note' => ['required_if:reason,Other', 'nullable', 'string', 'max:240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Sign in to cancel this booking.',
            'reason.required' => 'Tell us why you are cancelling.',
            'reason.in' => 'Choose a reason from the list.',
            'reason_note.required_if' => 'Type your reason when you choose Other.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->has('user_id') && ! $this->filled('user_id')) {
            throw new HttpResponseException(response()->json([
                'message' => 'Sign in to cancel this booking.',
            ], 401));
        }

        parent::failedValidation($validator);
    }
};
