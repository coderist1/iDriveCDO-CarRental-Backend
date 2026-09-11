<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'driver_details_id',
        'vehicle_id',
        'user_id',
        'pickup_time',
        'pickup_date',
        'return_time',
        'return_date',
        'payment_method',
        'number_of_passenger',
        'driver_option',
        'fuel_before_rent',
        'fuel_upon_return',
        'date_reserve',
        'booking_status',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'return_date' => 'date',
            'date_reserve' => 'date',
            'fuel_before_rent' => 'decimal:2',
            'fuel_upon_return' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    public function driverDetail(): BelongsTo
    {
        return $this->belongsTo(DriverDetail::class, 'driver_details_id', 'driver_details_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }
}
