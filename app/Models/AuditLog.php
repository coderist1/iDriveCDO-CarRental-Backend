<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasCode;

    public const CODE_PREFIX = 'aud';

    public const ACTOR_LABELS = ['system', 'guest'];

    public const UPDATED_AT = null;

    protected $primaryKey = 'audit_id';

    protected $fillable = [
        'code',
        'action',
        'user_id',
        'actor_label',
        'detail',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
