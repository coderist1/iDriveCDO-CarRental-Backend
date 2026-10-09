<?php

namespace App\Http\Requests;

use App\Models\FuelRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('fuel_records', 'code')->ignore($this->route('fuel_record'), 'fuel_record_id')],
            'vehicle_id' => [$required, Rule::exists('vehicles', 'vehicle_id')],
            'fuel_type' => [$required, Rule::in(FuelRecord::FUEL_TYPES)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:120'],
            'recorded_by' => ['sometimes', 'nullable', Rule::exists('users', 'user_id')],
            'recorded_at' => ['sometimes', 'date'],
        ];
    }
}
