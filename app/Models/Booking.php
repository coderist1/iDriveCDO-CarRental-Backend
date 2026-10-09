<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use HasCode, HasFactory;

    public const CODE_PREFIX = 'bk';

    public const STATUSES = ['pending', 'confirmed', 'ongoing', 'return_requested', 'completed', 'rejected', 'cancelled'];

    /**
     * Statuses that hold the vehicle; two of these may not overlap for the same vehicle.
     */
    public const ACTIVE_STATUSES = ['pending', 'confirmed', 'ongoing', 'return_requested'];

    /**
     * Allowed status changes, enforced by the trg_bookings_before_update trigger.
     */
    public const TRANSITIONS = [
        'pending' => ['confirmed', 'rejected', 'cancelled'],
        'confirmed' => ['ongoing', 'cancelled', 'return_requested'],
        'ongoing' => ['completed', 'return_requested'],
        'return_requested' => ['completed', 'ongoing'],
        'completed' => [],
        'rejected' => [],
        'cancelled' => [],
    ];

    public const DRIVE_MODES = ['self', 'chauffeur'];

    public const FUEL_LEVELS = ['Full', '3/4', '1/2', '1/4', 'Reserve', 'Empty'];

    public const PAYMENT_STATUSES = ['unpaid', 'paid'];

    public const PAYMENT_METHODS = ['cash', 'cashless', 'card'];

    protected $primaryKey = 'booking_id';

    /**
     * driver_option is a generated column and is never written.
     */
    protected $fillable = [
        'code',
        'ref',
        'user_id',
        'vehicle_id',
        'driver_id',
        'created_by',
        'start_date',
        'end_date',
        'pickup_time',
        'return_time',
        'days',
        'pickup_location_id',
        'dropoff_location_id',
        'number_of_passengers',
        'drive_mode',
        'fuel_before_rent',
        'fuel_upon_return',
        'subtotal',
        'extras',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'notes',
        'return_notes',
        'date_reserve',
        'started_at',
        'started_by',
        'return_requested_at',
        'return_requested_by',
        'returned_at',
        'returned_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'days' => 'integer',
            'number_of_passengers' => 'integer',
            'subtotal' => 'decimal:2',
            'extras' => 'decimal:2',
            'total' => 'decimal:2',
            'date_reserve' => 'datetime',
            'started_at' => 'datetime',
            'return_requested_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (blank($booking->ref)) {
                $booking->ref = static::generateRef();
            }
        });

        static::saving(function (Booking $booking) {
            if ($booking->start_date && $booking->end_date) {
                $booking->days = (int) Carbon::parse($booking->start_date)->diffInDays(Carbon::parse($booking->end_date));
            }

            $booking->extras ??= 0;

            if ($booking->subtotal !== null) {
                $booking->total = number_format((float) $booking->subtotal + (float) $booking->extras, 2, '.', '');
            }
        });
    }

    /**
     * Booking reference in the IDR-YYYYMMDD-XXXXXX format required by ck_bookings_ref.
     */
    public static function generateRef(): string
    {
        return 'IDR-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
    }

    public function canTransitionTo(string $status): bool
    {
        return $status === $this->status || in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id')->withTrashed();
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function dropoffLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'dropoff_location_id');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function returnRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_requested_by');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'booking_addons', 'booking_id', 'addon_id')
            ->using(BookingAddon::class)
            ->withPivot(['daily_rate', 'days', 'line_total']);
    }

    public function renterDocument(): HasOne
    {
        return $this->hasOne(BookingRenterDocument::class, 'booking_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class, 'booking_id');
    }

    public function messageThread(): HasOne
    {
        return $this->hasOne(MessageThread::class, 'booking_id');
    }
}
