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
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:40',
                Rule::unique('vehicles', 'code')->ignore($this->route('vehicle')),
            ],
            'name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'transmission' => ['sometimes', 'nullable', 'string', 'max:20'],
            'fuel' => ['sometimes', 'nullable', 'string', 'max:20'],
            'luggage' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'status' => ['sometimes', Rule::in(['available', 'maintenance'])],
            'image' => ['sometimes', 'nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'features' => ['sometimes', 'nullable', 'array'],
            'features.*' => ['string', 'max:40'],
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
