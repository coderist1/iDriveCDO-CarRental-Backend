<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreadMessage extends Model
{
    use HasCode;

    public const CODE_PREFIX = 'msg';

    public const ROLES = ['customer', 'staff'];

    protected $primaryKey = 'message_id';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'thread_id',
        'from_role',
        'from_user_id',
        'from_name',
        'body',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (ThreadMessage $message) {
            $message->thread()->update([
                'unread_staff' => $message->from_role === 'customer',
                'unread_customer' => $message->from_role === 'staff',
                'updated_at' => now(),
            ]);
        });
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
