<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRegistrationRequest extends FormRequest
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
        $registration = $this->route('vehicle_registration');

        return [
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('vehicle_registrations', 'code')->ignore($registration, 'registration_id')],
            'vehicle_id' => [
                $required,
                Rule::exists('vehicles', 'vehicle_id'),
                Rule::unique('vehicle_registrations', 'vehicle_id')->ignore($registration, 'registration_id'),
            ],
            'plate_number' => [$required, 'string', 'max:12'],
            'renewal_scheduled_day' => ['sometimes', 'nullable', 'integer', 'between:1,31'],
            'next_reg_renewal' => [$required, 'date'],
        ];
    }
}
