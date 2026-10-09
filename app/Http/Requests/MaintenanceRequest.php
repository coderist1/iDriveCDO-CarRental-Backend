<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceRequest extends FormRequest
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
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('maintenances', 'code')->ignore($this->route('maintenance'), 'maintenance_id')],
            'vehicle_id' => [$required, Rule::exists('vehicles', 'vehicle_id')],
            'maintenance_type' => [$required, 'string', 'max:60'],
            'scheduled_date' => [$required, 'date'],
            'performed_at' => ['sometimes', 'nullable', 'date'],
            'finished' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:200'],
            'prediction_id' => ['sometimes', 'nullable', Rule::exists('maintenance_predictions', 'prediction_id')],
            'created_by' => ['sometimes', 'nullable', Rule::exists('users', 'user_id')],
        ];
    }
}
