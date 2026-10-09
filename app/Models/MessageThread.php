<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageThread extends Model
{
    use HasCode;

    public const CODE_PREFIX = 'thr';

    public const KINDS = ['booking', 'contact', 'return'];

    public const STATUSES = ['open', 'closed'];

    protected $primaryKey = 'thread_id';

    protected $fillable = [
        'code',
        'kind',
        'topic',
        'booking_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'unread_staff',
        'unread_customer',
        'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'unread_staff' => 'boolean',
            'unread_customer' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class, 'thread_id')->orderBy('sent_at');
    }
}
