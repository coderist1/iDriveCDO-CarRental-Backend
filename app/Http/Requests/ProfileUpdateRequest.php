<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesAccountPhone;
use App\Models\User;
use App\Support\Patterns;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    use NormalizesAccountPhone;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:40'],
            'phone' => ['required', 'string', Patterns::ACCOUNT_PHONE],
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
