<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Row of booking_addons (composite key booking_id + addon_id).
 * line_total is a generated column and is never written.
 */
class BookingAddon extends Pivot
{
    protected $table = 'booking_addons';

    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'addon_id',
        'daily_rate',
        'days',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'days' => 'integer',
            'line_total' => 'decimal:2',
        ];
    }
}
