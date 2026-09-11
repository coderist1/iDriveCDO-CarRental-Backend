<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffInfoRequest extends FormRequest
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
                Rule::unique('staff_info', 'user_id')->ignore($this->route('staff_info')),
            ],
            'staff_full_name' => [$required, 'string', 'max:255'],
            'address' => [$required, 'string', 'max:255'],
            'department' => [$required, 'string', 'max:255'],
        ];
    }
}
