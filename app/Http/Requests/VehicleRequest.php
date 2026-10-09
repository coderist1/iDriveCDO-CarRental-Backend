<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use App\Support\Patterns;
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
        $vehicle = $this->route('vehicle');

        return [
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('vehicles', 'code')->ignore($vehicle, 'vehicle_id')],
            'name' => [$required, 'string', 'max:60'],
            'brand' => [$required, 'string', 'max:30'],
            'model' => [$required, 'string', 'max:30'],
            'year_model' => [$required, 'integer', 'between:1990,2100'],
            'year_purchased' => ['sometimes', 'nullable', 'integer', 'between:1990,2100'],
            'type' => [$required, Rule::in(Vehicle::TYPES)],
            'transmission' => [$required, Rule::in(Vehicle::TRANSMISSIONS)],
            'fuel' => [$required, Rule::in(Vehicle::FUELS)],
            'capacity' => ['sometimes', 'integer', 'between:1,30'],
            'luggage' => ['sometimes', 'integer', 'between:0,32767'],
            'mileage' => ['sometimes', 'integer', 'min:0'],
            'daily_rate' => [$required, 'numeric', 'min:500', 'max:99999999.99'],
            'plate_number' => [$required, 'string', 'max:12', Patterns::PLATE_NUMBER, Rule::unique('vehicles', 'plate_number')->ignore($vehicle, 'vehicle_id')],
            'image' => ['sometimes', 'nullable', 'string', 'max:300'],
            'description' => ['sometimes', 'nullable', 'string', 'max:400'],
            'status' => ['sometimes', Rule::in(Vehicle::STATUSES)],
            'features' => ['sometimes', 'array'],
            'features.*' => ['string', 'max:40', 'distinct'],
        ];
    }
}
