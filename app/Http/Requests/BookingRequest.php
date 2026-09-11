<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'driver_details_id' => ['nullable', 'exists:driver_details,driver_details_id'],
            'vehicle_id' => [$required, 'exists:vehicles,vehicle_id'],
            'user_id' => [$required, 'exists:users,id'],

            'pickup_time' => [$required, 'date_format:H:i,H:i:s'],
            'pickup_date' => [$required, 'date'],
            'return_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'return_date' => ['nullable', 'date', 'after_or_equal:pickup_date'],

            'payment_method' => [$required, 'string', 'max:255'],
            'number_of_passenger' => [$required, 'integer', 'min:1'],
            'driver_option' => [$required, 'string', 'max:255'],

            'fuel_before_rent' => ['nullable', 'numeric', 'between:0,999.99'],
            'fuel_upon_return' => ['nullable', 'numeric', 'between:0,999.99'],

            'date_reserve' => [$required, 'date'],
            'booking_status' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
