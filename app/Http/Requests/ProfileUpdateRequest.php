<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Patterns;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $canonical = Phone::canonical($this->input('phone'));
        if ($canonical !== null) {
            $this->merge(['phone' => $canonical]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Use a Philippine mobile number (09XXXXXXXXX).',
            'phone.unique' => 'This mobile number is already registered.',
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:40'],
            'phone' => [
                'required',
                'string',
                Patterns::ACCOUNT_PHONE,
                Rule::unique('users', 'phone')->ignore($this->user()->getKey(), 'user_id'),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                Patterns::EMAIL,
                'max:120',
                Rule::unique(User::class)->ignore($this->user()->getKey(), 'user_id'),
            ],
        ];
    }
}
