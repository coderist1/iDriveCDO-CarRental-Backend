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
        'dl_codes',
        'license_address',
        'emergency_phone',
        'license_photo',
        'id_photo',
        'id_type',
        'id_number',
        'id_expiry',
        'birthdate',
        'parent_name',
        'parent_phone',
        'parent_id_photo',
        'parent_consent_at',
        'privacy_accepted_at',
    ];

    /** Kept off booking lists; the desk loads them from the id-document route. */
    protected $hidden = [
        'license_photo',
        'id_photo',
        'parent_id_photo',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date:Y-m-d',
            'id_expiry' => 'date:Y-m-d',
            'birthdate' => 'date:Y-m-d',
            'parent_consent_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
