<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasCode, HasFactory;

    public const CODE_PREFIX = 'pay';

    public const METHODS = ['cash', 'cashless', 'card'];

    public const CASHLESS_BRANDS = ['GCash', 'Maya', 'GrabPay'];

    public const STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    public const UPDATED_AT = null;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'code',
        'booking_id',
        'amount',
        'payment_method',
        'brand',
        'account_last4',
        'holder',
        'reference_number',
        'payment_status',
        'payment_date',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if ($payment->payment_method === 'cash' && blank($payment->brand)) {
                $payment->brand = 'Cash';
            }

            if (blank($payment->reference_number)) {
                $payment->reference_number = 'PAY-'.now()->format('YmdHis').'-'.strtoupper(bin2hex(random_bytes(4)));
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
