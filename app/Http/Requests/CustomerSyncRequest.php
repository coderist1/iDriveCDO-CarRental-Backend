<?php

namespace App\Http\Requests;

use App\Support\Patterns;
use Illuminate\Foundation\Http\FormRequest;

class CustomerSyncRequest extends FormRequest
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
        return [
            'email' => ['required', 'string', 'email', Patterns::EMAIL, 'max:120'],
            'first_name' => ['required', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:40'],
            'phone' => ['required', 'string', Patterns::PHONE],
            'address' => ['nullable', 'string', 'max:120'],
            'license_no' => ['nullable', 'string', Patterns::LICENSE_NO],
            'license_expiry' => ['nullable', 'date'],
        ];
    }
}
