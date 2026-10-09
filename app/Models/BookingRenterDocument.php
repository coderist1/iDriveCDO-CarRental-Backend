<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRenterDocument extends Model
{
    public const ID_TYPES = ['National ID', 'Passport', 'UMID', 'Postal ID', 'Company ID', 'Student ID'];

    public const UPDATED_AT = null;

    protected $primaryKey = 'booking_id';

    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'license_name',
        'license_no',
        'license_expiry',
        'license_address',
        'emergency_phone',
        'license_photo',
        'id_type',
        'id_number',
    ];

    protected $hidden = [
        'license_photo',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date:Y-m-d',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
