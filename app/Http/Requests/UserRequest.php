<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Patterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
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
        $user = $this->route('user');

        return [
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('users', 'code')->ignore($user, 'user_id')],
            'email' => [$required, 'string', 'lowercase', 'email', Patterns::EMAIL, 'max:120', Rule::unique('users', 'email')->ignore($user, 'user_id')],
            'password' => [$required, Password::defaults()],
            'role' => ['sometimes', Rule::in(User::ROLES)],
            'first_name' => [$required, 'string', 'max:40'],
            'last_name' => [$required, 'string', 'max:40'],
            'phone' => [$required, 'string', Patterns::PHONE],
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
