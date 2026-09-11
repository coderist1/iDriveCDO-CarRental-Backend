<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverDetailRequest extends FormRequest
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
            'fullName' => [$required, 'string', 'max:255'],
            'Driver_License' => [$required, 'string', 'max:255'],
            'type_DriverLicense' => [$required, 'string', 'max:255'],
            'Driver_License_Expiry' => [$required, 'date'],
            'status' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
