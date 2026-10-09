<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Patterns;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
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
            'phone' => ['required', 'string', Patterns::PHONE],
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
