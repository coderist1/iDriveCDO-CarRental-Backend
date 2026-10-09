<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Addon extends Model
{
    use HasCode, HasFactory;

    public const CODE_PREFIX = 'add';

    protected $primaryKey = 'addon_id';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'daily_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_addons', 'addon_id', 'booking_id')
            ->using(BookingAddon::class)
            ->withPivot(['daily_rate', 'days', 'line_total']);
    }
}
