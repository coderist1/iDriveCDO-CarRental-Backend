<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleMaintenanceRequest extends FormRequest
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
            'maintenance_type' => [$required, 'string', 'max:255'],
            'scheduled_date' => [$required, 'date'],
            'performed_at' => ['nullable', 'date'],
            'finish' => ['nullable', 'string', 'max:255'],
        ];
    }
}
