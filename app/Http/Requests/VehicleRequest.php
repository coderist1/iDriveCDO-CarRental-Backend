<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
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
            'plate_number' => [
                $required,
                'string',
                'max:20',
                Rule::unique('vehicles', 'plate_number')->ignore($this->route('vehicle')),
            ],
            'mileage' => ['sometimes', 'integer', 'min:0'],
            'brand' => [$required, 'string', 'max:100'],
            'model' => [$required, 'string', 'max:100'],
            'type' => [$required, 'string', 'max:50'],
            'capacity' => [$required, 'integer', 'min:1'],
            'daily_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'year_model' => [$required, 'integer', 'digits:4'],
            'year_purchased' => [$required, 'integer', 'digits:4'],
        ];
    }
}
