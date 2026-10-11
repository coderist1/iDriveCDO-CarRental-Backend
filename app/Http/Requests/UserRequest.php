<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Patterns;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('phone') || $this->input('phone') === null || $this->input('phone') === '') {
            return;
        }
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
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';
        $user = $this->route('user');

        return [
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('users', 'code')->ignore($user, 'user_id')],
            'email' => [$required, 'string', 'lowercase', 'email', Patterns::EMAIL, 'max:120', Rule::unique('users', 'email')->ignore($user, 'user_id')],
            'password' => [$required, Password::defaults()],
            'role' => ['sometimes', Rule::in(User::ROLES)],
            'first_name' => [$required, 'string', 'max:40'],
            'last_name' => [$required, 'string', 'max:40'],
            'phone' => [
                $required,
                'string',
                Patterns::ACCOUNT_PHONE,
                Rule::unique('users', 'phone')->ignore($user instanceof User ? $user->user_id : $user, 'user_id'),
            ],
            'address' => ['sometimes', 'nullable', 'string', 'max:120'],
            'department' => ['sometimes', 'nullable', 'string', 'max:60'],
            'license_no' => ['sometimes', 'nullable', 'string', Patterns::LICENSE_NO],
            'license_expiry' => ['sometimes', 'nullable', 'date'],
            'avatar' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(User::STATUSES)],
            'age_confirmed_at' => ['sometimes', 'nullable', 'date'],
            'terms_accepted_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
