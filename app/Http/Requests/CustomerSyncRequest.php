<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Patterns;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', Patterns::EMAIL, 'max:120'],
            'first_name' => ['required', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:40'],
            'phone' => [
                'required',
                'string',
                Patterns::ACCOUNT_PHONE,
                Rule::unique('users', 'phone')->ignore(
                    User::query()->whereRaw('lower(email) = ?', [Str::lower((string) $this->input('email'))])->value('user_id'),
                    'user_id'
                ),
            ],
            'address' => ['nullable', 'string', 'max:120'],
            'license_no' => ['nullable', 'string', Patterns::LICENSE_NO],
            'license_expiry' => ['nullable', 'date'],
            'birthdate' => ['nullable', 'date'],
            'privacy_accepted_at' => ['nullable', 'date'],
        ];
    }
}
