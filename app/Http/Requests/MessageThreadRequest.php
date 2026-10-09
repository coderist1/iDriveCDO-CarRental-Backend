<?php

namespace App\Http\Requests;

use App\Models\MessageThread;
use App\Support\Patterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageThreadRequest extends FormRequest
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
        $thread = $this->route('message_thread');

        return [
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('message_threads', 'code')->ignore($thread, 'thread_id')],
            'kind' => [$required, Rule::in(MessageThread::KINDS)],
            'topic' => [$required, 'string', 'max:80'],
            'booking_id' => ['sometimes', 'nullable', Rule::exists('bookings', 'booking_id'), Rule::unique('message_threads', 'booking_id')->ignore($thread, 'thread_id')],
            'customer_id' => ['sometimes', 'nullable', Rule::exists('users', 'user_id')],
            'customer_name' => [$required, 'string', 'max:80'],
            'customer_email' => ['sometimes', 'nullable', 'string', 'email', 'max:120'],
            'customer_phone' => ['sometimes', 'nullable', 'string', Patterns::PHONE],
            'status' => ['sometimes', Rule::in(MessageThread::STATUSES)],
            'unread_staff' => ['sometimes', 'boolean'],
            'unread_customer' => ['sometimes', 'boolean'],
            'legacy_id' => ['sometimes', 'nullable', 'string', 'max:40', Rule::unique('message_threads', 'legacy_id')->ignore($thread, 'thread_id')],
        ];
    }
}
