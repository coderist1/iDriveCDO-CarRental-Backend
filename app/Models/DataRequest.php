<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRequest extends Model
{
    public const TYPES = ['view', 'delete'];

    public const STATUSES = ['pending', 'fulfilled', 'denied'];

    protected $primaryKey = 'request_id';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'payload',
        'handled_by',
        'fulfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
