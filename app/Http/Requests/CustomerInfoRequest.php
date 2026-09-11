<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerInfoRequest extends FormRequest
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
            'user_id' => [
                $required,
                'exists:users,id',
                Rule::unique('customer_info', 'user_id')->ignore($this->route('customer_info')),
            ],
            'customer_full_name' => [$required, 'string', 'max:255'],
            'address' => [$required, 'string', 'max:255'],
            'driver_license' => [$required, 'string', 'max:255'],
        ];
    }
}
