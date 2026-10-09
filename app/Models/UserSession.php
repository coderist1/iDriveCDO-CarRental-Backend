<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Login sessions from the frontend's own auth system (separate from Laravel's `sessions` table).
 */
class UserSession extends Model
{
    protected $primaryKey = 'session_id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'csrf_token',
        'ip_address',
        'user_agent',
        'issued_at',
        'last_seen_at',
        'expires_at',
        'revoked_at',
    ];

    protected $hidden = [
        'csrf_token',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
