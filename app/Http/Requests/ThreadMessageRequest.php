<?php

namespace App\Http\Requests;

use App\Models\ThreadMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThreadMessageRequest extends FormRequest
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
            'from_role' => ['required', Rule::in(ThreadMessage::ROLES)],
            'from_user_id' => ['nullable', Rule::exists('users', 'user_id')],
            'from_name' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:500'],
        ];
    }
}
