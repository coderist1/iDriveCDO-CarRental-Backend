<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRegDetailRequest extends FormRequest
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
            'plate_number' => [$required, 'string', 'max:20'],
            'renewal_scheduled_day' => [$required, 'date'],
            'next_reg_renewal' => [$required, 'date', 'after_or_equal:renewal_scheduled_day'],
        ];
    }
}
