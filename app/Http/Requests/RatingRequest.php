<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RatingRequest extends FormRequest
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
        $creating = $this->isMethod('POST');
        $required = $creating ? 'required' : 'sometimes';

        return [
            'booking_id' => $creating
                ? ['required', Rule::exists('bookings', 'booking_id'), Rule::unique('ratings', 'booking_id')]
                : ['prohibited'],
            'stars' => [$required, 'integer', 'between:1,5'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:280'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->isMethod('POST') || $validator->errors()->has('booking_id')) {
                    return;
                }

                if (Booking::find($this->input('booking_id'))?->status !== 'completed') {
                    $validator->errors()->add('booking_id', 'Rate the car after the trip is completed.');
                }
            },
        ];
    }
}
