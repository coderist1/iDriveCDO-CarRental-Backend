<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesAccountPhone;
use App\Support\Patterns;
use Illuminate\Foundation\Http\FormRequest;

class PhoneAvailableRequest extends FormRequest
{
    use NormalizesAccountPhone;

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
            'phone' => ['required', 'string', Patterns::ACCOUNT_PHONE],
        ];
    }
}
