<?php

namespace App\Http\Requests;

use App\Models\Driver;
use App\Support\Patterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverRequest extends FormRequest
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
        $driver = $this->route('driver');

        return [
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('drivers', 'code')->ignore($driver, 'driver_id')],
            'user_id' => [
                'sometimes', 'nullable',
                Rule::exists('users', 'user_id')->where('role', 'driver'),
                Rule::unique('drivers', 'user_id')->ignore($driver, 'driver_id'),
            ],
            'full_name' => [$required, 'string', 'max:80'],
            'driver_license' => [$required, 'string', 'max:30', Rule::unique('drivers', 'driver_license')->ignore($driver, 'driver_id')],
            'type_driver_license' => ['sometimes', 'string', 'max:30'],
            'license_expiry' => [$required, 'date'],
            'phone' => ['sometimes', 'nullable', 'string', Patterns::PHONE],
            'status' => ['sometimes', Rule::in(Driver::STATUSES)],
            'duty_status' => ['sometimes', Rule::in(Driver::DUTY_STATUSES)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'The linked login must be a user with the driver role.',
        ];
    }
}
