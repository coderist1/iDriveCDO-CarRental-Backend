<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasCode, HasFactory, SoftDeletes;

    public const CODE_PREFIX = 'drv';

    public const STATUSES = ['active', 'inactive'];

    public const DUTY_STATUSES = ['regular', 'on_call'];

    protected $primaryKey = 'driver_id';

    protected $fillable = [
        'code',
        'user_id',
        'full_name',
        'driver_license',
        'type_driver_license',
        'license_expiry',
        'phone',
        'status',
        'duty_status',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'driver_id');
    }
}
