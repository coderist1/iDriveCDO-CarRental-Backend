<?php

namespace App\Http\Requests;

use App\Models\MaintenancePrediction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenancePredictionRequest extends FormRequest
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
        return [
            'vehicle_id' => ['required', Rule::exists('vehicles', 'vehicle_id')],
            'telemetry_id' => ['sometimes', 'nullable', Rule::exists('telemetry_readings', 'telemetry_id')],
            'target' => ['sometimes', Rule::in(MaintenancePrediction::TARGETS)],
            'prediction' => ['required', 'integer', Rule::in([0, 1])],
            'needs_maintenance' => ['required', 'boolean'],
            'probability' => ['sometimes', 'nullable', 'numeric', 'between:0,1'],
            'model_name' => ['sometimes', 'string', 'max:60'],
            'predicted_by' => ['sometimes', 'nullable', Rule::exists('users', 'user_id')],
            'predicted_at' => ['sometimes', 'date'],
        ];
    }
}
