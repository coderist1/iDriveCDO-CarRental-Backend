<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FuelRecordRequest extends FormRequest
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
            'vehicle_id' => [$required, 'exists:vehicles,vehicle_id'],
            'fuel_type' => [$required, 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'between:0,999999.99'],
            'fuel_cost' => ['nullable', 'numeric', 'between:0,99999999.99'],
            'fuel_date' => [$required, 'date'],
            'mileage' => ['nullable', 'numeric', 'between:0,99999999.99'],
        ];
    }
}
