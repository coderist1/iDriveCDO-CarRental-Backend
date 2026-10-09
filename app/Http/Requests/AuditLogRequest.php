<?php

namespace App\Http\Requests;

use App\Models\AuditLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogRequest extends FormRequest
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
            'action' => ['required', 'string', 'max:40'],
            'user_id' => ['nullable', Rule::exists('users', 'user_id')],
            'actor_label' => ['nullable', Rule::in(AuditLog::ACTOR_LABELS)],
            'detail' => ['nullable', 'string', 'max:180'],
            'ip_address' => ['nullable', 'ip'],
        ];
    }
}
