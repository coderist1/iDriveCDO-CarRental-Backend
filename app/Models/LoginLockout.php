<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLockout extends Model
{
    protected $primaryKey = 'email_hash';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'email_hash',
        'failed_count',
        'last_failed_at',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'failed_count' => 'integer',
            'last_failed_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }
}
